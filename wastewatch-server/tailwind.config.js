/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      fontFamily: {
        'title': ['Albert Sans', 'sans-serif'],
        'button': ['Manrope', 'sans-serif'],
      },
      colors: {
        'field': '#f0f0f0',
        'title': '#6f6f6f',
        'primary': {
          'base': '#5300d8',
          'dark': '#4800bc',
          'darker': '#4200ac',
        },
        'safe': {
          'base': '#00e400',
          'dark': '#00bd00',
          'darker': '#009500',
        },
        'warning': {
          'base': '#e08300',          
        },
        'danger': {
          'base': '#c20000',
          'dark': '#a30000',
          'darker': '#7c0000',
        },
        'table': {
          line: '#d7d7d7',
          bg: '#eaeaea',
        },
        'message': {
          bg: '#fafafa',
          secondary: '#404040',
        }
      }
    },
  },
  plugins: [],
}

