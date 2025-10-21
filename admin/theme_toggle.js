// Shared theme toggler for Admin pages -  Bootstrap 5 data-bs-theme
(() => {
  'use strict';

  const getStoredTheme = () => localStorage.getItem('theme');
  const setStoredTheme = (theme) => localStorage.setItem('theme', theme);
  // get preferred theme
  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme) return storedTheme;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };
  // update icon
  const updateIcon = (theme) => {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;
    const isDark = theme === 'dark';
    toggle.textContent = isDark ? '🌙' : '☀';
    toggle.classList.toggle('btn-outline-light', isDark);
    toggle.classList.toggle('btn-outline-dark', !isDark);
  };
  // set theme
  const setTheme = (theme) => {
    document.documentElement.setAttribute('data-bs-theme', theme);
    updateIcon(theme);
  };

  // Set initial theme 
  setTheme(getPreferredTheme());

  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;

    // Ensure icon matches on load
    updateIcon(document.documentElement.getAttribute('data-bs-theme') || getPreferredTheme());

    toggle.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
      const next = current === 'light' ? 'dark' : 'light';
      setStoredTheme(next);
      setTheme(next);
    });
  });
})();

