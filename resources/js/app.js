// ============================================================
// CAMBOTRIPS — SPA Navigation Engine  (v3 — reload-loop fixes)
// ============================================================
//
import { initGallery } from './gallery.js';
import { initLiveSearch } from './live-search.js';

// Re-run per-page component initialisation after every SPA swap.
function initPageComponents() {
  initGallery();
  initLiveSearch();
}
//
// Root causes fixed in v2
// ----------------------------------
// BUG 1: initApp() added document click/submit/scroll listeners every time
//         it was called → duplicate listeners → double navigation.
// FIX:    Guard all global listeners with a single `_spaInitialised` flag.
//
// BUG 2: executePageScripts() re-ran app.js from the fetched <head>, causing
//         a second click listener to fire navigateTo() twice on every link.
// FIX:    runInlineScripts() skips any <script src="..."> tags.
//
// BUG 3: window.location.href in the catch block caused a real full-page
//         reload on any transient network blip.
// FIX:    Removed the fallback. Errors are shown as a toast instead.
//
// BUG 4: No navigation lock — rapid clicks fired multiple fetch() calls.
// FIX:    _navigating flag blocks concurrent navigations.
//
// BUG 5: onclick="window.location.href='...'" on card <div>s bypassed SPA.
// FIX:    All cards are now wrapped in proper <a> tags.
//
// Root causes fixed in v3
// ----------------------------------
// BUG 6: Footer newsletter form had no method attribute, so it defaulted to
//         GET. The SPA submit handler intercepted it, built a URL from the
//         current page + email query param, and called navigateTo() — making
//         the page appear to reload/re-render on every newsletter submission.
// FIX:    Added method="post" to the newsletter form (app.blade.php) so the
//         SPA's GET-only submit interceptor skips it entirely.
//         Also added a data-bypass attribute check in the submit listener as
//         a general escape hatch for any future form that must not be caught.
//
// BUG 7: navigateTo() had no isStandaloneRoute guard of its own. If it was
//         ever called with an admin/auth URL (e.g. via history state), it
//         would fetch the page, find no #app-content, and fall back to
//         window.location.href = url — which, if url == current URL, triggers
//         a hard browser reload.
// FIX:    isStandaloneRoute check moved to the very top of navigateTo() so
//         the function bails out with a real navigation before any fetch,
//         regardless of the call site.
//
// ============================================================

// ── State ─────────────────────────────────────────────────────
let _spaInitialised = false;
let _navigating     = false;
const _prefetches   = new Map();

// Helper to prefetch a URL and cache its promise
function prefetchRoute(url) {
  if (_prefetches.has(url)) return;

  const promise = fetch(url, { headers: { 'X-SPA-Request': '1' } })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.text();
    })
    .catch(err => {
      _prefetches.delete(url);
      throw err;
    });

  _prefetches.set(url, promise);
}


// ── Toast ─────────────────────────────────────────────────────
function showToast(message, type = 'success') {
  const existing = document.getElementById('toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.id        = 'toast';
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);

  requestAnimationFrame(() => toast.classList.add('show'));
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 3000);
}

// Expose showToast globally so inline Blade <script> tags can call it.
// Vite loads this file as type="module", which scopes all declarations.
window.showToast = showToast;



// ── Navbar mobile toggle ──────────────────────────────────────
// Safe to call multiple times — clones the button to wipe old listeners.
function setupNavbarToggle() {
  const toggle  = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');
  if (!toggle || !navMenu) return;

  const fresh = toggle.cloneNode(true);
  toggle.replaceWith(fresh);

  fresh.addEventListener('click', () => {
    navMenu.classList.toggle('open');
    fresh.classList.toggle('open');
  });
}

// Helper to check if a route is a standalone page bypassing SPA routing
function isStandaloneRoute(pathname) {
  return pathname.startsWith('/admin') ||
         pathname === '/logout' ||
         pathname === '/login' ||
         pathname === '/signup' ||
         pathname.startsWith('/auth/google') ||
         pathname.startsWith('/email/verify') ||
         pathname === '/forgot-password' ||
         pathname.startsWith('/reset-password');
}

