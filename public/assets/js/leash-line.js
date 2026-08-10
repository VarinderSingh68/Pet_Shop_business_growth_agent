(function () {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.addEventListener('DOMContentLoaded', function () {
    const svg = document.querySelector('[data-leash-line]');
    if (!svg) return;

    const path = svg.querySelector('.leash-line');
    const waypoints = Array.from(svg.querySelectorAll('.paw-waypoint'));
    if (!path) return;

    const length = path.getTotalLength();
    path.style.setProperty('--leash-length', String(length));

    if (reduceMotion) {
      path.classList.add('is-static');
      waypoints.forEach((w) => w.classList.add('is-active'));
      return;
    }

    function update() {
      const rect = svg.getBoundingClientRect();
      const viewportH = window.innerHeight || document.documentElement.clientHeight;
      const total = rect.height + viewportH;
      const scrolled = viewportH - rect.top;
      const progress = Math.min(Math.max(scrolled / total, 0), 1);

      path.style.strokeDashoffset = String(length * (1 - progress));

      waypoints.forEach((w) => {
        const at = parseFloat(w.getAttribute('data-at') || '0');
        w.classList.toggle('is-active', progress >= at);
      });
    }

    let ticking = false;
    window.addEventListener('scroll', function () {
      if (!ticking) {
        window.requestAnimationFrame(function () {
          update();
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    update();
  });
})();
