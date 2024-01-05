const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    content: [
        './classes/engine/client/**/*.{htm,js,vue}',
        '../../themes/**/*.htm'
    ],
    theme: {
        extend: {
            fontFamily: {
                'sans': ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                transparent: 'transparent',
                current: 'currentColor',
            },
        }
    },
    darkMode: 'class'
}
