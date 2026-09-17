{{-- resources/views/home/layouts/footer.blade.php --}}
<footer class="site-footer">
  <div class="container container--wide">
    <div class="footer-top">
      <div class="footer-brand">
        <span class="brand"><span class="brand-mark" aria-hidden="true"></span> Sharet!</span>
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
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="{{ route('company') }}">About</a></li>
          <li><a href="{{ route('company') }}#team">Team</a></li>
          <li><a href="{{ route('contact') }}#careers">Careers</a></li>
          <li><a href="{{ route('contact') }}#press">Press</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Sharet Africa · Nairobi HQ · regional by appointment</span>
      <div class="footer-meta-links">
        <a href="#">Privacy</a>
        <a href="#">Imprint</a>
        <a href="#">Sitemap</a>
      </div>
    </div>
  </div>
</footer>