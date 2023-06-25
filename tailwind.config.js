/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
  theme: {
    extend: {
        fontFamily: {
            display: ['Merriweather', 'sans-serif'],
            body: ['Merriweather', 'sans-serif'],
            sans: ['Work Sans', 'sans-serif'],
            serif: ['Merriweather', 'serif'],
            headline: ['Work Sans', 'sans-serif'],
        },
    },
  },
  plugins: [require('@tailwindcss/typography')],
}

