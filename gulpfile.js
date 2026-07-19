'use strict';

const gulp = require('gulp'),
	browserSync = require('browser-sync').create(),
	sass = require('gulp-sass')(require('sass')),
	postcss = require('gulp-postcss'),
	uglify = require('gulp-uglify'),
	rename = require('gulp-rename');

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
		.pipe(browserSync.stream());
}

// Scripts
function mainScripts() {
	return gulp.src(['assets/js/*.js', '!assets/js/*.min.js'])
		.pipe(uglify())
		.pipe(rename(function (path) {
			path.basename += ".min";
			path.extname = ".js";
		}))
		.pipe(gulp.dest('assets/js/'))
		.pipe(browserSync.stream());
}

// Server
function serve() {
	browserSync.init({
		proxy: 'path-to-local-site.loc'
	});
}

// Watcher
function watch() {
	gulp.watch('./**/*.php').on('change', browserSync.reload);
	gulp.watch('assets/images/*.*').on('change', browserSync.reload);
	gulp.watch('assets/scss/**/*.scss', mainStyles);
	gulp.watch('assets/js/main.js', mainScripts);
}

exports.build = gulp.parallel(
	mainStyles,
	mainScripts,
);
exports.default = gulp.series(
	this.build,
	gulp.parallel(
		serve,
		watch
	)
);