// ── Detect asset version changes after a new deployment ───────
// Compares the Vite-generated <script type="module"> src in the current
// page against the fetched page. If the hashed filename differs, a new
// build has been deployed and we need a full page reload.
function _hasAssetsChanged(fetchedDoc) {
  try {
    // Get the Vite module script from the current page
    const currentScript = document.querySelector('script[type="module"][src*="/build/assets/"]');
    // Get the Vite module script from the fetched page
    const fetchedScript = fetchedDoc.querySelector('script[type="module"][src*="/build/assets/"]');

    if (currentScript && fetchedScript) {
      return currentScript.getAttribute('src') !== fetchedScript.getAttribute('src');
    }
  } catch (_) { /* comparison failed — assume no change */ }
  return false;
}

// ── AJAX page swap ────────────────────────────────────────────
function navigateTo(url, addToHistory = true) {
  if (_navigating) return;          // prevent double-click races
  if (window.location.href === url) return; // already here

  // Guard: bail out immediately for admin/auth/standalone pages.
  // This ensures navigateTo() itself can never accidentally fetch
  // a non-SPA page, regardless of which code path invoked it.
  try {
    const u = new URL(url, window.location.href);
    if (isStandaloneRoute(u.pathname)) {
      window.location.href = url;
      return;
    }
  } catch (_) { /* invalid URL — fall through and let fetch() fail gracefully */ }

  _navigating = true;

  // Cache selectors for existing layout nodes that will be updated
  const appContent = document.getElementById('app-content');
  const curLinks   = document.querySelector('.nav-links');
  const curActions = document.querySelector('.nav-actions');

  // Use prefetch promise if available, otherwise fetch on demand
  const fetchPromise = _prefetches.get(url) || fetch(url, { headers: { 'X-SPA-Request': '1' } })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.text();
    });

  // Consume the prefetch promise from the map
  _prefetches.delete(url);

  fetchPromise
    .then(html => {
      const parser = new DOMParser();
      const doc    = parser.parseFromString(html, 'text/html');

      // ── Detect non-SPA pages (standalone pages without #app-content) ──
      // Auth pages (login/signup) are standalone and have no #app-content.
      // If the fetched page has no #app-content, do a real navigation instead
      // of silently failing and leaving the current page dimmed/frozen.
      const newContent = doc.getElementById('app-content');
      if (!newContent) {
        // Fall back to a real browser navigation for standalone pages
        window.location.href = url;
        return;
      }

      // ── Detect new deployment (asset version mismatch) ──────────
      // Vite generates hashed filenames (e.g. app-D2cU0ktk.js).
      // If a new deployment changed the assets, the fetched page will
      // reference different hashed filenames than what we currently have.
      // In that case, do a full page navigation so the browser loads
      // the new CSS/JS — no hard refresh required from the user.
      if (_hasAssetsChanged(doc)) {
        console.info('[SPA] New deployment detected — reloading with fresh assets.');
        window.location.href = url;
        return;
      }

      // Swap title
      document.title = doc.title;

      // Swap main content
      if (appContent) {
        appContent.innerHTML = newContent.innerHTML;
      }

      // Swap nav-links (active class update)
      const newLinks = doc.querySelector('.nav-links');
      if (curLinks && newLinks) curLinks.innerHTML = newLinks.innerHTML;

      // Swap nav-actions (auth state)
      const newActions = doc.querySelector('.nav-actions');
      if (curActions && newActions) curActions.innerHTML = newActions.innerHTML;

      // Auto-close mobile navigation menu if open on page swap
      const toggle = document.getElementById('navToggle');
      const navMenu = document.getElementById('navMenu');
      if (toggle && toggle.classList.contains('open')) {
        toggle.classList.remove('open');
        if (navMenu) navMenu.classList.remove('open');
      }

      // Push history
      if (addToHistory) history.pushState({ url }, doc.title, url);

      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'instant' });

      // Run page-specific inline scripts from the NEW content only
      runInlineScripts(newContent);

      // Re-init page-level components (gallery, live search) for new content
      initPageComponents();
    })
    .catch(err => {
      console.error('[SPA] Navigation failed:', err);
      showToast('Page could not be loaded. Please try again.', 'error');
    })
    .finally(() => {
      _navigating = false;
    });
}

