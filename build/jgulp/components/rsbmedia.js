var gulp = require("gulp");

var config = require("../../gulp-config.json");
var utils  = require("../../redGulp/utils.js");

var watchInterval = 500;
if (typeof config.watchInterval !== "undefined") {
    watchInterval = config.watchInterval;
}

// Dependencies
var browserSync = require("browser-sync");
var del         = require("del");

var baseTask = "components.rsbmedia";
var extPath  = "../";

// Clean
gulp.task("clean:" + baseTask,
    [
        "clean:" + baseTask + ":frontend",
        "clean:" + baseTask + ":backend"
    ],
    function () {
        return true;
    });

// Clean: frontend
gulp.task("clean:" + baseTask + ":frontend", ["clean:" + baseTask + ":frontend:lang"], function (cb) {
    return del(config.wwwDir + "/components/com_rsbmedia", {force: true});
});

// Clean: backend
gulp.task("clean:" + baseTask + ":backend", ["clean:" + baseTask + ":backend:lang"], function (cb) {
    return del(config.wwwDir + "/administrator/components/com_rsbmedia", {force: true});
});

// Copy
gulp.task("copy:" + baseTask,
    [
        "clean:" + baseTask,
        "copy:" + baseTask + ":frontend",
        "copy:" + baseTask + ":backend"
    ],
    function () {
    });

// Copy: frontend
gulp.task("copy:" + baseTask + ":frontend", ["clean:" + baseTask + ":frontend", "copy:" + baseTask + ":frontend:lang"], function () {
    return gulp.src(extPath + "extensions/components/com_rsbmedia/site/**").pipe(gulp.dest(config.wwwDir + "/components/com_rsbmedia"));
});

// Copy: backend
gulp.task("copy:" + baseTask + ":backend", ["clean:" + baseTask + ":backend", "copy:" + baseTask + ":backend:lang"], function (cb) {
    return (
        gulp.src([
            extPath + "extensions/components/com_rsbmedia/admin/**"
        ]).pipe(gulp.dest(config.wwwDir + "/administrator/components/com_rsbmedia")) &&
        gulp.src(extPath + "extensions/components/com_rsbmedia/rsbmedia.xml").pipe(gulp.dest(config.wwwDir + "/administrator/components/com_rsbmedia")) &&
        gulp.src(extPath + "extensions/components/com_rsbmedia/install.php").pipe(gulp.dest(config.wwwDir + "/administrator/components/com_rsbmedia"))
    );
});

// Watch
gulp.task("watch:" + baseTask,
    [
        "watch:" + baseTask + ":frontend",
        "watch:" + baseTask + ":backend"
    ],
    function () {
        return true;
    });

// Watch: frontend
gulp.task("watch:" + baseTask + ":frontend", ["watch:" + baseTask + ":frontend:lang"], function () {
    gulp.watch(extPath + "extensions/components/com_rsbmedia/site/**",
        ["copy:" + baseTask + ":frontend", browserSync.reload]);
});

// Watch: backend
gulp.task("watch:" + baseTask + ":backend", ["watch:" + baseTask + ":backend:lang"], function () {
    gulp.watch([
            extPath + "extensions/components/com_rsbmedia/admin/**/*",
            extPath + "extensions/components/com_rsbmedia/rsbmedia.xml",
            extPath + "extensions/components/com_rsbmedia/install.php"
        ],
        {interval: watchInterval},
        ["copy:" + baseTask + ":backend", browserSync.reload]);
});

utils.createGulpCopyLanguage("copy:" + baseTask + ":frontend:lang", "com_redshopb", "site");
utils.createGulpCopyLanguage("copy:" + baseTask + ":backend:lang", "com_redshopb", "admin");
utils.createGulpCleanLanguage("clean:" + baseTask + ":frontend:lang", "com_redshopb", "site");
utils.createGulpCleanLanguage("clean:" + baseTask + ":backend:lang", "com_redshopb", "admin");
utils.createGulpWatchLanguage("watch:" + baseTask + ":frontend:lang", "com_redshopb", "site");
utils.createGulpWatchLanguage("watch:" + baseTask + ":backend:lang", "com_redshopb", "admin");
