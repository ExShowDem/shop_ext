var gulp      = require("gulp");
var config    = require("../../../gulp-config.json");
var extension = require("../../../package.json");

var watchInterval = 500;
if (typeof config.watchInterval !== "undefined") {
    watchInterval = config.watchInterval;
}

// Dependencies
var browserSync = require("browser-sync");
var del         = require("del");
var fs          = require("fs");
var path        = require("path");
var minifyCSS   = require("gulp-minify-css");
var less        = require("gulp-less");
var rename      = require("gulp-rename");

var modName   = "category_list";
var modFolder = "mod_" + extension.name + "_" + modName;
var modBase   = "site";

var baseTask  = "modules.frontend." + modName;
var extPath   = "../extensions/modules/" + modBase + "/" + modFolder;
var mediaPath = "./media/modules/" + modBase + "/" + modFolder;

function getFolders(dir) {
    return fs.readdirSync(dir).filter(function (file) {
            return fs.statSync(path.join(dir, file)).isDirectory();
        }
    );
}

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
gulp.task("clean:" + baseTask + ":module", function () {
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
gulp.task("copy:" + baseTask + ":module", ["clean:" + baseTask + ":module"], function () {
    return gulp.src([
        extPath + "/**"
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
gulp.task("watch:" + baseTask + ":module", function () {
    gulp.watch([
            extPath + "/**/*"
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