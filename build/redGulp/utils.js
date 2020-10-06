const gulp   = require("gulp");
const del    = require("del");
const path   = require("path");
const fs     = require("fs");
const config = require("../gulp-config.json");

var langFolder = "../resources/lang";

function createGulpCopyLanguage(baseTask, extName, extBase) {
    // Languages stuff
    var tasks   = [];
    var wwwLang = extBase === "site" ? config.wwwDir + "/language/" : config.wwwDir + "/administrator/language/";

    getFolders(langFolder).forEach(function (lang) {
        if (lang !== "en-GB") {
            gulp.task(baseTask + ":" + lang, function () {
                return gulp.src(langFolder + "/" + lang + "/" + extBase + "/" + lang + "/" + lang + "." + extName + "*.ini").pipe(gulp.dest(wwwLang + lang));
            });
            tasks.push(baseTask + ":" + lang);
        }
    });

    gulp.task(baseTask, tasks);
}

function createGulpCleanLanguage(baseTask, extName, extBase) {
    // Languages stuff
    var tasks   = [];
    var wwwLang = extBase === "site" ? config.wwwDir + "/language/" : config.wwwDir + "/administrator/language/";

    getFolders(langFolder).forEach(function (lang) {
        if (lang !== "en-GB") {
            gulp.task(baseTask + ":" + lang, function () {
                return del(wwwLang + lang + "/" + lang + "." + extName + "*.ini", {force:true});
            });

            tasks.push(baseTask + ":" + lang);
        }
    });

    gulp.task(baseTask, tasks);
}

function createGulpWatchLanguage(baseTask, extName, extBase) {
    // Languages stuff
    var tasks   = [];
    var wwwLang = extBase === "site" ? config.wwwDir + "/language/" : config.wwwDir + "/administrator/language/";

    getFolders(langFolder).forEach(function (lang) {
        if (lang !== "en-GB") {
            gulp.task(baseTask + ":" + lang, function () {
                gulp.watch(langFolder + "/" + lang + "/" + extBase + "/" + lang + "/" + lang + "." + extName + "*.ini").on("change", function (file) {
                    return gulp.src(langFolder + "/" + lang + "/" + extBase + "/" + lang + "/" + path.basename(file.path)).pipe(gulp.dest(wwwLang + lang));
                });
            });

            tasks.push(baseTask + ":" + lang);
        }
    });

    gulp.task(baseTask, tasks);
}

function getFolders(dir) {
    return fs.readdirSync(dir).filter(function (file) {
            return fs.statSync(path.join(dir, file)).isDirectory();
        }
    );
}

exports.createGulpCopyLanguage  = createGulpCopyLanguage;
exports.createGulpCleanLanguage = createGulpCleanLanguage;
exports.createGulpWatchLanguage = createGulpWatchLanguage;
exports.getFolders              = getFolders;
