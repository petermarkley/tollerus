module.exports = {
  content: [
    './resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        main: ['var(--tollerus-font-main)', 'ui-sans-serif', 'system-ui'],
      },
      colors: {
        tollerus: {
          bg: 'rgb(var(--tollerus-bg) / <alpha-value>)',
          surface: 'rgb(var(--tollerus-surface) / <alpha-value>)',
          text: 'rgb(var(--tollerus-text) / <alpha-value>)',
          muted: 'rgb(var(--tollerus-muted) / <alpha-value>)',
          border: 'rgb(var(--tollerus-border) / <alpha-value>)',
          primary: 'rgb(var(--tollerus-primary) / <alpha-value>)',
          ring: 'rgb(var(--tollerus-ring) / <alpha-value>)',
        },
      },
    },
  },
  plugins: [],
}