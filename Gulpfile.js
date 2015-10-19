/* jshint node: true */
'use strict';

/**
 * Config section
 * ==============
 */
var build = {
    /**
     * Directory location of assets build manifests.
     *
     * @type {String}
     */
    manifestsPath: 'config/build/assets',

    /**
     * File name in which the build version is stored.
     *
     * @type {String}
     */
    versionFile: '.BUILD',

    /**
     * Current build version. Will be overwritten if build version file exists.
     *
     * @type {String}
     */
    version: 'dev'
};

/**
 * Dependencies
 * ============
 */
var fs = require('fs'),
    _ = require('lodash'),
    Q = require('q'),
    jsonMinify = require('jsonminify'),
    gulp = require('gulp'),
    gutil = require('gulp-util'),
    clean = require('gulp-clean'),
    concat = require('gulp-concat'),
    sourcemaps = require('gulp-sourcemaps'),
    less = require('gulp-less'),
    autoprefixer = require('gulp-autoprefixer'),
    minifyCss = require('gulp-minify-css'),
    ngAnnotate = require('gulp-ng-annotate'),
    uglify = require('gulp-uglify'),
    jshint = require('gulp-jshint');

try {
    build.version = fs.readFileSync(build.versionFile, 'utf8').trim();
} catch(e) {}

/**
 * Build methods
 * =============
 */

/**
 * Read an asset build manifest file.
 *
 * @param  {String} name Name of the manifest file.
 *
 * @return {Object}
 */
var readManifest = function(name) {
    var manifest = JSON.parse(jsonMinify(fs.readFileSync(build.manifestsPath + '/' + name + '.json', 'utf8'))),
        packages = {};

    // normalize the packages from the manifest, so they all are in form {files: [], watch: []}
    _.each(manifest, function(item, name) {
        item = !_.isPlainObject(item) ? {files: item} : item;
        item.files = !_.isArray(item.files) ? [item.files] : item.files;
        item.watch = _.isUndefined(item.watch) ? item.files : item.watch;
        item.watch = !_.isArray(item.watch) ? [item.watch] : item.watch;
        packages[name] = item;
    });

    return packages;
};

/**
 * Build a single LESS package.
 *
 * @param  {String} name  Package name.
 * @param  {Array}  files Files that should be included in the package.
 *
 * @return {Promise}
 */
var buildLessPackage = function(name, files) {
    gutil.log('Building LESS package "' + gutil.colors.yellow(name) + '"...');

    var cleanDefer = Q.defer(),
        buildDefer = Q.defer();

    // clear any previous results
    gulp.src('web/assets/css/' + name + '-*.css{.map,}', {read: false})
        .on('end', cleanDefer.resolve)
        .pipe(clean());

    // when cleared, build new
    cleanDefer.promise.then(function() {
        gulp.src(files)
            .pipe(sourcemaps.init())
                .pipe(less()).on('error', buildDefer.reject)
                .pipe(autoprefixer({
                    browsers: ['last 3 versions'],
                    cascade: false
                }))
                .pipe(minifyCss({
                    processImport: false
                }))
                .pipe(concat(name + '-' + build.version + '.css'))
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest('web/assets/css'))
            .on('end', buildDefer.resolve);
    });

    return buildDefer.promise.then(function() {
        gutil.log('Built LESS package "' + gutil.colors.green(name) + '".');
    }, function(error) {
        gutil.log(gutil.colors.bold.red('LESS Error:') + ' ' + error.message);
        gutil.beep();
    });
};

/**
 * Build a single JS package.
 *
 * @param  {String} name  Package name.
 * @param  {Array}  files Files that should be included in the package.
 *
 * @return {Promise}
 */
var buildJsPackage = function(name, files) {
    gutil.log('Building JS package "' + gutil.colors.yellow(name) + '"...');

    var cleanDefer = Q.defer(),
        buildDefer = Q.defer();

    // clear any previous results
    gulp.src('web/assets/js/' + name + '-*.js{.map,}', {read: false})
        .on('end', cleanDefer.resolve)
        .pipe(clean());

    // when cleared, build new
    cleanDefer.promise.then(function() {
        gulp.src(files).on('end', buildDefer.resolve)
            .pipe(sourcemaps.init())
                .pipe(ngAnnotate())
                .pipe(uglify())
                .pipe(concat(name + '-' + build.version + '.js'))
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest('web/assets/js'))
            .on('end', buildDefer.resolve);
    });

    return buildDefer.promise.then(function() {
        gutil.log('Built JS package "' + gutil.colors.green(name) + '".');
    }, function(error) {
        gutil.log(gutil.colors.bold.red('JS Error:') + ' ' + error.message);
        gutil.beep();
    });
};

/**
 * Gulp tasks
 * ==========
 */

/**
 * Build all LESS assets.
 */
gulp.task('less', function() {
    var promises = [];

    _.each(readManifest('less'), function(pckg, name) {
        promises.push(buildLessPackage(name, pckg.files));
    });

    return Q.all(promises);
});

/**
 * Build all JS assets.
 */
gulp.task('js', function() {
    var promises = [];

    _.each(readManifest('js'), function(pckg, name) {
        promises.push(buildJsPackage(name, pckg.files));
    });

    return Q.all(promises);
});

/**
 * Lint all JS assets.
 */
gulp.task('js:lint', function() {
    var config = JSON.parse(jsonMinify(fs.readFileSync('.jshintrc', 'utf8')));
    config.lookup = false;
    return gulp.src('web/js/**/*.js')
        .pipe(jshint(config))
        .pipe(jshint.reporter('jshint-stylish'));
});

/**
 * Watch all relevant files.
 *
 * Also trigger default build on start (so we know all build artifacts are up to date).
 */
gulp.task('watch', ['default'], function() {
    var watched = [];

    var watchManifestContents = function(name, builder) {
        _.each(readManifest(name), function(pckg, pckgName) {
            _.each(pckg.watch, function(file) {
                // if already watching then dont watch again
                if (_.indexOf(watched, file) !== -1) {
                    return;
                }

                gulp.watch(file, function() {
                    return builder(pckgName, pckg.files);
                });

                watched.push(file);
            });
        });
    };

    // watch manifests
    gulp.watch(build.manifestsPath + '/less.json', ['less'])
        .on('change', function() {
            watchManifestContents('less', buildLessPackage);
        });
    gulp.watch(build.manifestsPath + '/js.json', ['js'])
        .on('change', function() {
            watchManifestContents('js', buildJsPackage);
        });

    // watch all files in packages
    watchManifestContents('less', buildLessPackage);
    watchManifestContents('js', buildJsPackage);
});

/**
 * Default task - build all assets.
 */
gulp.task('default', ['less', 'js']);
