const defaultTheme = require('tailwindcss/defaultTheme');


/** @type {import('tailwindcss').Config} */
module.exports = {

    // presets: [
    //      // require('./vendor/wireui/wireui/tailwind.config.js')
    // ],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        // './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',

        // './vendor/wireui/wireui/resources/**/*.blade.php',
        // './vendor/wireui/wireui/ts/**/*.ts',
        // './vendor/wireui/wireui/src/View/**/*.php'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['IRANSans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "primary": {
                    DEFAULT: "rgb(48 86 211)",
                    "dark" : "#2244b7",
                    '50': '#f1f5fd',
                    '100': '#dfe9fa',
                    '200': '#c6d8f7',
                    '300': '#9fbff1',
                    '400': '#719de9',
                    '500': '#507be1',
                    '600': '#3056d3',
                    '700': '#324cc3',
                    '800': '#2e3f9f',
                    '900': '#2a397e',
                },
                'amaranth': {
                    '50': '#fff0f1',
                    '100': '#ffe3e4',
                    '200': '#ffcbd0',
                    '300': '#ffa0a8',
                    '400': '#ff6b7a',
                    '500': '#fb3850',
                    '600': '#eb2346',
                    '700': '#c50b30',
                    '800': '#a50c2f',
                    '900': '#8d0e2f',
                },

                'cerise-red': {
                    '50': '#fef2f4',
                    '100': '#fde6e9',
                    '200': '#fbd0d9',
                    '300': '#f7aab9',
                    '400': '#f27a93',
                    '500': '#e63f66',
                    '600': '#d42a5b',
                    '700': '#b21e4b',
                    '800': '#951c45',
                    '900': '#801b40',
                },
            }
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        // require("tailwindcss-flip")
        // require("daisyui")
    ],

    // daisyUI config
    // daisyui: {
    //     styled: true,
    //     base: true,
    //     utils: true,
    //     logs: true,
    //     rtl: true,
    //     prefix: "",
    //     darkTheme: "dark",
    //     themes: ["aqua", "dark",]
    // },
};
