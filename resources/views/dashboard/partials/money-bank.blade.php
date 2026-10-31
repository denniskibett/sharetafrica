{{-- Bank and account number, inside the money panel (send or withdraw). --}}
<div class="grid grid-cols-2 gap-3">
  <div><label class="{{ $label }}">Bank</label>
    <select x-model="form.bank" class="{{ $input }}">
      <option value="">Choose a bank</option>
      @foreach ($banks as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach
    </select><p class="{{ $err }}" x-text="errors.bank?.[0]"></p></div>
  <div><label class="{{ $label }}">Account number</label>
    <input x-model="form.account" inputmode="numeric" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.account?.[0]"></p></div>
</div>
