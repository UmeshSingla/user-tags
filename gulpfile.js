// Defining requirements
let gulp = require('gulp');
let plumber = require('gulp-plumber');
let sass = require('gulp-sass');
let babel = require('gulp-babel');
let postcss = require('gulp-postcss');
let touch = require('gulp-touch-fd');
let rename = require('gulp-rename');
let uglify = require('gulp-uglify');
let sourcemaps = require('gulp-sourcemaps');
let browserSync = require('browser-sync').create();
let del = require('del');
let cleanCSS = require('gulp-clean-css');
let autoprefixer = require('autoprefixer');

// Configuration file to keep your code DRY
let cfg = require('./gulpconfig.json');
let paths = cfg.paths;

// Run:
// gulp sass
// Compiles SCSS files in CSS
gulp.task('sass', function () {
    return gulp
        .src(paths.sass + '/*.scss')
        .pipe(
            plumber({
                errorHandler: function (err) {
                    console.log(err);
                    this.emit('end');
                }
            })
        )
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sass({errLogToConsole: true}))
        .pipe(postcss([autoprefixer()]))
        .pipe(sourcemaps.write(undefined, {sourceRoot: null}))
        .pipe(gulp.dest(paths.css))
        .pipe(touch());
});

// Run:
// gulp watch
// Starts watcher. Watcher runs gulp sass task on changes
gulp.task('watch', function () {
    gulp.watch(
        [
            `${paths.sass}/**/*.scss`,
            `${paths.sass}/*.scss`
        ],
        gulp.series('styles')
    );
    gulp.watch(
        [
            `${paths.dev}/js/**/*.js`,
            '!js/main.js',
            '!js/main.min.js'
        ],
        gulp.series('scripts')
    );
});

gulp.task('minifycss', function () {
    return gulp
        .src([
            `${paths.css}/main.css`,
            `${paths.css}/block.css`,
        ])
        .pipe(sourcemaps.init({
            loadMaps: true
        }))
        .pipe(cleanCSS({
            compatibility: '*'
        }))
        .pipe(
            plumber({
                errorHandler: function (err) {
                    console.log(err);
                    this.emit('end');
                }
            })
        )
        .pipe(rename({suffix: '.min'}))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(paths.css))
        .pipe(touch());
});

/**
 * Delete minified CSS files and their maps
 */
gulp.task('cleancss', function () {
    return del(paths.css + '/*.min.css*');
});

gulp.task('styles', function (callback) {
    gulp.series('sass', 'minifycss')(callback);
});

// Run:
// gulp browser-sync
// Starts browser-sync task for starting the server.
gulp.task('browser-sync', function () {
    browserSync.init(cfg.browserSyncWatchFiles, cfg.browserSyncOptions);
});

// Run:
// gulp scripts.
// Uglifies and concat all JS files into one
gulp.task('scripts', function () {

    let scripts = [
        `${paths.dev}/js/*.js`,
    ];
    return gulp
        .src(scripts, {allowEmpty: true})
        .pipe(babel({presets: ['@babel/preset-env']}))
        .pipe(gulp.dest(paths.js))
        .pipe(uglify())
        .pipe(rename(function(path) {
            path.basename += '.min';
        }))
        .pipe(gulp.dest(paths.js));
});

// Run:
// gulp watch-bs
// Starts watcher with browser-sync. Browser-sync reloads page automatically on your browser
gulp.task('watch-bs', gulp.parallel('browser-sync', 'watch'));

// Deleting any file inside the /dist folder
gulp.task('clean-dist', function () {
    return del([paths.dist + '/**']);
});

// Run
// gulp dist
// Copies the files to the /dist folder for distribution as simple plugin
gulp.task(
    'dist',
    gulp.series(['clean-dist'], function () {
        return gulp
            .src(
                [
                    '**/*',
                    `!${paths.node}`,
                    `!${paths.node}/**`,
                    `!${paths.dev}`,
                    `!${paths.dev}/**`,
                    `!${paths.dist}`,
                    `!${paths.dist}/**`,
                    `!${paths.distprod}`,
                    `!${paths.distprod}/**`,
                    `!${paths.sass}`,
                    `!${paths.sass}/**`,
                    `!${paths.composer}`,
                    `!${paths.composer}/**`,
                    '!readme.txt',
                    '!README.md',
                    '!*.+(json|js|lock|xml)',
                    '!CHANGELOG.md',
                ],
                {buffer: true}
            )
            .pipe(gulp.dest(paths.dist))
            .pipe(touch());
    })
);

// Deleting any file inside the /dist-product folder
gulp.task('clean-dist-product', function () {
    return del([paths.distprod + '/**']);
});

// Run
// gulp dist-product
// Copies the files to the /dist-prod folder for distribution as plugin with all assets
gulp.task(
    'dist-product',
    gulp.series(['clean-dist-product'], function () {
        return gulp
            .src([
                '**/*',
                `!${paths.node}`,
                `!${paths.node}/**`,
                `!${paths.composer}`,
                `!${paths.composer}/**`,
                `!${paths.dist}`,
                `!${paths.dist}/**`,
                `!${paths.distprod}`,
                `!${paths.distprod}/**`,
            ])
            .pipe(gulp.dest(paths.distprod))
            .pipe(touch());
    })
);

// Run
// gulp compile
// Compiles the styles and scripts and runs the dist task
gulp.task('compile', gulp.series('styles', 'scripts', 'dist'));

// Run:
// gulp
// Starts watcher (default task)
gulp.task('default', gulp.series('watch'));
