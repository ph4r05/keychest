const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .extract(['vue', 'jquery', 'jquery-ui', 'axios', 'lodash']);

mix.scripts([
    'resources/assets/js/blocs.js'
], 'public/js/misc.js');

// var bootstrap_sass = './node_modules/bootstrap-sass/';
// mix.copy(bootstrap_sass+"assets/fonts/bootstrap",'public/fonts');

// if (mix.config.inProduction) {
//     mix.version();
// }

mix.webpackConfig.watchOptions = mix.webpackConfig.watchOptions || {};
mix.webpackConfig.watchOptions.poll = 500;
mix.webpackConfig.watchOptions.ignored = '/node_modules/';
