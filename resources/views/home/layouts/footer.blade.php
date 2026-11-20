{{-- resources/views/home/layouts/footer.blade.php --}}
<footer class="site-footer">
  <div class="container container--wide">
    <div class="footer-top">
      <div class="footer-brand">
        <span class="brand"><img class="brand-logo brand-logo--footer" src="{{ asset('images/brand/sharet-logo-on-dark.svg') }}" alt="Sharet Africa" width="200" height="64" loading="lazy"></span>
        <p>Cheaper payments across East Africa. Any rail. The other person doesn't need Sharet. One wallet, one ledger, many rails.</p>
        <span class="label">Nairobi HQ · regional by appointment</span>
      </div>
      <div>
        <h4>Use Sharet</h4>
        <ul>
          <li><a href="{{ route('merchants') }}">Merchants</a></li>
          <li><a href="{{ route('personal') }}">Personal</a></li>
          <li><a href="{{ route('trade') }}">Trade</a></li>
          <li><a href="{{ route('contact') }}">Get started</a></li>
        </ul>
      </div>
      <div>
        <h4>Build on Sharet</h4>
        <ul>
          <li><a href="{{ route('developers') }}">Developers</a></li>
          <li><a href="{{ route('developers') }}#endpoints">API reference</a></li>
          <li><a href="{{ route('developers') }}#licensing">Licensing</a></li>
          <li><a href="{{ route('developers') }}#sandbox">Sandbox</a></li>
          @if (\App\Support\AndroidRelease::latest())<li><a href="{{ route('developers') }}#android">Android test build</a></li>@endif
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="{{ route('company') }}">About</a></li>
          <li><a href="{{ route('company') }}#team">Team</a></li>
          <li><a href="{{ route('company') }}#companies">Our companies</a></li>
          <li><a href="{{ route('contact') }}#careers">Careers</a></li>
          <li><a href="{{ route('contact') }}#press">Press</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Sharet Africa · Nairobi HQ · regional by appointment</span>
      <div class="footer-meta-links">
        <a href="{{ route('privacy') }}">Privacy</a>
        <a href="{{ route('company') }}">Company details</a>
        <a href="{{ route('sitemap') }}">Sitemap</a>
      </div>
    </div>
  </div>
</footer>