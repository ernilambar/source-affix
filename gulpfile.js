// Env.
require('dotenv').config();

// Config.
var rootPath = './';

// Gulp.
var gulp = require('gulp');

// Gulp plugins.
var gulpPlugins = require('gulp-load-plugins')();

// SASS.
var sass = require('gulp-sass')(require('sass'));

// Browser sync.
var browserSync = require('browser-sync').create();

// SASS.
gulp.task('scss', function () {
    const { autoprefixer, cleanCss, plumber, sassGlob, rename, sourcemaps, filter } = gulpPlugins;
    return gulp.src(rootPath + 'src/sass/*.scss')
        .on('error', sass.logError)
        .pipe(sourcemaps.init())
        .pipe(plumber())
        .pipe(sassGlob())
        .pipe(sass())
        .pipe(autoprefixer('last 4 version'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('assets/css'))
        .pipe(filter('**/*.css'))
        .pipe(cleanCss())
        .pipe(rename({ extname: '.min.css' }))
        .pipe(gulp.dest('assets/css'))
});

// Scripts.
gulp.task('scripts', function() {
    const { plumber, rename, uglify, jshint } = gulpPlugins;
    return gulp.src( [rootPath + 'src/scripts/*.js'] )
        .pipe(jshint())
        .pipe(jshint.reporter('default'))
        .pipe(jshint.reporter('fail'))
        .pipe(plumber())
        .pipe(gulp.dest('assets/js'))
        .pipe(rename({suffix: '.min'}))
        .pipe(uglify())
        .pipe(gulp.dest('assets/js'))
});

// Watch.
gulp.task( 'watch', function() {
    browserSync.init({
        proxy: process.env.DEV_SERVER_URL,
        open: true
    });

    // Watch SASS files.
    gulp.watch(rootPath + 'src/sass/**/**/*.scss', gulp.series( 'scss' )).on('change',browserSync.reload);

    // Watch JS files.
    gulp.watch(rootPath + 'src/scripts/**/**/*.js', gulp.series( 'scripts' )).on('change',browserSync.reload);

    // Watch PHP files.
    gulp.watch(rootPath + '**/**/*.php').on('change',browserSync.reload);
});

// Tasks.
gulp.task( 'default', gulp.series('watch'));

gulp.task( 'styles', gulp.series('scss'));

gulp.task( 'build', gulp.series('styles', 'scripts'));
