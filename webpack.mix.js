let mix = require('laravel-mix');

mix.webpackConfig({
   resolve: {
      extensions: ['.js', '.vue', '.json'],
      alias: {
         '@modules': __dirname + '/resources/js/store/modules',
         '@store': __dirname + '/resources/js/store',
         '@components': __dirname + '/resources/js/components',
         '@views': __dirname + '/resources/js/views',
         '@mixins': __dirname + '/resources/js/mixins',
         '@' : __dirname + '/resources/js',
      },
   },
});

// require('laravel-mix-tailwind');

const tailwindcss = require('tailwindcss');

require('laravel-mix-purgecss');


mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/setup.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .options({
         processCssUrls: false,
         postCss: [ tailwindcss('./tailwind.config.js') ],
         terser: {
            extractComments: false // Disable Licence files
         }
   });
