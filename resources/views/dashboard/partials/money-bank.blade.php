{{-- Bank and account number, inside the money panel (send or withdraw). Banks show their own logos
     (App\Support\Partners); picking one sets form.bank exactly as the old dropdown did. --}}
<div class="space-y-4">
  <div>
    <p class="{{ $label }}" id="bank-label">Bank</p>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4" role="radiogroup" aria-labelledby="bank-label">
      @foreach ($banks as $code => $name)
        @php $slug = \App\Support\Partners::forBank($code) ?? \App\Support\Partners::forBank($name); $logo = \App\Support\Partners::logo($slug); $dark = \App\Support\Partners::onDark($slug); @endphp
        <button type="button" role="radio" @click="form.bank = '{{ $code }}'" :aria-checked="form.bank === '{{ $code }}'"
                :class="form.bank === '{{ $code }}' ? 'border-app-ink' : 'border-transparent'"
                @class(['flex h-[72px] flex-col items-center justify-center gap-1.5 rounded-2xl border-2 px-2 text-[11px] font-medium transition hover:opacity-90', 'bg-[#0A0A0C]' => $dark, 'bg-white' => ! $dark])>
          @if ($logo)<img src="{{ $logo }}" alt="" class="h-7 max-w-[90%] object-contain" loading="lazy">@else<x-heroicon-o-building-library class="h-6 w-6 text-orange-600" />@endif
          <span @class(['truncate', 'text-white/80' => $dark, 'text-[#45453F]' => ! $dark])>{{ $name }}</span>
        </button>
      @endforeach
    </div>
    <p class="{{ $err }}" x-text="errors.bank?.[0]"></p>
  </div>
  <div><label class="{{ $label }}" for="m-account">Account number</label>
    <input id="m-account" x-model="form.account" inputmode="numeric" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.account?.[0]"></p></div>
</div>
