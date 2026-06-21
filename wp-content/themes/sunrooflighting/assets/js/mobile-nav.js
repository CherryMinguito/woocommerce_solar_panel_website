/**
 * Mobile navigation and filter drawer.
 */
(function () {
  'use strict';

  function initMobileNav() {
    var toggle = document.querySelector('.jcs-nav-toggle');
    var nav = document.getElementById('jcs-mobile-nav');
    var overlay = document.querySelector('.jcs-nav-overlay');

    if (!toggle || !nav) {
      return;
    }

    function openNav() {
      nav.classList.add('is-open');
      document.body.classList.add('jcs-nav-open');
      toggle.setAttribute('aria-expanded', 'true');
      toggle.setAttribute('aria-label', 'Close menu');
      if (overlay) {
        overlay.hidden = false;
        overlay.classList.add('is-visible');
      }
    }

    function closeNav() {
      nav.classList.remove('is-open');
      document.body.classList.remove('jcs-nav-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Open menu');
      if (overlay) {
        overlay.classList.remove('is-visible');
        overlay.hidden = true;
      }
    }

    toggle.addEventListener('click', function () {
      if (nav.classList.contains('is-open')) {
        closeNav();
      } else {
        openNav();
      }
    });

    if (overlay) {
      overlay.addEventListener('click', closeNav);
    }

    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', closeNav);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        closeNav();
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 900) {
        closeNav();
      }
    });
  }

  function initFilterToggle() {
    var sidebar = document.querySelector('.jcs-plp-sidebar');
    if (!sidebar) {
      return;
    }

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'jcs-filter-toggle';
    btn.textContent = 'Show Filters';
    btn.setAttribute('aria-expanded', 'false');

    sidebar.parentNode.insertBefore(btn, sidebar);

    btn.addEventListener('click', function () {
      var open = sidebar.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.textContent = open ? 'Hide Filters' : 'Show Filters';
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMobileNav();
    initFilterToggle();
  });
})();
