var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefix = require('gulp-autoprefixer');
var livereload = require('gulp-livereload');
var notify = require('gulp-notify');
var minifycss = require('gulp-minify-css');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var pump = require('pump');

// Style Paths
var scss_admin = [
	'assets/scss/admin/*'
]
var scss_public = [
	'assets/scss/public/*'
]
var css = 'assets/css/';

// JS Paths
var js_source_admin = [
	'assets/js/admin/factory.js'
];
var js_source_public = [
	'assets/js/public/checkout.js',
	'assets/js/public/factory.js'
];
var js_compiled = 'assets/js/';

/**
* Process the admin styles
*/
var styles_admin = function(){
	return gulp.src(scss_admin)
		.pipe(sass({sourceComments: 'map', sourceMap: 'sass', style: 'compact'}))
		.pipe(autoprefix('last 5 version'))
		.pipe(minifycss({keepBreaks: false}))
		.pipe(gulp.dest(css))
		.pipe(livereload());
}

/**
* Process the public styles
*/
var styles_public = function(){
	return gulp.src(scss_public)
		.pipe(sass({sourceComments: 'map', sourceMap: 'sass', style: 'compact'}))
		.pipe(autoprefix('last 5 version'))
		.pipe(minifycss({keepBreaks: false}))
		.pipe(gulp.dest(css))
		.pipe(livereload());
}

/**
* Process the admin scripts
*/
var scripts_admin = function(){
	return gulp.src(js_source_admin)
		.pipe(concat('admin.scripts.min.js'))
		.pipe(uglify())
		.pipe(gulp.dest(js_compiled));
};

/**
* Process the public scripts
*/
var scripts_public = function(){
	return gulp.src(js_source_public)
		.pipe(concat('scripts.min.js'))
		.pipe(uglify())
		.pipe(gulp.dest(js_compiled));
};

/**
* Watch Task
*/
gulp.task('watch', function(){
	livereload.listen();
	gulp.watch(scss_admin, gulp.series(styles_admin));
	gulp.watch(scss_public, gulp.series(styles_public));
	gulp.watch(js_source_admin, gulp.series(scripts_admin));
	gulp.watch(js_source_public, gulp.series(scripts_public));
});

/**
* Default
*/
gulp.task('default', gulp.series(styles_admin, styles_public, scripts_admin, scripts_public, 'watch'));