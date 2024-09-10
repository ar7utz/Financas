/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "../**/*.{html,js,php}",
    "!./node_modules",
  ],
  theme: {
    extend: {
      colors: {
        'tollens': '#005485',
        'kansai':'#6f848c',
        'resene':'#cbd4d4',
        'ghostwhite':'#F8F8FF',
      }
    },
  },
  plugins: [],
}