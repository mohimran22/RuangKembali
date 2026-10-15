<?php

// Simpan sebagai: app/Console/Commands/HapusAkunPalsu.php
// Versi 3: tambah --panjang (nama acak panjang tepat N huruf) dan --kecuali (daftar email aman).

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HapusAkunPalsu extends Command
{
    protected $signature = 'akun:bersihkan-palsu
        {--apply : Benar-benar menghapus. Tanpa opsi ini hanya simulasi (dry-run)}
        {--hari=1 : Hanya akun belum terverifikasi yang dibuat lebih dari N hari lalu}
        {--semua-belum-verifikasi : Abaikan pola nama, targetkan semua akun belum terverifikasi}
        {--panjang= : Anggap juga nama acak jika hanya huruf dengan panjang tepat N (contoh: 10)}
        {--kecuali=* : Email yang dikecualikan dari penghapusan (boleh diulang)}
        {--relasi=* : Tabel yang mengacu ke users, format tabel:kolom (contoh: --relasi=customers:user_id)}';

    protected $description = 'Deteksi dan hapus akun palsu hasil bot (belum terverifikasi + nama acak)';

    public function handle(): int
    {
        $hari = max(0, (int) $this->option('hari'));
        $semua = (bool) $this->option('semua-belum-verifikasi');
        $panjang = $this->option('panjang') !== null && $this->option('panjang') !== ''
            ? max(1, (int) $this->option('panjang'))
            : null;
        $kecuali = array_values(array_filter(array_map(
            fn ($e) => strtolower(trim((string) $e)),
            (array) $this->option('kecuali')
        )));
        $relasi = $this->parseRelasi((array) $this->option('relasi'));

        if ($relasi === null) {
            $this->error('Format --relasi harus tabel:kolom, contoh: --relasi=customers:user_id');
            return self::FAILURE;
        }

        // Sesuaikan nama kolom jika berbeda di database Anda
        $query = User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', now()->subDays($hari));

        if (! empty($kecuali)) {
            $query->whereNotIn(DB::raw('lower(email)'), $kecuali);
        }

        $kandidat = [];

        $query->orderBy('id')->chunkById(500, function ($users) use (&$kandidat, $semua, $panjang) {
            foreach ($users as $user) {
                if ($semua || $this->namaAcak($user->fullname, $panjang)) {
                    $kandidat[] = $user;
                }
            }
        });

        $jumlah = count($kandidat);

        if ($jumlah === 0) {
            $this->info('Tidak ada akun yang cocok dengan kriteria.');
            return self::SUCCESS;
        }

        $ids = collect($kandidat)->pluck('id')->all();

        // Arsip CSV sebelum apa pun dihapus
        $namaFile = 'akun-palsu-' . now()->format('Ymd-His') . '.csv';
        $csv = "id,fullname,email,created_at\n";
        foreach ($kandidat as $u) {
            $csv .= sprintf(
                "%s,\"%s\",\"%s\",%s\n",
                $u->id,
                str_replace('"', '""', (string) $u->fullname),
                str_replace('"', '""', (string) $u->email),
                $u->created_at
            );
        }
        Storage::disk('local')->put($namaFile, $csv);

        $this->table(
            ['ID', 'Nama', 'Email', 'Dibuat'],
            collect($kandidat)->take(60)->map(fn ($u) => [$u->id, $u->fullname, $u->email, $u->created_at])->all()
        );
        if ($jumlah > 60) {
            $this->line('... dan ' . ($jumlah - 60) . ' akun lainnya.');
        }
        $this->info("Total kandidat: {$jumlah} akun. Arsip CSV: {$namaFile} (di storage/app).");

        // Hitung baris relasi yang akan ikut terhapus
        foreach ($relasi as [$tabel, $kolom]) {
            $n = 0;
            foreach (array_chunk($ids, 200) as $potongan) {
                $n += DB::table($tabel)->whereIn($kolom, $potongan)->count();
            }
            $this->line("Relasi {$tabel}.{$kolom}: {$n} baris akan ikut dihapus.");
        }

        if (! $this->option('apply')) {
            $this->warn('Mode simulasi: belum ada data yang dihapus. Tambahkan --apply untuk menghapus.');
            return self::SUCCESS;
        }

        if (! $this->confirm("Hapus {$jumlah} akun (dan baris relasinya) secara permanen? Pastikan database sudah di-backup.")) {
            $this->line('Dibatalkan.');
            return self::SUCCESS;
        }

        try {
            // Satu transaksi: kalau ada yang gagal, semuanya dibatalkan
            $terhapus = DB::transaction(function () use ($ids, $relasi) {
                foreach ($relasi as [$tabel, $kolom]) {
                    foreach (array_chunk($ids, 200) as $potongan) {
                        DB::table($tabel)->whereIn($kolom, $potongan)->delete();
                    }
                }

                $total = 0;
                foreach (array_chunk($ids, 200) as $potongan) {
                    $total += User::whereIn('id', $potongan)->delete();
                }

                return $total;
            });
        } catch (\Throwable $e) {
            $this->error('Gagal, tidak ada data yang diubah (transaksi dibatalkan).');
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info("Selesai. {$terhapus} akun dihapus.");
        return self::SUCCESS;
    }

    /**
     * Ubah ["customers:user_id"] menjadi [["customers","user_id"]].
     * Mengembalikan null jika ada format yang salah.
     */
    private function parseRelasi(array $daftar): ?array
    {
        $hasil = [];

        foreach ($daftar as $item) {
            if (! preg_match('/^([A-Za-z0-9_]+):([A-Za-z0-9_]+)$/', $item, $m)) {
                return null;
            }
            $hasil[] = [$m[1], $m[2]];
        }

        return $hasil;
    }

    /**
     * Heuristik nama acak ala bot. Cocok jika salah satu terpenuhi:
     * 1. Satu kata huruf saja, panjang tepat $panjang (jika opsi --panjang diisi).
     * 2. Satu kata huruf saja, panjang 8-16, dengan sangat sedikit vokal (<= 30%).
     */
    private function namaAcak(?string $nama, ?int $panjang = null): bool
    {
        $nama = trim((string) $nama);

        if ($panjang !== null && preg_match('/^[A-Za-z]{' . $panjang . '}$/', $nama)) {
            return true;
        }

        if (! preg_match('/^[A-Za-z]{8,16}$/', $nama)) {
            return false;
        }

        $vokal = preg_match_all('/[aeiou]/i', $nama);

        return ($vokal / strlen($nama)) <= 0.3;
    }
}