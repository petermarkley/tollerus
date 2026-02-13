module.exports = {
  content: [
    './resources/views/**/*.blade.php',
  ],
  important: '#tollerus_root',
  theme: {
    extend: {
      fontFamily: {
        main: ['var(--tollerus-font-main)', 'ui-sans-serif', 'system-ui'],
        mono: ['var(--tollerus-font-mono)', 'ui-monospace', 'monospace'],
      },
      colors: {
        tollerus: {
          bg: 'rgb(var(--tollerus-bg) / <alpha-value>)',
          surface: 'rgb(var(--tollerus-surface) / <alpha-value>)',
          'surface-inactive': 'rgb(var(--tollerus-surface-inactive) / <alpha-value>)',
          'surface-hover': 'rgb(var(--tollerus-surface-hover) / <alpha-value>)',
          text: 'rgb(var(--tollerus-text) / <alpha-value>)',
          'text-inverse': 'rgb(var(--tollerus-text-inverse) / <alpha-value>)',
          'text-irregular': 'rgb(var(--tollerus-text-irregular) / <alpha-value>)',
          muted: 'rgb(var(--tollerus-muted) / <alpha-value>)',
          border: 'rgb(var(--tollerus-border) / <alpha-value>)',
          primary: 'rgb(var(--tollerus-primary) / <alpha-value>)',
          'primary-hover': 'rgb(var(--tollerus-primary-hover) / <alpha-value>)',
          secondary: 'rgb(var(--tollerus-secondary) / <alpha-value>)',
          'secondary-hover': 'rgb(var(--tollerus-secondary-hover) / <alpha-value>)',
          ring: 'rgb(var(--tollerus-ring) / <alpha-value>)',
        },
      },
      typography: {
        DEFAULT: {
          css: {
            maxWidth: '100ch',
            color: 'rgb(var(--tollerus-text))',
            a: {
              color: 'rgb(var(--tollerus-primary))',
              '&:hover': {
                color: 'rgb(var(--tollerus-primary-hover))',
              },
            },
            p: {
              'text-indent': '1rem',
              'margin-top': '0.25em',
              'margin-bottom': '0.25em',
            },
            div: {
              'margin-top': '1.5em',
              'margin-bottom': '1.5em',
            },
          },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}