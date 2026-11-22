{{-- Approve / reject with an optional note. --}}
<form method="POST" class="d-flex flex-column gap-1" style="min-width: 12rem">
    @csrf @method('PATCH')
    <input name="review_note" class="form-control form-control-sm" placeholder="Note (optional)" maxlength="255">
    <div class="d-flex gap-1">
        <button formaction="{{ $approve }}" class="btn btn-sm btn-success flex-fill">Approve</button>
        <button formaction="{{ $reject }}" class="btn btn-sm btn-outline-danger flex-fill">Reject</button>
    </div>
</form>
