var gulp       = require("gulp");
var requireDir = require("require-dir");
var argv       = require("yargs").argv;
var xml2js     = require("xml2js");
var fs         = require("fs");
var path       = require("path");
var minifyCSS  = require("gulp-minify-css");
var less       = require("gulp-less");
var rename     = require("gulp-rename");
var zip        = require("gulp-zip");
var del        = require("del");
var merge      = require("merge-stream");

var config    = require("./gulp-config.json");
var extension = require("./package.json");

var joomlaGulp  = requireDir("./node_modules/joomla-gulp", {recurse: true});
var redcoregulp = requireDir("./redCORE/build/gulp-redcore", {recurse: true});
var jgulp       = requireDir("./jgulp", {recurse: true});
var crowdinConf = require("./crowdin-conf.js");
var discover = require("./redGulp/discover.js");

var parser = new xml2js.Parser();

var bump_version = require("./bump-version.js");

discover.discoverModules();
discover.discoverPlugins();

/**
 * Function for read list folder
 *
 * @param  string dir Path of folder
 *
 * @return array      Subfolder list.
 */
function getFolders(dir) {
    return fs.readdirSync(dir).filter(function (file) {
            return fs.statSync(path.join(dir, file)).isDirectory();
        }
    );
}

// Clean test site
gulp.task(
    "clean",
    [
        "clean:cli",
        "clean:components",
        "clean:libraries",
        "clean:media",
        "clean:modules",
        "clean:packages",
        "clean:plugins",
        "clean:templates",
        "clean:webservices"
    ], function () {
        return true;
    });

// Copy to test site
gulp.task("copy", [
    "copy:cli",
    "copy:components",
    "copy:libraries",
    "copy:media",
    "copy:modules",
    "copy:packages",
    "copy:plugins",
    "copy:templates",
    "copy:webservices"
], function () {
    return true;
});

// Watch for file changes
gulp.task("watch", [
    "watch:cli",
    "watch:components",
    "watch:libraries",
    "watch:media",
    "watch:modules",
    "watch:packages",
    "watch:plugins",
    "watch:templates",
    "watch:webservices"
], function () {
    return true;
});

gulp.task("release",
    [
        "release:redshopb",
        "release:plugin",
        "release:rsbmedia",
        "release:languages"
    ]
);

gulp.task("release:redshopb:media", ["release:redshopb:media:less"], function (cb) {
    return gulp.src([
        "media/components/com_redshopb/**/*",
        "!media/components/com_redshopb/**/.gitkeep",
        "!media/components/com_redshopb/less",
        "!media/components/com_redshopb/less/**"
    ]).pipe(gulp.dest("../extensions/media/com_redshopb"));
});

gulp.task("release:redshopb:redCORE", function (cb) {
    return gulp.src([
        "./redCORE/extensions/**"
    ]).pipe(gulp.dest("../extensions/redCORE/extensions"));
});

gulp.task("release:redshopb:media:less", function () {
    return gulp.src("media/components/com_redshopb/less/**/*.less").
        pipe(less()).
        pipe(gulp.dest("../extensions/media/com_redshopb/css")).
        pipe(minifyCSS()).
        pipe(rename(function (path) { path.extname = ".min" + path.extname; })).
        pipe(gulp.dest("../extensions/media/com_redshopb/css"));
});

gulp.task("release:redshopb:modules", ["release:redshopb:modules:less"], function () {
    var modules = getFolders("media/modules/site");

    for (var i = 0; i < modules.length; i++) {
        gulp.src([
            "media/modules/site/" + modules[i] + "/**",
            "media/modules/site/" + modules[i] + "/.gitkeep",
            "!media/modules/site/" + modules[i] + "/less",
            "!media/modules/site/" + modules[i] + "/less/**"
        ]).pipe(gulp.dest("../extensions/modules/site/" + modules[i] + "/media/" + modules[i]));
    }
});

gulp.task("release:redshopb:modules:less", function () {
    var modules = getFolders("media/modules/site");

    for (var i = 0; i < modules.length; i++) {
        gulp.src("media/modules/site/" + modules[i] + "/less/**/*.less").
            pipe(less()).
            pipe(gulp.dest("../extensions/modules/site/" + modules[i] + "/media/" + modules[i] + "/css")).
            pipe(minifyCSS()).
            pipe(rename(function (path) { path.extname = ".min" + path.extname; })).
            pipe(gulp.dest("../extensions/modules/site/" + modules[i] + "/media/" + modules[i] + "/css"));
    }
});

