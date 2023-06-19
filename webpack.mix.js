const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

if (process.env.MIX_ENV == 'local')
    mix.alias({
        vue$: path.join(__dirname, 'node_modules/vue/dist/vue.js')
    });

if (process.env.MIX_ENV == 'production')
    mix.alias({
        vue$: path.join(__dirname, 'node_modules/vue/dist/vue.min.js')
    });

mix.alias({
    global_json$: path.join(__dirname, 'resources/json/json.js'),
    moe_json$: path.join(__dirname, 'resources/json/emoji_moe.js')
});


mix.js('resources/js/app.js', 'public/js').vue()
mix.sass('resources/css/app.scss', 'public/css')
// mix.extract(['global_json', 'moe_json'], 'public/js/json.js')//这个没用！
mix.extract()

if (mix.inProduction()) {
    mix.version();
}

mix.disableSuccessNotifications();//禁用成功提醒