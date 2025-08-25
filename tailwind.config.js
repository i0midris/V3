/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'tw-',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {},
    },
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
    }