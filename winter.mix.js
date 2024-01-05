const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');

mix.setPublicPath(__dirname);

mix
    // Backend
    .postCss(
        './formwidgets/leveleditor/assets/src/css/leveleditor.css',
        './formwidgets/leveleditor/assets/dist/css/leveleditor.css',
        [
            tailwindcss('./tailwind.backend.config.js'),
            require('tailwindcss/nesting'),
            require('postcss-import')
        ]
    )
    .js(
        'formwidgets/leveleditor/assets/src/js/leveleditor.js',
        'formwidgets/leveleditor/assets/dist/js/leveleditor.js'
    )
    // Client
    .postCss(
        'classes/engine/client/css/app.css',
        'assets/app.css',
        [
            tailwindcss('./tailwind.frontend.config.js'),
            require('tailwindcss/nesting'),
            require('postcss-import'),
        ]
    )
    .js(
        'classes/engine/client/client.js',
        'assets/client.js'
    )
    .vue();


mix.webpackConfig({
    stats: {
        children: true
    }
});
