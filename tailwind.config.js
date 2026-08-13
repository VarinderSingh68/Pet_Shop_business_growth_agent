/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/Views/**/*.php'],
  theme: {
    extend: {
      colors: {
        ink: '#12141c',
        paper: '#f6f7f2',
        // Dynamic per-request: the admin picks an accent from a curated set
        // (see theme_accent_hex() in app/Core/helpers.php), which is written
        // into a `--leash` CSS custom property on :root by each layout.
        // Routing the utility color through var() keeps that per-request
        // theming working with a statically-built stylesheet.
        leash: 'var(--leash, #e8492a)',
        tennis: '#f2b705',
        fern: '#1f5f4a',
        mist: '#dde3de',
        danger: '#dc2626',
        info: '#2563eb',
        plum: '#7c3aed',
        sky: '#2f7fb8',
      },
      fontFamily: {
        display: ['"Bricolage Grotesque"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
