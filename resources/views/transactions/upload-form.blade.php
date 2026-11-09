<form action="{{ route('transactions.upload-proof', $transaction->id) }}"
      method="POST"
      enctype="multipart/form-data"
      class="mb-3">
    @csrf

    <label class="form-label">Bukti Pembayaran (Silahkan unggah bukti pembayaran disini)</label>
    <input type="file"
           name="proof_of_payment"
           accept="image/*"
           class="form-control @error('proof_of_payment') is-invalid @enderror"
           required>
    @error('proof_of_payment')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-hint">Format gambar, maksimal 5 MB. Pengunggah akan tercatat atas nama kamu.</div>

    <button type="submit" class="btn btn-primary mt-3">
        <i class="ti ti-upload me-1"></i>
        Upload Bukti
    </button>
</form>