import './bootstrap'

import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

window.addEventListener('scroll', () => {
  const nav = document.querySelector('nav')
  const header = document.querySelector('header.bg-gray-800')
  const scrolled = window.pageYOffset > 60

  nav.classList.toggle('h-12', scrolled)
  nav.classList.toggle('h-16', !scrolled)

  if (header) {
    header.classList.toggle('py-3', scrolled)
    header.classList.toggle('py-6', !scrolled)
  }
})
