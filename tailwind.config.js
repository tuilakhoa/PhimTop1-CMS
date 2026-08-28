/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./themes/**/*.php",
    "./themes/**/*.js",
    "./admin/**/*.php",
    "./admin/**/*.js",
    "./includes/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        'phim-yellow': '#eab308',
        admin: {
            bg: '#0B0F19',
            panel: 'rgba(17, 24, 39, 0.7)',
            border: 'rgba(255, 255, 255, 0.08)',
            primary: '#f43f5e',
            primaryGlow: 'rgba(244, 63, 94, 0.4)',
            hover: '#2563EB',
            text: '#E2E8F0',
            muted: '#94A3B8'
        }
      },
      fontFamily: { sans: ['Inter', 'sans-serif'] },
      animation: {
          'fade-in': 'fadeIn 0.3s ease-out',
      },
      keyframes: {
          fadeIn: {
              '0%': { opacity: '0', transform: 'translateY(10px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' },
          }
      }
    },
  },
  safelist: [
    'text-red-500', 'text-yellow-500', 'text-blue-500', 'text-green-500', 'text-purple-500',
    'bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500', 'bg-purple-500',
    'translate-x-0', 'translate-x-[100%]'
  ],
  plugins: [],
}
