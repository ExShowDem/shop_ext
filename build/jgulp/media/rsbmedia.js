var gulp   = require('gulp');
var config = require('../../gulp-config.json');

var watchInterval = 500;
if (typeof config.watchInterval !== 'undefined') {
	watchInterval = config.watchInterval;
}

// Dependencies
var browserSync = require('browser-sync');
var del         = require('del');
var minifyCSS   = require('gulp-minify-css');
var less        = require('gulp-less');
var rename      = require('gulp-rename');

var baseTask  = 'media.rsbmedia';
var mediaPath = './media/components/com_rsbmedia';
var mediaWww  = config.wwwDir + '/media/com_rsbmedia';

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':css',
		'clean:' + baseTask + ':media'
	], function() {}
);

// Clean: Media
gulp.task('clean:' + baseTask + ':media', function(){
	return del([
		mediaWww,
		'!' + mediaWww + '/css',
		'!' + mediaWww + '/images'
	], {force: true, dryRun: true});
});

// Clean: CSS
gulp.task('clean:' + baseTask + ':css', function() {
	return del(mediaWww + '/css', {force: true});
});

// Copy: Media
gulp.task('copy:' + baseTask,
	[
		'copy:' + baseTask + ':css',
		'copy:' + baseTask + ':media'
	],
	function() {
});

// Copy: Media files
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
	return gulp.src([
			mediaPath + '/**',
			'!' + mediaPath + '/css',
			'!' + mediaPath + '/css/**',
			'!' + mediaPath + '/less',
			'!' + mediaPath + '/less/**'
		])
		.pipe(gulp.dest(mediaWww));
});

// Copy: CSS files
gulp.task('copy:' + baseTask + ':css', ['less:' + baseTask], function() {
	return gulp.src([
			mediaPath + '/css/**/*.css'
		])
		.pipe(gulp.dest(mediaWww + '/css'));
});

// LESS compiler
gulp.task('less:' + baseTask, ['clean:' + baseTask + ':css'], function() {
	// Compiler Less to CSS files
	return gulp.src(mediaPath + '/less/**/*.less')
		.pipe(less())
		.pipe(gulp.dest(mediaWww + '/css'))
		.pipe(minifyCSS())
		.pipe(rename(function (path) { path.extname = '.min.' + path.extname; }))
		.pipe(gulp.dest(mediaWww + '/css'));
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':media',
		'watch:' + baseTask + ':less',
		'watch:' + baseTask + ':css'
	],
	function() {
});

// Watch: Media
gulp.task('watch:' + baseTask + ':media', function() {
	gulp.watch([
		mediaPath + '/**',
		'!' + mediaPath + '/less',
		'!' + mediaPath + '/less/**'
	],
	{ interval: watchInterval },
	['copy:' + baseTask + ':media', browserSync.reload]);
});

// Watch: CSS
gulp.task('watch:' + baseTask + ':css', function() {
	gulp.watch([
		mediaPath + '/css/**/*.css'
	],
	{ interval: watchInterval },
	['copy:' + baseTask + ':css', browserSync.reload]);
});

// Watch: LESS
gulp.task('watch:' + baseTask + ':less', function() {
	gulp.watch([
		mediaPath + '/less/**/*.less'
	],
	{ interval: watchInterval },
	['less:' + baseTask, browserSync.reload]);
});