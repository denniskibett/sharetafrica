{{-- Every Sharet email: eyebrow, heading, paragraphs, optional button, optional details table, sign-off.
     Data: $eyebrow, $heading, $lines (array), $button ['label', 'url'], $details ([label => value]), $after (array), $signoff --}}
@extends('emails.layout')

@section('content')
  @isset($eyebrow)
    <p style="margin:0 0 12px; font-size:12px; letter-spacing:.12em; text-transform:uppercase; color:#5A5A55;" class="muted">
      <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#9CCB1F; margin-right:6px;"></span>{{ $eyebrow }}
    </p>
  @endisset

  <h1 class="h1" style="margin:0 0 16px; font-size:28px; line-height:34px; font-weight:800; color:#0A0A0C; letter-spacing:-.01em;">{{ $heading }}</h1>

  @foreach ($lines ?? [] as $line)
    <p class="text" style="margin:0 0 14px; font-size:16px; line-height:25px; color:#2B2B28;">{{ $line }}</p>
  @endforeach

  @isset($button)
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;">
      <tr><td class="btn" style="border-radius:12px; background:#D4FF3D;">
        <a href="{{ $button['url'] }}" style="display:inline-block; padding:14px 24px; font-size:15px; font-weight:700; color:#0A0A0C; text-decoration:none; border-radius:12px;">{{ $button['label'] }} →</a>
      </td></tr>
    </table>
    <p class="muted" style="margin:0 0 14px; font-size:12px; line-height:18px; color:#80807A;">Button not working? Paste this link into your browser:<br><a href="{{ $button['url'] }}" style="color:#5A5A55; word-break:break-all;">{{ $button['url'] }}</a></p>
  @endisset

  @if (! empty($details))
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; border-top:1px solid #E5E5DE;" class="rule">
      @foreach ($details as $label => $value)
        <tr>
          <td class="muted rule" style="padding:10px 12px 10px 0; border-bottom:1px solid #E5E5DE; font-size:13px; color:#80807A; vertical-align:top; width:34%;">{{ $label }}</td>
          <td class="cell rule" style="padding:10px 0; border-bottom:1px solid #E5E5DE; font-size:14px; color:#0A0A0C; white-space:pre-line;">{{ $value }}</td>
        </tr>
      @endforeach
    </table>
  @endif

  @foreach ($after ?? [] as $line)
    <p class="text" style="margin:0 0 14px; font-size:16px; line-height:25px; color:#2B2B28;">{{ $line }}</p>
  @endforeach

  <p class="text" style="margin:24px 0 0; font-size:16px; line-height:25px; color:#2B2B28;">{{ $signoff ?? 'The Sharet team' }}</p>
@endsection
