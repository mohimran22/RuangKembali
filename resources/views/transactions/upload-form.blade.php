<form action="{{ route('transactions.upload-proof', $transaction->id) }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <div class="mb-3">
        <label class="form-label">Upload Bukti Transfer</label>
        <input type="file"
               name="proof_of_payment"
               class="form-control"
               accept="image/*"
               required>
        <div class="form-hint">Format JPG/PNG, maksimal 5MB.</div>
    </div>

    @error('proof_of_payment')
        <div class="text-danger small mb-3">{{ $message }}</div>
    @enderror

    <button type="submit" class="btn btn-primary w-100">
        <i class="ti ti-upload me-1"></i>
        Upload Bukti Transfer
    </button>

</form>