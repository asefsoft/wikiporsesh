const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {

    // presets: [
    //      // require('./vendor/wireui/wireui/tailwind.config.js')
    // ],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',

        './vendor/wireui/wireui/resources/**/*.blade.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/View/**/*.php'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Vazir FD', ...defaultTheme.fontFamily.sans],
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
                }
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
