const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    important: '#jax-tailwind',
    relative: true,
    content: [
        './formwidgets/leveleditor/assets/src/**/*.{vue,htm}',
        './formwidgets/leveleditor/partials/**/*.{htm,php}',
        './classes/engine/core/**/*.php',
        './controllers/**/*.php'
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
    corePlugins: {
        preflight: false,
    },
    darkMode: 'class'
}
