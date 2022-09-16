// Env.
require('dotenv').config();

// Config.
var rootPath = './';

// Gulp.
var gulp = require('gulp');

// Babel.
const babel = require( 'gulp-babel' );

// Rename.
const rename = require( 'gulp-rename' );

// Uglify.
const uglify = require( 'gulp-uglify' );

// SASS.
var sass = require('gulp-sass')(require('sass'));


// Plumber.
const plumber = require( 'gulp-plumber' );

// Browser sync.
var browserSync = require('browser-sync').create();


// Autoprefixer.
const autoprefixer = require( 'gulp-autoprefixer' );

// Clean CSS.
const cleanCSS = require( 'gulp-clean-css' );

// SASS.
gulp.task( 'scss', function() {
	return gulp.src( rootPath + 'src/sass/*.scss' )
		.on( 'error', sass.logError )
		.pipe( plumber() )
		.pipe( sass() )
		.pipe( autoprefixer() )
		.pipe( gulp.dest( 'assets/css' ) )
		.pipe( cleanCSS() )
		.pipe( rename( { extname: '.min.css' } ) )
		.pipe( gulp.dest( 'assets/css' ) );
} );

// Scripts.
gulp.task( 'scripts', function() {
	return gulp.src( [ rootPath + 'src/scripts/*.js' ] )
		.pipe( babel( {
			presets: [ '@babel/env' ],
		} ) )
		.pipe( gulp.dest( 'assets/js' ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( uglify() )
		.pipe( gulp.dest( 'assets/js' ) );
} );

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
