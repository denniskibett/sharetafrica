{{-- Every Sharet email: eyebrow, heading, paragraphs, optional button, optional details table, sign-off.
     Data: $eyebrow (top-right label), $heading, $highlight (optional lime second line), $lines (first one sits in the black hero), $button ['label', 'url'], $details ([label => value]), $after (array), $signoff --}}
@extends('emails.layout')

@section('hero')
  <h1 class="h1" style="margin:0 0 16px; font-family:'Boldonse', 'Archivo Black', 'Arial Black', sans-serif; font-size:28px; font-weight:400; line-height:1.4; color:#F4F4F0;">{{ $heading }}@isset($highlight)<br><span style="color:#D4FF3D;">{{ $highlight }}</span>@endisset</h1>

  @foreach (array_slice($lines ?? [], 0, 1) as $line)
    <p style="margin:0 0 4px; font-size:17px; line-height:27px; color:#C9C9C2;">{{ $line }}</p>
  @endforeach

  @isset($button)
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 0;">
      <tr><td style="border-radius:10px; background:#D4FF3D;">
        <a href="{{ $button['url'] }}" style="display:inline-block; padding:16px 26px; font-size:16px; font-weight:600; color:#050507; text-decoration:none; border-radius:10px;">{{ $button['label'] }}</a>
      </td></tr>
    </table>
  @endisset
@endsection

@section('content')
  @foreach (array_slice($lines ?? [], 1) as $line)
    <p style="margin:0 0 16px; font-size:16px; line-height:26px; color:#111114;">{{ $line }}</p>
  @endforeach

  @if (! empty($details))
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 24px; background:#F4F4F0; border-radius:12px;">
      <tr><td style="padding:20px 24px 8px; font-family:'Geist Mono', ui-monospace, Menlo, monospace; font-size:12px; letter-spacing:.16em; color:#5A5A55;">{{ strtoupper($detailsTitle ?? 'Details') }}</td></tr>
      @foreach ($details as $label => $value)
        <tr><td style="padding:0 24px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-bottom:1px solid #E5E5DE;"><tr>
            <td style="padding:10px 12px 10px 0; font-size:14px; color:#5A5A55; vertical-align:top; width:38%;">{{ $label }}</td>
            <td style="padding:10px 0; font-size:14px; color:#111114; white-space:pre-line; text-align:right;">{{ $value }}</td>
          </tr></table>
        </td></tr>
      @endforeach
      <tr><td style="height:12px; line-height:12px;">&nbsp;</td></tr>
    </table>
  @endif

  @foreach ($after ?? [] as $line)
    <p style="margin:0 0 16px; font-size:16px; line-height:26px; color:#111114;">{{ $line }}</p>
  @endforeach

  @isset($button)
    <p style="margin:0 0 16px; font-size:12px; line-height:18px; color:#80807A;">Button not working? Paste this link into your browser:<br><a href="{{ $button['url'] }}" style="color:#5A5A55; word-break:break-all;">{{ $button['url'] }}</a></p>
  @endisset

  <p style="margin:8px 0 24px; font-size:16px; line-height:26px; color:#111114;">{{ $signoff ?? 'The Sharet team' }}</p>
@endsection
