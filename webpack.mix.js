const { mix } = require('laravel-mix');
const GitRevisionPlugin = require('git-revision-webpack-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const webpack = require('webpack');

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
// __webpack_public_path__ = '/';
mix.js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/app-user.js', 'public/js/app-user.js')
    .js('resources/assets/js/polyinit.js', 'public/js/polyinit.js')
    .js('resources/assets/js/polyapp.js', 'public/js/polyapp.js')
    .js('resources/assets/js/polyapp-user.js', 'public/js/polyapp-user.js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .sass('resources/assets/sass/app-landing.scss', 'public/css')

    // Admin LTE
    .less('node_modules/bootstrap-less/bootstrap/bootstrap.less', 'public/css/bootstrap.css')
    .less('resources/assets/less/adminlte-app.less','public/css/adminlte-app.css')
    .less('node_modules/toastr/toastr.less','public/css/toastr.css')
    .combine([
        'public/css/app.css',
        'node_modules/admin-lte/dist/css/skins/_all-skins.css',
        'public/css/adminlte-app.css',
        'node_modules/icheck/skins/square/blue.css',
        'public/css/toastr.css',
        'public/css/bootstrap-switch.css',
        'node_modules/sweetalert2/dist/sweetalert2.css',
        'node_modules/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.css'
    ], 'public/css/all.css')
    .combine([
        'public/css/app-landing.css',
        'public/css/toastr.css'
    ], 'public/css/all-landing.css')

    //APP RESOURCES
    .copy('resources/assets/img/*.*','public/img', false)
    .copy('resources/assets/images/*.*','public/images', false)

    //VENDOR RESOURCES
    .copy('node_modules/font-awesome/fonts/*.*','public/fonts/')
    .copy('node_modules/ionicons/dist/fonts/*.*','public/fonts/')
    .copy('node_modules/admin-lte/bootstrap/fonts/*.*','public/fonts/bootstrap')
    .copy('node_modules/admin-lte/dist/css/skins/*.*','public/css/skins')
    .copy('node_modules/admin-lte/dist/img','public/img')
    .copy('node_modules/admin-lte/plugins','public/plugins')
    .copy('node_modules/icheck/skins/square/blue.png','public/css')
    .copy('node_modules/icheck/skins/square/blue@2x.png','public/css')
    .extract([
        'es6-promise',
        'vue', 'vuex', 'vue-router', 'vue-resource', 'vue-events',
        'jquery', 'jquery-ui', 'lodash', 'axios',
        'sweetalert2', 'toastr', 'pluralize',
        'moment', 'moment-timezone'])
    .sourceMaps();

mix.babel([
    'resources/assets/js/blocs.js',
], 'public/js/misc.js');

if (mix.inProduction()) {
    mix.version();
}

mix.webpackConfig({
    watchOptions: {
        aggregateTimeout: 1000,
        ignored: '/node_modules/',
    },
    output: {
        filename: '[name].js',
        chunkFilename: 'js/[name].' + (mix.inProduction() ? '[chunkhash].' : '') + 'app.js',
        publicPath: '/'
    },
    plugins: [
        new CleanWebpackPlugin(['public/js'], {verbose: false}),
        new webpack.BannerPlugin({
            banner: new GitRevisionPlugin().version(),
        }),
        new webpack.optimize.MinChunkSizePlugin({
            minChunkSize: 10000 // Minimum number of characters
        }),
        // new webpack.optimize.CommonsChunkPlugin({
        //     names: ['es6-promise'],
        //     chunks: ['es6-promise'],
        //     filename: 'js/[name].' + (mix.inProduction() ? '[chunkhash].' : '') + 'js',
        //     //minChunks: Infinity,
        // }),
    ]
});

