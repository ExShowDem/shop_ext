var gulp = require('gulp');

// Load config
var config = require('../../gulp-config.json');

var watchInterval = 500;
if (typeof config.watchInterval !== 'undefined') {
	watchInterval = config.watchInterval;
}

// Dependencies
var browserSync = require('browser-sync');
var del         = require('del');

var baseTask = 'webservices.redshopb';
var extPath  = './../extensions/webservices/com_redshopb';

var wwwExtPath = config.wwwDir + '/media/redcore/webservices/com_redshopb';

// Clean
gulp.task('clean:' + baseTask, function() {
	return del(wwwExtPath, {force : true});
});

// Copy
gulp.task('copy:' + baseTask, ['clean:' + baseTask, 'copy:libraries.redcore:media'], function() {
	return gulp.src(extPath + '/**')
		.pipe(gulp.dest(wwwExtPath));
});

// Watch
gulp.task('watch:' + baseTask, function() {
	return gulp.watch(
		extPath + '/**/*',
		{ interval: watchInterval },
		['copy:' + baseTask, browserSync.reload]
	);
});
