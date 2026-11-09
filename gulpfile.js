'use strict';

const gulp = require('gulp'),
	sass = require('gulp-sass')(require('sass')),
	postcss = require('gulp-postcss'),
	uglify = require('gulp-uglify'),
	rename = require('gulp-rename');

let browserSync;
function sync() {
	if (!browserSync) browserSync = require('browser-sync').create();
	return browserSync;
}

// Styles
function mainStyles() {
	return gulp.src('assets/scss/style.scss')
		.pipe(sass.sync({
			outputStyle: 'expanded'
		}).on('error', sass.logError))
		.pipe(postcss([
			require('autoprefixer')(),
			require('postcss-sort-media-queries')(),
			require('postcss-csso')({
				comments: 'exclamation'
			})
		]))
		.pipe(gulp.dest('./'))
		.pipe(browserSync ? browserSync.stream() : new (require('stream').PassThrough)({ objectMode: true }));
}

// Scripts
function mainScripts() {
	return gulp.src('assets/js/main.js')
		.pipe(uglify())
		.pipe(rename(function (path) {
			path.basename += ".min";
			path.extname = ".js";
		}))
		.pipe(gulp.dest('assets/js/'))
		.pipe(browserSync ? browserSync.stream() : new (require('stream').PassThrough)({ objectMode: true }));
}

// Server
function requireProxy(done) {
	if (!process.env.WP_PROXY || !process.env.WP_PROXY.trim()) {
		return done(new Error('Set WP_PROXY to the URL of your local WordPress site, for example WP_PROXY=http://my-site.test npm run dev'));
	}
	done();
}

function serve(done) {
	sync().init({
		proxy: process.env.WP_PROXY.trim()
	});
	done();
}

// Watcher
function watch(done) {
	gulp.watch('./**/*.php').on('change', sync().reload);
	gulp.watch('assets/images/*.*').on('change', sync().reload);
	gulp.watch('assets/scss/**/*.scss', mainStyles);
	gulp.watch('assets/js/main.js', mainScripts);
	done();
}

exports.build = gulp.parallel(
	mainStyles,
	mainScripts,
);
exports.default = gulp.series(
	requireProxy,
	this.build,
	gulp.parallel(
		serve,
		watch
	)
);
