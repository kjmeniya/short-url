(() => {
  'use strict'

  const getStoredTheme = () => localStorage.getItem('theme')
  const setStoredTheme = theme => localStorage.setItem('theme', theme)

  // Custom event dispatch 
  // const setStoredTheme = (theme) => { 
  //   localStorage.setItem('theme', theme);
  //   const event = new CustomEvent('themeChanged');
  //   document.dispatchEvent(event)
  // }

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme()
    if (storedTheme) {
      return storedTheme
    }

    // Check if we're in admin area and get admin default theme
    const isAdminArea = window.location.pathname.startsWith('/admin');
    if (isAdminArea && window.adminDefaultTheme) {
      if (window.adminDefaultTheme === 'auto') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        setStoredTheme(systemTheme);
        return systemTheme;
      } else {
        setStoredTheme(window.adminDefaultTheme);
        return window.adminDefaultTheme;
      }
    }

    // Fallback to system preference
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    setStoredTheme(systemTheme);
    return systemTheme;
  }

  const setTheme = theme => {
    document.documentElement.setAttribute('data-bs-theme', theme)
  }

  setTheme(getPreferredTheme())

  const showActiveTheme = (theme) => {
    const themeSwitcher = document.querySelector('#theme-switcher')

    if (!themeSwitcher) {
      return
    }

    const box = document.querySelector('.box');

    if(theme === 'dark') {
      box.classList.remove('light')
      box.classList.add('dark')
    }
    if(theme ==='light') {
      box.classList.remove('dark')
      box.classList.add('light')
    }

  }

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const storedTheme = getStoredTheme()
    if (storedTheme !== 'light' && storedTheme !== 'dark') {
      setTheme(getPreferredTheme())
    }
  })

  window.addEventListener('DOMContentLoaded', () => {
    showActiveTheme(getPreferredTheme())

    const themeSwitcher = document.querySelector('#theme-switcher')

    if (themeSwitcher) {
      if (getStoredTheme() == 'dark') {
        themeSwitcher.checked = true;
      } else if (getStoredTheme() == 'light') {
        themeSwitcher.checked = false;
      }

      themeSwitcher.addEventListener('change', function() {
        const theme = this.checked ? 'dark' : 'light'
        setStoredTheme(theme)
        setTheme(theme)
        showActiveTheme(theme)
      })
    }

  })



  // Theme switch based on parameters from query string
  const urlParams = new URLSearchParams(window.location.search);
  const themeParam = urlParams.get('theme');
  if ( (themeParam === 'light') || (themeParam === 'dark')) {
    setStoredTheme(themeParam)
    setTheme(themeParam)
    showActiveTheme(themeParam)
  }

})()