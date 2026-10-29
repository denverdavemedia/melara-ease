var gulp = require('gulp');
var concat = require('gulp-concat');
var cleanCss = require('gulp-clean-css');
var rev = require('gulp-rev');
var del = require('del');
var replace = require('gulp-replace');
var uglify = require('gulp-uglify-es').default;


function del_js(file) {
  return del([
      'public/js/' + file
  ]);
}

// global task primarily used for cache busting
gulp.task("cache-bust", function () {
  var cbString = new Date().getTime();
  return gulp
    .src(["**/*.php"])
    .pipe(
      replace(/t=\d+/g, function () {
        return "t=" + cbString;
      })
    )
    .pipe(gulp.dest("."));
});


//task------------------------------------------------------------------------------
gulp.task('index-js', function () {
  var file = 'index.js';
  del_js(file);
  return gulp.src([
      'src/common/js/jquery-3.4.1.min.js',
      'src/common/js/integrated.js',
      'assets/js/index.js',
      "assets/js/advertizerEf.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('index2-js', function () {
  var file = 'index2.js';
  del_js(file);
  return gulp.src([
      'src/common/js/jquery-3.4.1.min.js',
      'src/common/js/integrated.js',
      'assets/js/index.js',
      "assets/js/advertizerEf2.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('advertizerEf-js', function () {
  var file = 'advertizerEf.js';
  del_js(file);
  return gulp.src([
      'src/common/js/jquery-3.4.1.min.js',
      "assets/js/advertizerEf.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});


gulp.task('checkout-js', function () {
  var file = 'checkout.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",
      //"src/common/js/jquery.inputmask.min.js",
      "src/common/js/self-host-address-suggestion.js",
      "src/common/js/jquery.mask.min.js",
      'src/common/js/intltelinput/utils.js',
      'src/common/js/intltelinput/intltelinput.min.js',
      "src/common/js/states.js",
      "src/common/js/integrated.js",
      "src/common/js/klarnaPayment.js",
      "assets/js/checkout.js",
      //"src/common/js/address.google.autocomplete.js",
      "assets/js/advertizerEf.js",
      "assets/js/timer.js",
      "assets/js/purchasePopup.js",

  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('melarapro-pillow-js', function () {
  var file = 'melarapro-pillow.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/melarapro-pillow.js",
      "assets/js/timer.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('dust-pillow-cover-js', function () {
  var file = 'dust-pillow-cover.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/dust-pillow-cover.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('dust-pillow-cover-new-js', function () {
  var file = 'dust-pillow-cover-new.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/dust-pillow-cover-new.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('cloud-air-cover-new-js', function () {
  var file = 'cloud-air-cover-new.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/cloud-air-cover-new.js",
      "assets/js/timer.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('anti-dust-cover-popup-js', function () {
  var file = 'anti-dust-cover-popup.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/anti-dust-cover-popup.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('thermo-pillow-cover-js', function () {
  var file = 'thermo-pillow-cover.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/thermo-pillow-cover.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('knee-pillow-js', function () {
  var file = 'knee-pillow.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/knee-pillow.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('sleeping-mask-js', function () {
  var file = 'sleeping-mask.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/sleeping-mask.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('warranty-js', function () {
  var file = 'warranty.js';
  del_js(file);
  return gulp.src([    
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "assets/js/slick.min.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/warranty.js",
      "assets/js/advertizerEf.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('sleep-spray-js', function () {
  var file = 'sleep-spray.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/sleep-spray.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('gummies-tea-js', function () {
  var file = 'gummies-tea.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/gummies-tea.js",
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('magnetic-nasal-strip', function () {
  var file = 'magnetic-nasal-strip.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",     
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/magnetic-nasal-strip.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('sleep-patch-js', function () {
  var file = 'sleep-patch.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/sleep-patch.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('spray-patch-js', function () {
  var file = 'spray-patch.js';
  del_js(file);
  return gulp.src([
      "src/common/js/jquery-3.4.1.min.js",
      "src/common/js/integrated.js",
      "src/common/js/intlNumberFormat.js",
      "assets/js/spray-patch.js",
      "assets/js/timer.js"
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});

gulp.task('thankyou-js', function () {
  var file = 'thankyou.js';
  del_js(file);
  return gulp.src([
      'src/common/js/jquery-3.4.1.min.js',
      'src/common/js/integrated.js',
      "src/common/js/intlNumberFormat.js",
      "src/common/js/trustrating.js",
      'assets/js/thankyou.js'
  ])
    .pipe(concat(file))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))

});
function watchFiles() {
  gulp.watch(
    ["src/common/js/*.js", "assets/js/*.js"], // 👈 combined both paths
    gulp.series(
      'index2-js',
      'advertizerEf-js',  
      'index-js',   
      "checkout-js",
      "melarapro-pillow-js",
      "dust-pillow-cover-js",
      "thermo-pillow-cover-js",
      'knee-pillow-js',
      'magnetic-nasal-strip',
      'sleeping-mask-js',
      'sleep-patch',
      'warranty-js',
      'thankyou-js',
      "cache-bust"
    )
  );
}

//-----------------------------------------------------------------------------------
gulp.task("watch", watchFiles);
gulp.task('all', gulp.series(
  'index2-js',
  'advertizerEf-js', 
  'index-js',
  'checkout-js',
  "melarapro-pillow-js",
  "dust-pillow-cover-js",
  "thermo-pillow-cover-js",
  "dust-pillow-cover-new-js",
  "cloud-air-cover-new-js",
  "anti-dust-cover-popup-js",
  'sleeping-mask-js',
  'magnetic-nasal-strip',
  'sleep-spray-js',
  'gummies-tea-js',
  'sleep-patch-js',
  'spray-patch-js',
  'knee-pillow-js',
  'warranty-js',
  'thankyou-js',
  'cache-bust'
));



