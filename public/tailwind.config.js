/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "../**/*.{html,js,php}",
    "!./node_modules",
  ],
  theme: {
    extend: {
      colors: {
        'tollens': '#1133A6', //tom de azul
        'kansai':'#6f848c', //tom de cinza
        'resene':'#cbd4d4', //cinza mais claro
        'ghostwhite':'#F8F8FF', //branco
      }
    },
  },
  plugins: [],
}