// ── Run only the inline scripts inside the swapped content ────
// Skips any <script src="..."> tags — those are layout-level scripts
// (like app.js) that are already loaded and must NOT be re-executed.
function runInlineScripts(container) {
  if (!container) return;

  container.querySelectorAll('script').forEach(oldScript => {
    // Skip external scripts (app.js, CDN libs, etc.)
    if (oldScript.src) return;

    const s = document.createElement('script');
    s.textContent = oldScript.textContent;
    document.body.appendChild(s);
    s.remove();
  });
}

// ── One-time global event setup ───────────────────────────────
function initApp() {
  if (_spaInitialised) return;   // ← THE key guard: run once only
  _spaInitialised = true;

  setupNavbarToggle();

  // Sticky navbar + back-to-top
  const navbar      = document.getElementById('navbar');
  const backToTop   = document.getElementById('backToTop');

  const onScroll = () => {
    const y = window.scrollY;
    navbar?.classList.toggle('scrolled', y > 20);
    backToTop?.classList.toggle('show', y > 300);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // ── Delegate all link clicks ──────────────────────────────
  document.addEventListener('click', e => {
    const link = e.target.closest('a[href]');
    if (!link) return;

    // Let modifier keys / special attributes fall through
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (link.target === '_blank' || link.hasAttribute('download') || link.hasAttribute('data-bypass')) return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
        href.startsWith('mailto:') || href.startsWith('tel:')) return;

    try {
      const url = new URL(href, window.location.href);
      if (url.origin !== window.location.origin) return;

      // Hard-exclude admin, logout, and auth routes from SPA.
      if (isStandaloneRoute(url.pathname)) return;

      e.preventDefault();
      navigateTo(url.toString());
    } catch (_) { /* invalid URL — let browser handle */ }
  });

  // ── Delegate GET form submissions (search, filters) ───────
  // Skips: POST forms, cross-origin forms, standalone-route forms,
  //        and any form with the data-bypass attribute.
  document.addEventListener('submit', e => {
    const form = e.target;
    if (form.method.toLowerCase() !== 'get') return;   // only GET forms
    if (form.hasAttribute('data-bypass')) return;       // explicit opt-out

    try {
      const url = new URL(form.action || window.location.href, window.location.href);
      if (url.origin !== window.location.origin) return;
      if (isStandaloneRoute(url.pathname)) return;

      const data = new FormData(form);
      url.search = '';
      data.forEach((val, key) => { if (val !== '') url.searchParams.set(key, val); });

      e.preventDefault();
      navigateTo(url.toString());
    } catch (_) { /* invalid form action */ }
  });

  // ── Back / Forward buttons ────────────────────────────────
  window.addEventListener('popstate', e => {
    const target = (e.state && e.state.url) ? e.state.url : window.location.href;

    // If the target is a standalone/auth page, do a real navigation
    try {
      const u = new URL(target, window.location.href);
      if (isStandaloneRoute(u.pathname)) {
        window.location.href = target;
        return;
      }
    } catch (_) {}

    navigateTo(target, false);
  });

  // ── Conservative prefetch on hover (desktop only) ─────────
  document.addEventListener('mouseover', e => {
    // Check if device supports hover
    if (window.matchMedia('(hover: none)').matches) return;

    const link = e.target.closest('a[href]');
    if (!link) return;

    try {
      const url = new URL(link.getAttribute('href'), window.location.href);
      if (url.origin !== window.location.origin) return;

      // Limit prefetching strictly to important public entry points
      const pathname = url.pathname;
      const isPrefetchable = pathname === '/' || pathname === '/destination' || pathname === '/explore';
      if (!isPrefetchable) return;

      // Skip current page and standalone routes
      if (url.href === window.location.href || isStandaloneRoute(pathname)) return;

      prefetchRoute(url.href);
    } catch (_) {}
  }, { passive: true });
}

// ── Bootstrap ─────────────────────────────────────────────────
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => { initApp(); initPageComponents(); });
} else {
  initApp();
  initPageComponents();
}
