/**
 * Simple carousel for promo bar and hero sections.
 */
(function () {
  'use strict';

  function initCarousel(container) {
    const slides = container.querySelectorAll('[class*="-slide"]');
    if (slides.length <= 1) return;

    let current = 0;
    let interval = null;
    let paused = false;

    function show(index) {
      slides.forEach((slide, i) => {
        slide.classList.toggle('is-active', i === index);
      });
      current = index;
      updateDots();
    }

    function next() {
      show((current + 1) % slides.length);
    }

    function updateDots() {
      const dotsContainer = container.closest('.jcs-container')?.querySelector('[data-carousel-dots]')
        || container.parentElement?.querySelector('[data-carousel-dots]');

      if (!dotsContainer) return;

      dotsContainer.innerHTML = '';
      slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.setAttribute('aria-label', 'Slide ' + (i + 1));
        if (i === current) dot.classList.add('is-active');
        dot.addEventListener('click', () => {
          show(i);
          resetInterval();
        });
        dotsContainer.appendChild(dot);
      });
    }

    function startInterval() {
      if (interval) clearInterval(interval);
      interval = setInterval(() => {
        if (!paused) next();
      }, 5000);
    }

    function resetInterval() {
      startInterval();
    }

    show(0);
    startInterval();

    const pauseBtn = container.closest('.jcs-promo-bar')?.querySelector('[data-carousel-pause]');
    if (pauseBtn) {
      pauseBtn.addEventListener('click', () => {
        paused = !paused;
        pauseBtn.textContent = paused ? '▶' : '⏸';
        pauseBtn.setAttribute('aria-label', paused ? 'Resume promo carousel' : 'Pause promo carousel');
      });
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-carousel]').forEach(initCarousel);
  });
})();
