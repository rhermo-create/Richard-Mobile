// Lightweight client-side loader: keeps sidebar persistent and swaps main content
(function () {
  function sameOrigin(href) {
    try {
      const url = new URL(href, location.href);
      return url.origin === location.origin;
    } catch (e) { return false; }
  }

  async function loadPage(href, replaceState = false) {
    const url = new URL(href, location.href);
    if (url.pathname === location.pathname && !url.search) return;
    try {
      const res = await fetch(url.href, {cache: 'no-store'});
      if (!res.ok) {
        location.href = url.href; // fallback to full navigation
        return;
      }
      const text = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(text, 'text/html');
      const newMain = doc.querySelector('main.sidebar-main');
      if (!newMain) {
        location.href = url.href; // fallback
        return;
      }
      const currentMain = document.querySelector('main.sidebar-main');
      currentMain.innerHTML = newMain.innerHTML;
      document.title = doc.title || document.title;
      if (replaceState) {
        history.replaceState({url: url.href}, '', url.href);
      } else {
        history.pushState({url: url.href}, '', url.href);
      }
      updateActiveLinks(url.pathname);
    } catch (err) {
      console.error('Failed to load page', err);
      location.href = href; // hard fallback
    }
  }

  function updateActiveLinks(pathname) {
    const sidebarLinks = document.querySelectorAll('.sidebar__link');
    sidebarLinks.forEach(a => {
      try {
        const href = new URL(a.getAttribute('href'), location.href).pathname;
        if (href === pathname) a.classList.add('sidebar__link--active'); else a.classList.remove('sidebar__link--active');
      } catch (e) { }
    });
    const navLinks = document.querySelectorAll('.nav_Settings .nav__link');
    navLinks.forEach(a => {
      try {
        const href = new URL(a.getAttribute('href'), location.href).pathname;
        if (href === pathname) a.classList.add('nav__link--active'); else a.classList.remove('nav__link--active');
      } catch (e) { }
    });
  }

  document.addEventListener('click', function (e) {
    const a = e.target.closest('a');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
    if (!sameOrigin(href)) return; // external
    // Only intercept .html internal links
    if (href.endsWith('.html') || href.indexOf('.html') !== -1) {
      // allow ctrl/cmd/shift/alt clicks
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      e.preventDefault();
      loadPage(href);
    }
  });

  window.addEventListener('popstate', function (e) {
    const url = (e.state && e.state.url) || location.href;
    loadPage(url, true);
  });

  // On initial load, make sure active links reflect the current path
  document.addEventListener('DOMContentLoaded', function () {
    updateActiveLinks(location.pathname);
  });
})();
