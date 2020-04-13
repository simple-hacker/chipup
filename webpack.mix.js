let mix = require('laravel-mix');

mix.webpackConfig({
   resolve: {
      extensions: ['.js', '.vue', '.json'],
      alias: {
         '@components': __dirname + '/resources/js/components',
         '@views': __dirname + '/resources/js/views'
      },
   },
});

require('laravel-mix-tailwind');
require('laravel-mix-purgecss');


mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/setup.js', 'public/js')
   .js('resources/js/welcome.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .tailwind('./tailwind.config.js')
   .purgeCss();
