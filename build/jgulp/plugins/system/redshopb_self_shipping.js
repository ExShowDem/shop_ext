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

var group = 'system';
var name  = 'redshopb_self_shipping';

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
gulp.task('copy:' + baseTask + ':webservices', function() {
	// Copy library files
	return gulp.src(extPath + '/extensions/webservices/**')
		.pipe(gulp.dest(config.wwwDir + "/media/redcore/webservices"));
});

// Copy
gulp.task('copy:' + baseTask, ['clean:' + baseTask], function() {
    return gulp.src([
    	extPath + '/**'
	])
        .pipe(gulp.dest(wwwExtPath));
});

// Watch
gulp.task('watch:' + baseTask,
    [
        'watch:' + baseTask + ':plugin',
		'watch:' + baseTask + ':webservices'
    ],
    function() {
    });

// Watch: plugin
gulp.task('watch:' + baseTask + ':plugin', function() {
    gulp.watch(
        [
        	extPath + '/**/*',
			'!' + extPath + '/extensions/webservices/**'
		],
        { interval: watchInterval },
        ['copy:' + baseTask, browserSync.reload]
    );
});

// Watch: plugin
gulp.task('watch:' + baseTask + ':webservices', function() {
	gulp.watch(
		extPath + '/extensions/webservices/**',
		{ interval: watchInterval },
		['copy:' + baseTask + ':webservices', browserSync.reload]
	);
});
