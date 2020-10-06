var gulp      = require('gulp');
var normalize = require("normalize-path");
var path      = require("path");
var fsextra   = require("fs-extra");

var config = require('../../gulp-config.json');

var watchInterval = 500;
if (typeof config.watchInterval !== 'undefined') {
	watchInterval = config.watchInterval;
}

// Dependencies
var browserSync = require('browser-sync');
var del         = require('del');
var composer    = require('gulp-composer');
var fs          = require('fs');

var baseTask  = 'libraries.redshopb';
var extPath   = '../extensions/libraries/redshopb';

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':library',
		'clean:' + baseTask + ':manifest'
	],
	function() {
});

// Clean: library
gulp.task('clean:' + baseTask + ':library', function() {
	return del(config.wwwDir + '/libraries/redshopb', {force : true});
});

// Clean: manifest
gulp.task('clean:' + baseTask + ':manifest', function() {
	return del(config.wwwDir + '/administrator/manifests/libraries/redshopb.xml', {force : true});
});

// Copy
gulp.task('copy:' + baseTask,
	[
		'copy:' + baseTask + ':library',
		'copy:' + baseTask + ':manifest'
	],
	function() {
});

// Copy: manifest
gulp.task('copy:' + baseTask + ':manifest', ['clean:' + baseTask + ':manifest'], function() {
	return gulp.src(extPath + '/redshopb.xml')
		.pipe(gulp.dest(config.wwwDir + '/administrator/manifests/libraries'));
});

// Composer
gulp.task('composer:' + baseTask, function(cb) {
	composer({"d": extPath}).on('end', function () {
		cb();

		var fontPath = extPath + '/vendor/mpdf/mpdf/ttfonts/';

		// Leave only default font
		return del([
			fontPath + '*.ttf',
			fontPath + '*.otf',
			'!' + fontPath + 'DejaVu*',
			extPath + '/vendor/imagine/imagine/lib/Imagine/resources/Adobe/CMYK/**'
		], {force : true});
	});
});

// Copy: redSHOP B2B Library
gulp.task('copy:' + baseTask + ':library', ['composer:' + baseTask], function() {
	return gulp.src([
		extPath + '/**',
		'!' + extPath + '/redshopb.xml',
		'!' + extPath + '/**/docs',
		'!' + extPath + '/**/docs/**',
		'!' + extPath + '/vendor/**/sample',
		'!' + extPath + '/vendor/**/sample/**',
		'!' + extPath + '/vendor/**/tests',
		'!' + extPath + '/vendor/**/tests/**',
		'!' + extPath + '/vendor/**/Tests',
		'!' + extPath + '/vendor/**/Tests/**',
		'!' + extPath + '/vendor/**/doc',
		'!' + extPath + '/vendor/**/doc/**',
		'!' + extPath + '/vendor/**/docs',
		'!' + extPath + '/vendor/**/docs/**',
		'!' + extPath + '/**/composer.*',
		'!' + extPath + '/vendor/**/*.sh',
		'!' + extPath + '/vendor/**/build.xml',
		'!' + extPath + '/**/phpunit*',
		'!' + extPath + '/**/Vagrant*',
		'!' + extPath + '/vendor/**/.*.yml',
		'!' + extPath + '/vendor/**/.editorconfig',
	])
	.pipe(gulp.dest(config.wwwDir + '/libraries/redshopb'));
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':library',
		'watch:' + baseTask + ':manifest'
	],
	function() {
});

// Watch: library
gulp.task('watch:' +  baseTask + ':library', function() {
    var absPath = process.cwd().replace("build", "");
	gulp.watch([extPath + '/**/*', '!' + extPath + '/redshopb.xml'], { interval: watchInterval })
        .on("change", function(file){
            var targetFile = normalize(file.path);
            var extBase    = normalize(absPath + "extensions/libraries/redshopb/");
            var wwwBase    = normalize(config.wwwDir + '/libraries/redshopb');
            targetFile     = targetFile.replace(extBase, wwwBase);

            fsextra.copy(normalize(file.path), targetFile, {overwrite: true}, function(err){
                if (err) {
                    console.error(err);
                }

                browserSync.reload();
            });
        });
});

// Watch: manifest
gulp.task('watch:' +  baseTask + ':manifest', function() {
	gulp.watch(extPath + '/redshopb.xml',
	{ interval: watchInterval },
	['copy:' + baseTask + ':manifest', browserSync.reload]);
});
