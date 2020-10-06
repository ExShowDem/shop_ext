// Load config
var config = require('../../../gulp-config.json');

var watchInterval = 500;

if (typeof config.watchInterval !== 'undefined') {
    watchInterval = config.watchInterval;
}

// Dependencies
var gulp        = require('gulp');
var browserSync = require('browser-sync');
var del         = require('del');
var composer    = require('gulp-composer');

var group = 'system';
var name  = 'sh404sef_observer';

var baseTask   = 'plugins.' + group + '.' + name;
var extPath    = '../extensions/plugins/' + group + '/' + name;

var wwwExtPath = config.wwwDir + '/plugins/' + group + '/' + name;

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':plugin'
	],
	function() {
	});

// Clean: plugin
gulp.task('clean:' + baseTask + ':plugin', function() {
	return del(wwwExtPath, {force : true});
});

// Copy
gulp.task('copy:' + baseTask, ['clean:' + baseTask, 'composer:' + baseTask], function() {
	return gulp.src( extPath + '/**')
		.pipe(gulp.dest(wwwExtPath));
});

// Composer
gulp.task('composer:' + baseTask, function(cb) {
	composer({"d": extPath}).on('end', cb);
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':plugin'
	],
	function() {
	});

// Watch: plugin
gulp.task('watch:' + baseTask + ':plugin', function() {
	gulp.watch(
		extPath + '/**/*',
		{ interval: watchInterval },
		['copy:' + baseTask, browserSync.reload]
	);
});
