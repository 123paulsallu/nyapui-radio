// Simple mobile menu toggle for NYAPUI navbar
(function(){
  const openMenu = (menu) => {
    menu.removeAttribute('hidden');
    document.documentElement.classList.add('nav-open');
  };
  const closeMenu = (menu) => {
    menu.setAttribute('hidden', '');
    document.documentElement.classList.remove('nav-open');
  };

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.hamburger');
    if (btn) {
      const menu = document.getElementById(btn.getAttribute('aria-controls') || 'mobile-menu');
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        btn.setAttribute('aria-expanded', 'false');
        closeMenu(menu);
      } else {
        btn.setAttribute('aria-expanded', 'true');
        openMenu(menu);
      }
      return;
    }

    const closeBtn = e.target.closest('.mobile-close');
    if (closeBtn) {
      const menu = closeBtn.closest('.mobile-menu');
      const ham = document.querySelector('.hamburger');
      if (ham) ham.setAttribute('aria-expanded', 'false');
      closeMenu(menu);
      return;
    }

    // backdrop click
    if (e.target.matches('.mobile-menu-backdrop') || e.target.closest('[data-close="true"]')) {
      const menu = e.target.closest('.mobile-menu');
      if (menu) {
        const ham = document.querySelector('.hamburger');
        if (ham) ham.setAttribute('aria-expanded', 'false');
        closeMenu(menu);
      }
    }
  });

  // close on escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const menu = document.getElementById('mobile-menu');
      if (menu && !menu.hasAttribute('hidden')) {
        const ham = document.querySelector('.hamburger');
        if (ham) ham.setAttribute('aria-expanded', 'false');
        menu.setAttribute('hidden', '');
      }
    }
  });
})();