gulp.task("release:redshopb:package",
    [
        "composer:libraries.redshopb",
        "composer:plugins.vanir_search.solr",
        "composer:plugins.system.sh404sef_observer",
        "release:redshopb:media",
        "release:redshopb:modules",
        "release:redshopb:redCORE"
    ],
    function (cb) {
        fs.readFile("../extensions/redshopb.xml", function (err, data) {
            parser.parseString(data, function (err, result) {
                var version  = result.extension.version[0];

                if (result.extension.releaseName && result.extension.releaseName[0])
                {
                    version = version + '-' + result.extension.releaseName[0].toLowerCase();
                }

                var fileName = argv.skipVersion ? extension.name + ".zip" : extension.name + "-v" + version + ".zip";

                // We will output where release package is going so it is easier to find
                console.log("Creating new Aesir E-Commerce release file in: " + path.join(config.release_dir, fileName));

                return gulp.src([
                    "../extensions/**/*",
                    "!../extensions/**/.gitkeep",
                    "!../extensions/components/com_redshopb/**/.gitkeep",
                    "!../extensions/components/com_rsbmedia",
                    "!../extensions/components/com_rsbmedia/**",
                    "!../extensions/libraries/**/.gitkeep",
                    "!../extensions/libraries/redshopb/vendor/**/tests/**/*",
                    "!../extensions/libraries/redshopb/vendor/**/tests",
                    "!../extensions/libraries/redshopb/vendor/**/Tests/**/*",
                    "!../extensions/libraries/redshopb/vendor/**/Tests",
                    "!../extensions/libraries/redshopb/vendor/**/docs/**/*",
                    "!../extensions/libraries/redshopb/vendor/**/docs",
                    "!../extensions/libraries/redshopb/vendor/**/doc/**/*",
                    "!../extensions/libraries/redshopb/vendor/**/doc",
                    "!../extensions/libraries/redshopb/vendor/**/composer.*",
                    "!../extensions/libraries/redshopb/vendor/**/phpunit*",
                    "!../extensions/libraries/redshopb/vendor/**/Vagrantfile",
                    "!../extensions/modules/**/.gitkeep",
                    "!../extensions/plugins/**/.gitkeep",
                    "!../extensions/plugins/rb_sync",
                    "!../extensions/plugins/rb_sync/**",
                    "!../extensions/**/composer.lock",
                    "!../extensions/**/composer.json"
                ]).pipe(zip(fileName)).pipe(gulp.dest(config.release_dir)).on("end", cb);
            });
        });
    });

// Override of the release script
gulp.task("release:redshopb", ["release:redshopb:package"], function (cb) {

    // Clean up temporary files
    return del([
            "../extensions/media",
            "../extensions/modules/site/*/media",
            "../extensions/redCORE"
        ],
        {force: true}
    );
});

gulp.task("release:rsbmedia:media", ["release:rsbmedia:media:less"], function () {
    return gulp.src([
        "media/components/com_rsbmedia/**/*",
        "!media/components/com_rsbmedia/**/.gitkeep",
        "!media/components/com_rsbmedia/less",
        "!media/components/com_rsbmedia/less/**"
    ]).pipe(gulp.dest("../extensions/components/com_rsbmedia/media/com_rsbmedia"));
});

// LESS compiler
gulp.task("release:rsbmedia:media:less", function () {
    return gulp.src("media/components/com_rsbmedia/less/**/*.less").
        pipe(less()).
        pipe(gulp.dest("../extensions/components/com_rsbmedia/media/com_rsbmedia/css")).
        pipe(minifyCSS()).
        pipe(rename(function (path) { path.extname = ".min" + path.extname; })).
        pipe(gulp.dest("../extensions/components/com_rsbmedia/media/com_rsbmedia/css"));
});

gulp.task("release:rsbmedia:package", ["release:rsbmedia:media"], function (cb) {
    fs.readFile("../extensions/components/com_rsbmedia/rsbmedia.xml", function (err, data) {
        parser.parseString(data, function (err, result) {
            var version = result.extension.version[0];

            if (result.extension.releaseName && result.extension.releaseName[0])
            {
                version = version + '-' + result.extension.releaseName[0].toLowerCase();
            }

            var fileName = argv.skipVersion ? "rsbmedia.zip" : "rsbmedia-v" + version + ".zip";

            // We will output where release package is going so it is easier to find
            console.log("Creating new rsbmedia release file in: " + path.join(config.release_dir, fileName));

            return gulp.src([
                "../extensions/components/com_rsbmedia/**/*"
            ]).pipe(zip(fileName)).pipe(gulp.dest(config.release_dir)).on("end", cb);
        });
    });
});

