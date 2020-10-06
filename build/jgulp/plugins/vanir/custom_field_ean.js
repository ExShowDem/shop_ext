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
var fs          = require('fs');

var group = 'vanir';
var name  = 'custom_field_ean';

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
gulp.task('copy:' + baseTask, ['clean:' + baseTask], function() {
    return gulp.src( extPath + '/**')
        .pipe(gulp.dest(wwwExtPath));
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
