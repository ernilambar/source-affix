// Env.
require('dotenv').config()

// Config.
var rootPath = './';

// Gulp.
var gulp = require( 'gulp' );

// Gulp plugins.
var gulpPlugins = require( 'gulp-load-plugins' )();

// File system.
var fs = require('fs');

// Package.
var pkg = JSON.parse(fs.readFileSync('./package.json'));

// Delete.
var del = require('del');

// Browser sync.
var browserSync = require('browser-sync').create();

// Deploy files list.
var deploy_files_list = [
	'assets/**',
	'includes/**',
	'languages/**',
	'vendor/**',
	'readme.txt',
	pkg.main_file
];

// SASS.
gulp.task('scss', function () {
    const { autoprefixer, cleanCss, notify, plumber, sass, sassGlob, uglify, rename, sourcemaps, filter } = gulpPlugins;
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
    gulp.watch( rootPath + 'src/sass/**/**/*.scss', gulp.series( 'scss' ) ).on('change',browserSync.reload);

    // Watch JS files.
    gulp.watch( rootPath + 'src/scripts/**/**/*.js', gulp.series( 'scripts' ) ).on('change',browserSync.reload);

    // Watch PHP files.
    gulp.watch( rootPath + '**/**/*.php' ).on('change',browserSync.reload);
});

// Clean deploy folder.
gulp.task('clean:deploy', function() {
    return del('deploy')
});

// Copy to deploy folder.
gulp.task('copy:deploy', function() {
	const { zip } = gulpPlugins;
	return gulp.src(deploy_files_list,{base:'.'})
	    .pipe(gulp.dest('deploy/' + pkg.name))
	    .pipe(zip(pkg.name + '.zip'))
	    .pipe(gulp.dest('deploy'))
});

// Tasks.
gulp.task( 'default', gulp.series('watch'));

gulp.task( 'styles', gulp.series('scss'));

gulp.task( 'build', gulp.series('styles', 'scripts'));

gulp.task( 'deploy', gulp.series('clean:deploy', 'copy:deploy'));
