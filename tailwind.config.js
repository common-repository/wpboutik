/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.php", "./**/assets/js/*.js"],
  corePlugins: {
    visibility: false,
    preflight: true,
    container: false
  },
  safelist: [
    {
      pattern: /grid-cols-(1|2|3|4|5|6)/,
      variants: ['lg', 'md', 'sm']
    }
  ],
  theme: {
    extend: {        
      animation: {
        blink: 'blink 1.4s infinite both',
        fade: 'fade 1.4s infinite both',
        scale: 'scale 2s infinite',
        perspective: 'perspective 1.2s infinite',
        fadeIn: 'fadeIn 1.2s ease-in-out infinite both',
      },
      keyframes: {
        blink: {
          '0%': {
            opacity: '0.2',
          },
          '20%': {
            opacity: '1',
          },
          '100%': {
            opacity: ' 0.2',
          },
        },
        fade: {
          '0%, 100%': {
            opacity: '1',
          },
          '50%': {
            opacity: ' 0.3',
          },
        },
        fadeIn: {
          '0%, 39%, 100%': {
            opacity: '0',
          },
          '40%': {
            opacity: '1',
          },
        },
        scale: {
          '0%, 100%': {
            transform: 'scale(1.0)',
          },
          '50%': {
            transform: 'scale(0)',
          },
        },
        perspective: {
          '0%': { transform: 'perspective(120px)' },
          ' 50%': { transform: 'perspective(120px) rotateY(180deg)' },
          '100%': { transform: 'perspective(120px) rotateY(180deg)  rotateX(180deg)' },
        },
      },
      maxHeight: {
        '104': '26rem',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
  important: true,
}
