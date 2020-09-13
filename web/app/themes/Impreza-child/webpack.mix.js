const mix = require('laravel-mix');

mix
  .js('assets/js/app.js', 'dist/js')
  .sass('assets/scss/app.scss', 'dist/css')
  .options({ processCssUrls: false })
  .copyDirectory('assets/images', 'dist/images')
  .copyDirectory('assets/fonts', 'dist/fonts')
  .browserSync({
    proxy: {
      target: 'actvonline.pub.localhost',
    },
    open: false,
    files: [
      'dist/css/{*,**/*}.css',
      'dist/js/{*,**/*}.js',
      '{*,**/*}.php',
    ],
  });
