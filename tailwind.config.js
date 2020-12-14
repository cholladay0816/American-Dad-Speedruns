const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: [
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'dark':'#1D1D1D',
                'darker':'#101010',
                'darkest':'#0D0D0D',
            },
            spacing: {
                '128': '32rem',
                '144': '36rem',
            },
            opacity: {
                '10': '0.1',
                '20': '0.2',
                '30': '0.3',
                '40': '0.4',
                '60': '0.6',
                '70': '0.7',
                '80': '0.8',
                '90': '0.9'
            }
        },
    },

    variants: {
        opacity: ['responsive', 'hover', 'focus', 'disabled'],
    },

    plugins: [require('@tailwindcss/ui')],
};
