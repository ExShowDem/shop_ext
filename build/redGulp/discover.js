const browserSync = require("browser-sync");
const del         = require("del");
const minifyCSS   = require("gulp-minify-css");
const less        = require("gulp-less");
const rename      = require("gulp-rename");
const gulp        = require("gulp");
const exists      = require("fs-exists-sync");

const config    = require("../gulp-config.json");
const extension = require("../package.json");
const util      = require("../redGulp/utils.js");

var extPath         = "../extensions";
var gulpPath        = "../jgulp";
var gulpPathCurrent = "jgulp";
var watchInterval = 500;

if (typeof config.watchInterval !== "undefined") {
    watchInterval = config.watchInterval;
}

function discoverModules() {
    util.getFolders(extPath + "/modules").forEach(function (modBase) {
        util.getFolders(extPath + "/modules/" + modBase).forEach(function (modFolder) {
            var modName = modFolder.replace("mod_" + extension.name + "_", "");

            // Skip discover if already exist.
            if (exists(gulpPathCurrent + "/modules/" + modBase + "/" + modName + ".js")) {
				console.log("found custom jgulp module files:" + gulpPathCurrent + "/modules/" + modBase + "/" + modName + ".js");
                return;
            }

            var baseTask  = "modules." + (modBase === "site" ? "frontend" : "backend") + "." + modName;
            var modPath   = extPath + "/modules/" + modBase + "/" + modFolder;
            var mediaPath = "./media/modules/" + modBase + "/" + modFolder;

            // Clean
            gulp.task("clean:" + baseTask,
                [
                    "clean:" + baseTask + ":module",
                    "clean:" + baseTask + ":media",
                    "clean:" + baseTask + ":css"
                ],
                function () {
                });

            // Clean: Module
            gulp.task("clean:" + baseTask + ":module", ["clean:" + baseTask + ":module:lang"], function () {
                return del(config.wwwDir + "/modules/" + modFolder, {force: true});
            });

            // Clean: Media
            gulp.task("clean:" + baseTask + ":media", function () {
                return del([
                    config.wwwDir + "/media/" + modFolder,
                    "!" + config.wwwDir + "/media/" + modFolder + "/css",
                    "!" + config.wwwDir + "/media/" + modFolder + "/images"
                ], {force: true});
            });

            // Clean: CSS
            gulp.task("clean:" + baseTask + ":css", function () {
                return del(config.wwwDir + "/media/" + modFolder + "/css", {force: true});
            });

            // Copy: Module
            gulp.task("copy:" + baseTask,
                [
                    "copy:" + baseTask + ":module",
                    "copy:" + baseTask + ":media",
                    "copy:" + baseTask + ":css"
                ],
                function () {
                });

            // Copy: Module
            gulp.task("copy:" + baseTask + ":module", ["clean:" + baseTask + ":module", "copy:" + baseTask + ":module:lang"], function () {
                return gulp.src([
                    modPath + "/**"
                ]).pipe(gulp.dest(config.wwwDir + "/modules/" + modFolder));
            });

            // Copy: Media
            gulp.task("copy:" + baseTask + ":media", ["clean:" + baseTask + ":media"], function () {
                return gulp.src([
                    mediaPath + "/**",
                    "!" + mediaPath + "/css",
                    "!" + mediaPath + "/css/**",
                    "!" + mediaPath + "/less",
                    "!" + mediaPath + "/less/**"
                ]).pipe(gulp.dest(config.wwwDir + "/media/" + modFolder));
            });

            // Copy: CSS
            gulp.task("copy:" + baseTask + ":css", ["less:" + baseTask], function () {
                return gulp.src(mediaPath + "/css/**/*").pipe(gulp.dest(config.wwwDir + "/media/" + modFolder + "/css"));
            });

            // LESS compiler
            gulp.task("less:" + baseTask, ["clean:" + baseTask + ":css"], function () {
                return gulp.src(mediaPath + "/less/**/*.less").
                    pipe(less()).
                    pipe(gulp.dest(config.wwwDir + "/media/" + modFolder + "/css")).
                    pipe(minifyCSS()).
                    pipe(rename(function (path) { path.extname = ".min." + path.extname; })).
                    pipe(gulp.dest(config.wwwDir + "/media/" + modFolder + "/css"));
            });

            // Watch
            gulp.task("watch:" + baseTask,
                [
                    "watch:" + baseTask + ":module",
                    "watch:" + baseTask + ":media",
                    "watch:" + baseTask + ":css",
                    "watch:" + baseTask + ":less"
                ],
                function () {
                });

            // Watch: Module
            gulp.task("watch:" + baseTask + ":module", ["watch:" + baseTask + ":module:lang"], function () {
                gulp.watch([
                        modPath + "/**/*"
                    ],
                    {interval: watchInterval},
                    ["copy:" + baseTask + ":module", browserSync.reload]);
            });

            // Watch: Media
            gulp.task("watch:" + baseTask + ":media", function () {
                gulp.watch([
                        mediaPath + "/**",
                        "!" + mediaPath + "/css",
                        "!" + mediaPath + "/css/**",
                        "!" + mediaPath + "/less",
                        "!" + mediaPath + "/less/**"
                    ],
                    {interval: watchInterval},
                    ["copy:" + baseTask + ":media", browserSync.reload]);
            });

            // Watch: CSS (3rd library)
            gulp.task("watch:" + baseTask + ":css", function () {
                gulp.watch([
                        mediaPath + "/css/**/*.css"
                    ],
                    {interval: watchInterval},
                    ["copy:" + baseTask + ":css", browserSync.reload]);
            });

            // Watch: LESS
            gulp.task("watch:" + baseTask + ":less", function () {
                gulp.watch([
                        mediaPath + "/less/**/*.less"
                    ],
                    {interval: watchInterval},
                    ["less:" + baseTask, browserSync.reload]);
            });

            util.createGulpCopyLanguage("copy:" + baseTask + ":module:lang", modFolder, modBase);
            util.createGulpCleanLanguage("clean:" + baseTask + ":module:lang", modFolder, modBase);
            util.createGulpWatchLanguage("watch:" + baseTask + ":module:lang", modFolder, modBase);
        });
    });
}

