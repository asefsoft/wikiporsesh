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
        // colors: {
        //     // bg-primary
        // },
        extend: {
            // fontFamily: {
            //     sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            // },
            colors: {
                "primary": "rgb(48 86 211)"
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
