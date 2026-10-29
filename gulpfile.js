var gulp = require('gulp');
var concat = require('gulp-concat');
var del = require('del');
var replace = require('gulp-replace');
var uglify = require('gulp-uglify-es').default;

/* ------------------------------------------------------------------
 * Melara Seat Cushion build (adapted from shopmelara-apex/gulpfile.js)
 * Page scripts live in assets/js/<page>.js and are bundled + minified
 * into public/js/<page>.js, which the pages load with ?t=<timestamp>.
 *
 * The checkout bundle starts with the apex CRM libraries from the src
 * submodule (same list as shopmelara-apex checkout-js); assets/js/checkout.js
 * drives them when a campaign is set in integrated/config_us.php.
 * ------------------------------------------------------------------ */

function del_js(file) {
  return del(['public/js/' + file]);
}

// Cache busting: refresh ?t=<number> in the site's own pages only
// (never vendor/, node_modules/ or the src submodule).
gulp.task('cache-bust', function () {
  var cbString = new Date().getTime();
  return gulp
    .src(['*.php'])
    .pipe(replace(/t=\d+/g, function () {
      return 't=' + cbString;
    }))
    .pipe(gulp.dest('.'));
});

//task------------------------------------------------------------------------------
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
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'));
});

function watchFiles() {
  gulp.watch(
    ['assets/js/*.js'],
    gulp.series('index-js', 'checkout-js', 'cache-bust')
  );
}

//-----------------------------------------------------------------------------------
gulp.task('watch', watchFiles);
gulp.task('all', gulp.series(
  'index-js',
  'checkout-js',
  'cache-bust'
));
