var gulp = require('gulp');
var fs   = require('fs');

var config = require('../../gulp-config.json');

var watchInterval = 500;
if (typeof config.watchInterval !== 'undefined') {
	watchInterval = config.watchInterval;
}

var extPath   = './../';

// Dependencies
var browserSync = require('browser-sync');
var del         = require('del');

var baseTask  = 'cli.redshopb';

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':cli'
	],
	function() {
		return true;
});

// Clean: cli
gulp.task('clean:' + baseTask + ':cli', function(cb) {
	return del(config.wwwDir + '/cli/com_redshopb', {force : true});
});

// Copy
gulp.task('copy:' + baseTask,
	[
		'copy:' + baseTask + ':cli'
	],
	function() {
		return true;
});

// Copy: CLI
gulp.task('copy:' + baseTask + ':cli', ['clean:' + baseTask + ':cli'], function() {
	return gulp.src(extPath + 'extensions/cli/com_redshopb/**')
		.pipe(gulp.dest(config.wwwDir + '/cli/com_redshopb'));
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':cli'
	],
	function() {
		return true;
});

// Watch: CLI
gulp.task('watch:' + baseTask + ':cli', function() {
	gulp.watch([
		extPath + 'extensions/cli/com_redshopb/**/*'
	], { interval: watchInterval },
	['copy:' + baseTask + ':cli', browserSync.reload]);
});
