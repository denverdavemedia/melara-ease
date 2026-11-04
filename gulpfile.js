var gulp = require('gulp');
var concat = require('gulp-concat');
var del = require('del');
var replace = require('gulp-replace');
var uglify = require('gulp-uglify-es').default;
var execFile = require('child_process').execFile;

/* Melara build: page scripts assets/js/<page>.js -> public/js/<page>.js, Tailwind -> public/css/app.css */

function del_js(file) {
  return del(['public/js/' + file]);
}

// Cache busting: refresh ?t=<number> in the site's own pages only
// (never vendor/, node_modules/ or the src submodule).
gulp.task('cache-bust', function () {
  var cbString = new Date().getTime();
  return gulp
    .src(['*.php', 'includes/*.php'], { base: '.' })
    .pipe(replace(/t=\d+/g, function () {
      return 't=' + cbString;
    }))
    .pipe(gulp.dest('.'));
});

// Tailwind v4: assets/css/tailwind.css (theme + @source globs) -> public/css/app.css, minified
gulp.task('tailwind-css', function (done) {
  var cli = __dirname + '/node_modules/@tailwindcss/cli/dist/index.mjs';
  execFile(process.execPath, [cli, '-i', 'assets/css/tailwind.css', '-o', 'public/css/app.css', '--minify'], function (err, stdout, stderr) {
    if (err) {
      console.error(stderr);
    }
    done(err);
  });
});

gulp.task('index-js', function () {
  var file = 'index.js';
  del_js(file);
  return gulp.src([
      'assets/js/index.js',
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'));
});

gulp.task('checkout-js', function () {
  var file = 'checkout.js';
  del_js(file);
  return gulp.src([
      'src/common/js/jquery-3.4.1.min.js',
      'src/common/js/jquery.mask.min.js',
      'src/common/js/intltelinput/utils.js',
      'src/common/js/intltelinput/intltelinput.min.js',
      'src/common/js/states.js',
      'src/common/js/integrated.js',
      'assets/js/checkout.js',
      "src/common/js/binWarningPopup.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'));
});

// Upsell pages (extra-seat-cushion, knee-pillow, melara-apex-pillow .php): same CRM libraries as the apex upsells (knee-pillow-js)
gulp.task('upsell-js', function () {
  var file = 'upsell.js';
  del_js(file);
  return gulp.src([
      'src/common/js/jquery-3.4.1.min.js',
      'src/common/js/integrated.js',
      'src/common/js/intlNumberFormat.js',
      'assets/js/upsell.js',
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'));
});

function watchFiles() {
  gulp.watch(
    ['assets/js/*.js'],
    gulp.series('index-js', 'checkout-js', 'upsell-js', 'tailwind-css')
  );
  gulp.watch(
    ['*.php', 'includes/**/*.php', 'assets/css/tailwind.css', 'assets/css/language-selector.css', 'assets/css/crm-overlay.css'],
    gulp.series('tailwind-css')
  );
}

gulp.task('watch', watchFiles);
gulp.task('all', gulp.series(
  'index-js',
  'checkout-js',
  'upsell-js',
  'tailwind-css'
));
