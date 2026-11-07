@foreach (['KE' => 'Kenya', 'TZ' => 'Tanzania', 'UG' => 'Uganda'] as $code => $name)
  <option value="{{ $code }}" @selected(old('country', 'KE') === $code)>{{ $name }}</option>
@endforeach
