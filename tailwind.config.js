/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./templates/**/*.html.twig",
    "./assets/**/*.js",
    "./assets/**/*.css",
    "./node_modules/flowbite/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'wellcare': {
          '50': '#e6f7f5',
          '100': '#ccf0eb',
          '200': '#99e1d7',
          '300': '#66d2c3',
          '400': '#33c3af',
          '500': '#00A790',
          '600': '#008674',
          '700': '#006458',
          '800': '#00433c',
          '900': '#00211e',
        },
        'wellcare-light': {
          '50': '#f0fffd',
          '100': '#e1fffb',
          '200': '#c3fff7',
          '300': '#a5fff3',
          '400': '#87ffef',
          '500': '#00DBB6',
          '600': '#00af91',
          '700': '#00836c',
          '800': '#005747',
          '900': '#002b23',
        },
        'wellcare-dark': {
          '50': '#e6ebf5',
          '100': '#ccd6eb',
          '200': '#99add7',
          '300': '#6685c3',
          '400': '#335caf',
          '500': '#002F5C',
          '600': '#00264a',
          '700': '#001c37',
          '800': '#001325',
          '900': '#000912',
        },
        'wellcare-bg': {
          '50': '#ffffff',
          '100': '#f5f9ff',
          '200': '#ebf4ff',
          '300': '#e0eefe',
          '400': '#d6e9fe',
          '500': '#cce3fd',
        }
      },
      fontFamily: {
        'sans': ['Open Sans', 'Roboto', 'system-ui', '-apple-system', 'sans-serif'],
        'display': ['Montserrat', 'Open Sans', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'heartbeat': 'heartbeat 1.5s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        heartbeat: {
          '0%': { transform: 'scale(1)' },
          '50%': { transform: 'scale(1.1)' },
          '100%': { transform: 'scale(1)' },
        },
      },
    },
  },
  plugins: [
    require('flowbite/plugin'),
  ],
}