// Override of the release script
gulp.task("release:rsbmedia", ["release:rsbmedia:package"], function () {
    // Clean up temporary files
    return del(["../extensions/components/com_rsbmedia/media"], {force: true});
});

function pluginRelease(group, name) {
    var fileName = "plg_" + group + "_" + name;

    if (!argv.skipVersion) {
        fs.readFile("../extensions/plugins/" + group + "/" + name + "/" + name + ".xml", function (err, data) {
            parser.parseString(data, function (err, result) {
                var version = result.extension.version[0];

                if (result.extension.releaseName && result.extension.releaseName[0])
                {
                    version = version + '-' + result.extension.releaseName[0].toLowerCase();
                }

                fileName += "-v" + version + ".zip";

                // We will output where release package is going so it is easier to find
                console.log("Creating new plugin release file in: " + path.join(config.release_dir + "/plugins", fileName));

                return gulp.src("../extensions/plugins/" + group + "/" + name + "/**").pipe(zip(fileName)).pipe(gulp.dest(config.release_dir + "/plugins"));
            });
        });
    }
    else {
        return gulp.src("../extensions/plugins/" + group + "/" + name + "/**").pipe(zip(fileName + ".zip")).pipe(gulp.dest(config.release_dir + "/plugins"));
    }
}

// Task for release plugins
gulp.task("release:plugin", ["composer:plugins.vanir_search.solr", "composer:plugins.system.sh404sef_observer"], function (cb) {
    var basePath = "../extensions/plugins";
    var plgGroup = argv.group ? argv.group : false;
    var plgName  = argv.name ? argv.name : false;

    // No group specific, release all of them.
    if (!plgGroup) {
        var groups = getFolders(basePath);

        for (var i = 0; i < groups.length; i++) {
            var plugins = getFolders(basePath + "/" + groups[i]);

            for (j = 0; j < plugins.length; j++) {
                pluginRelease(groups[i], plugins[j]);
            }
        }
        ;
    }
    else if (plgGroup && !plgName) {
        try {
            fs.statSync("../extensions/plugins/" + plgGroup);
        }
        catch (e) {
            console.error("Folder not exist: " + basePath + "/" + plgGroup);
            return;
        }

        var plugins = getFolders(basePath + "/" + plgGroup);

        for (i = 0; i < plugins.length; i++) {
            pluginRelease(plgGroup, plugins[i]);
        }
    }
    else {
        try {
            fs.statSync("../extensions/plugins/" + plgGroup + "/" + plgName);
        }
        catch (e) {
            console.error("Folder not exist: " + basePath + "/" + plgGroup + "/" + plgName);
            return;
        }

        pluginRelease(plgGroup, plgName);
    }
});

gulp.task("release:languages", function () {
    const langPath   = "../resources/lang";
    const releaseDir = path.join(config.release_dir, "language");

    const folders = fs.readdirSync(langPath).map(function (file) {
        return path.join(langPath, file);
    }).filter(function (file) {
        return fs.existsSync(path.join(file, "install.xml"));
    });

    // We need to combine streams so we can know when this task is actually done
    return merge(folders.map(function (directory) {
            const data = fs.readFileSync(path.join(directory, "install.xml"));

            // xml2js parseString is sync, but must be called using callbacks... hence this awkwards vars
            // see https://github.com/Leonidas-from-XIV/node-xml2js/issues/159
            var task;
            var error;

            parser.parseString(data, function (err, result) {
                if (err) {
                    error = err;
                    console.log(err);

                    return;
                }

                var lang     = path.basename(directory);
                var version  = result.extension.version[0];

                if (result.extension.releaseName && result.extension.releaseName[0])
                {
                    version = version + '-' + result.extension.releaseName[0].toLowerCase();
                }

                const fileName = config.skipVersion ? extension.name + "_" + lang + ".zip" : extension.name + "_" + lang + "-v" + version + ".zip";

                task = gulp.src([directory + "/**"]).pipe(zip(fileName)).pipe(gulp.dest(releaseDir));
            });

            if (error) {
                throw error;
            }

            if (!error && !task) {
                throw new Error("xml2js callback became suddenly async or something.");
            }

            return task;
        })
    );
});