function discoverPlugins() {
    util.getFolders(extPath + "/plugins").forEach(function (plgGroup) {
        util.getFolders(extPath + "/plugins/" + plgGroup).forEach(function (plgName) {
            // Skip discover if already exist.
            if (exists(gulpPathCurrent + "/plugins/" + plgGroup + "/" + plgName + ".js")) {
                console.log("found custom jgulp plugin files:" + gulpPathCurrent + "/plugins/" + plgGroup + "/" + plgName + ".js");
                return;
            }

            var baseTask   = "plugins." + plgGroup + "." + plgName;
            var plgPath    = extPath + "/plugins/" + plgGroup + "/" + plgName;
            var wwwExtPath = config.wwwDir + "/plugins/" + plgGroup + "/" + plgName;

            // Clean
            gulp.task("clean:" + baseTask,
                [
                    "clean:" + baseTask + ":files",
                    "clean:" + baseTask + ":lang"
                ],
                function () {
                });

            // Clean: plugin
            gulp.task("clean:" + baseTask + ":files", function () {
                return del(wwwExtPath, {force: true});
            });

            // Copy
            gulp.task("copy:" + baseTask, ["clean:" + baseTask, "copy:" + baseTask + ":lang"], function () {
                return gulp.src(plgPath + "/**").pipe(gulp.dest(wwwExtPath));
            });

            // Watch
            gulp.task("watch:" + baseTask,
                [
                    "watch:" + baseTask + ":files",
                    "watch:" + baseTask + ":lang"
                ],
                function () {
                });

            // Watch: plugin
            gulp.task("watch:" + baseTask + ":files", function () {
                gulp.watch(
                    plgPath + "/**/*",
                    {interval: watchInterval},
                    ["copy:" + baseTask, browserSync.reload]
                );
            });

            util.createGulpCopyLanguage("copy:" + baseTask + ":lang", "plg_" + plgGroup + "_" + plgName, "admin");
            util.createGulpCleanLanguage("clean:" + baseTask + ":lang", "plg_" + plgGroup + "_" + plgName, "admin");
            util.createGulpWatchLanguage("watch:" + baseTask + ":lang", "plg_" + plgGroup + "_" + plgName, "admin");
        });
    });
}

exports.discoverModules = discoverModules;
exports.discoverPlugins = discoverPlugins;