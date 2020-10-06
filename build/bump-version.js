var gulp        = require('gulp');
var replace     = require('gulp-replace');

gulp.task('bump-version', function(){
	var argv = require('yargs').argv;
	var version = (argv.version === undefined) ? false : argv.version;

	if (!version)
	{
	    console.log('Missing version tag, use --version to specify the version');

        return;
	}

	var base = "../extensions";

	var paths = [
		base + "/**/*.php",
		"!" + base + "/libraries{,/mpdf/**}"
	];

	return gulp.src(paths, {base: "./"})
		.pipe(replace('__DEPLOY_VERSION__', version))
		.pipe(gulp.dest("./"));
});
