<?php

// Simpan sebagai: app/Console/Commands/HapusAkunPalsu.php
// Laravel 11+ otomatis mendaftarkan command di folder ini.

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class HapusAkunPalsu extends Command
{
    protected $signature = 'akun:bersihkan-palsu
        {--apply : Benar-benar menghapus. Tanpa opsi ini hanya simulasi (dry-run)}
        {--hari=1 : Hanya akun belum terverifikasi yang dibuat lebih dari N hari lalu}
        {--semua-belum-verifikasi : Abaikan pola nama, targetkan semua akun belum terverifikasi}';

    protected $description = 'Deteksi dan hapus akun palsu hasil bot (belum terverifikasi + nama acak)';

    public function handle(): int
    {
        $hari = max(0, (int) $this->option('hari'));
        $semua = (bool) $this->option('semua-belum-verifikasi');

        // Sesuaikan nama kolom jika berbeda di database Anda
        $query = User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', now()->subDays($hari));

        $kandidat = [];

        $query->orderBy('id')->chunkById(500, function ($users) use (&$kandidat, $semua) {
            foreach ($users as $user) {
                if ($semua || $this->namaAcak($user->fullname)) {
                    $kandidat[] = $user;
                }
            }
        });

        $jumlah = count($kandidat);

        if ($jumlah === 0) {
            $this->info('Tidak ada akun yang cocok dengan kriteria.');
            return self::SUCCESS;
        }

        // Ekspor daftar ke CSV sebagai arsip sebelum apa pun dihapus
        $namaFile = 'akun-palsu-' . now()->format('Ymd-His') . '.csv';
        $csv = "id,fullname,email,created_at\n";
        foreach ($kandidat as $u) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",%s\n",
                $u->id,
                str_replace('"', '""', (string) $u->fullname),
                str_replace('"', '""', (string) $u->email),
                $u->created_at
            );
        }
        Storage::disk('local')->put($namaFile, $csv);

        $this->table(
            ['ID', 'Nama', 'Email', 'Dibuat'],
            collect($kandidat)->take(25)->map(fn ($u) => [$u->id, $u->fullname, $u->email, $u->created_at])->all()
        );
        if ($jumlah > 25) {
            $this->line('... dan ' . ($jumlah - 25) . ' akun lainnya.');
        }
        $this->info("Total kandidat: {$jumlah} akun. Daftar lengkap disimpan di storage/app/private/{$namaFile} (atau storage/app/{$namaFile}).");

        if (! $this->option('apply')) {
            $this->warn('Mode simulasi: belum ada data yang dihapus. Tambahkan --apply untuk menghapus.');
            return self::SUCCESS;
        }

        if (! $this->confirm("Hapus {$jumlah} akun ini secara permanen? Pastikan database sudah di-backup.")) {
            $this->line('Dibatalkan.');
            return self::SUCCESS;
        }

        $ids = collect($kandidat)->pluck('id');
        $terhapus = 0;
        foreach ($ids->chunk(200) as $potongan) {
            $terhapus += User::whereIn('id', $potongan)->delete();
        }

        $this->info("Selesai. {$terhapus} akun dihapus.");
        return self::SUCCESS;
    }

    /**
     * Heuristik nama acak ala bot: satu kata huruf saja, panjang 8-16,
     * dengan sangat sedikit huruf vokal (contoh: Pmytsupqld, Nlosllgzrd).
     */
    private function namaAcak(?string $nama): bool
    {
        $nama = trim((string) $nama);

        if (! preg_match('/^[A-Za-z]{8,16}$/', $nama)) {
            return false;
        }

        $vokal = preg_match_all('/[aeiou]/i', $nama);

        return ($vokal / strlen($nama)) <= 0.3;
    }
}