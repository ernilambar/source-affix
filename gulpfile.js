// Config.
var rootPath   = './';
var projectURL = 'http://staging.local/';

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
	'css/**',
	'includes/**',
	'js/**',
	'views/**',
	'readme.txt',
	'class-source-affix.php',
	'class-source-affix-admin.php',
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

// Watch.
gulp.task( 'watch', function() {
    browserSync.init({
        proxy: projectURL,
        open: true
    });

    // Watch CSS files.
    gulp.watch( rootPath + 'src/styles/**/**/*.css' ).on('change',browserSync.reload);

    // Watch PHP files.
    gulp.watch( rootPath + '**/**/*.php' ).on('change',browserSync.reload);
});

// Make pot file.
gulp.task('pot', function() {
	const { run } = gulpPlugins;
	return run('wpi18n makepot --domain-path=languages --exclude=vendor,deploy').exec();
})

// Add text domain.
gulp.task('language', function() {
	const { run } = gulpPlugins;
	return run('wpi18n addtextdomain').exec();
})

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

gulp.task( 'style', gulp.series('scss'));

gulp.task( 'textdomain', gulp.series('language', 'pot'));

gulp.task( 'build', gulp.series('style', 'textdomain'));

gulp.task( 'deploy', gulp.series('clean:deploy', 'copy:deploy'));
