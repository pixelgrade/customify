var plugin = 'customify',
	source_SCSS = 'scss/**/*.scss',
	dest_CSS = './css/',

	gulp 		= require('gulp'),
	sass 		= require('gulp-sass'),
	prefix 		= require('gulp-autoprefixer'),
	exec 		= require('gulp-exec'),
	replace 	= require('gulp-replace'),
	minify 		= require('gulp-minify-css'),
	concat 		= require('gulp-concat'),
	notify 		= require('gulp-notify'),
	beautify 	= require('gulp-beautify'),
	csscomb 	= require('gulp-csscomb'),
	cmq 		= require('gulp-combine-media-queries'),
	fs          = require('fs'),
	rtlcss 		= require('rtlcss'),
	postcss 	= require('gulp-postcss'),
	del         = require('del'),
	rename 		= require('gulp-rename');

require('es6-promise').polyfill();

var jsFiles = [
	'./assets/js/vendor/*.js',
	'./assets/js/main/wrapper_start.js',
	'./assets/js/main/shared_vars.js',
	'./assets/js/modules/*.js',
	'./assets/js/main/main.js',
	'./assets/js/main/functions.js',
	'./assets/js/main/wrapper_end.js'
];


var options = {
	silent: true,
	continueOnError: true // default: false
};

// styles related
gulp.task('styles-dev', function () {
	return gulp.src(source_SCSS)
		.pipe(sass({'sourcemap': false, style: 'compact'}))
			.on('error', function (e) {
				console.log(e.message);
			})
		.pipe(prefix("last 1 version", "> 1%", "ie 8", "ie 7"))
        .pipe(gulp.dest(dest_CSS));
		// .pipe(postcss([
		//     require('rtlcss')({ /* options */ })
		// ]))
		// .pipe(rename("rtl.css"))
		// .pipe(gulp.dest('./'))
});

gulp.task('styles', function () {
	return gulp.src(source_SCSS)
		.pipe(sass({'sourcemap': true, style: 'expanded'}))
		.pipe(prefix("last 1 version", "> 1%", "ie 8", "ie 7"))
		.pipe(csscomb())
        .pipe(gulp.dest(dest_CSS, {"mode": "0644"}))
});

gulp.task('styles-watch', function () {
	return gulp.watch(source_SCSS, ['styles']);
});

// javascript stuff
gulp.task('scripts', function () {
	return gulp.src(jsFiles)
		.pipe(concat('main.js'))
		.pipe(beautify({indentSize: 2}))
		.pipe(gulp.dest('./assets/js/', {"mode": "0644"}));
});

gulp.task('scripts-watch', function () {
	return gulp.watch(source_SCSS, ['scripts']);
});

gulp.task('watch', function () {
	gulp.watch(source_SCSS, ['styles-dev']);
	// gulp.watch('assets/js/**/*.js', ['scripts']);
});

// usually there is a default task for lazy people who just wanna type gulp
gulp.task('start', ['styles', 'scripts'], function () {
	// silence
});

gulp.task('server', ['styles', 'scripts'], function () {
	console.log('The styles and scripts have been compiled for production! Go and clear the caches!');
});

/**
 * Create a zip archive out of the cleaned folder and delete the folder
 */
gulp.task( 'zip', ['build'], function() {
    var versionString = '';
    // get plugin version from the main plugin file
    var contents = fs.readFileSync("./" + plugin + ".php", "utf8");

    // split it by lines
    var lines = contents.split(/[\r\n]/);

    function checkIfVersionLine(value, index, ar) {
        var myRegEx = /^[\s\*]*[Vv]ersion:/;
        if (myRegEx.test(value)) {
            return true;
        }
        return false;
    }

    // apply the filter
    var versionLine = lines.filter(checkIfVersionLine);

    versionString = versionLine[0].replace(/^[\s\*]*[Vv]ersion:/, '').trim();
    versionString = '-' + versionString.replace(/\./g, '-');

    return gulp.src('./')
        .pipe(exec('cd ./../; rm -rf ' + plugin[0].toUpperCase() + plugin.slice(1) + '*.zip; cd ./build/; zip -r -X ./../' + plugin[0].toUpperCase() + plugin.slice(1) + versionString + '.zip ./; cd ./../; rm -rf build'));

} );

/**
 * Copy theme folder outside in a build folder, recreate styles before that
 */
gulp.task( 'copy-folder', function() {

	return gulp.src( './' )
		.pipe( exec( 'rm -Rf ./../build; mkdir -p ./../build/customify; cp -Rf ./* ./../build/customify/' ) );
} );

/**
 * Clean the folder of unneeded files and folders
 */
gulp.task( 'build', ['copy-folder'], function() {

	// files that should not be present in build zip
	files_to_remove = [
		'**/codekit-config.json',
		'node_modules',
		'config.rb',
		'gulpfile.js',
		'package.json',
        'package-lock.json',
		'pxg.json',
		'build',
		'.idea',
		'**/*.css.map',
		'**/.git*',
		'*.sublime-project',
		'.DS_Store',
		'**/.DS_Store',
		'__MACOSX',
		'**/__MACOSX',
		'+development.rb',
		'+production.rb',
		'README.md',
		'.labels',
        '.csscomb',
        '.csscomb.json',
        '.codeclimate.yml',
        'tests',
        'circle.yml',
        '.circleci',
        '.labels',
        '.jscsrc',
        '.jshintignore',
        'browserslist'
	];

	files_to_remove.forEach( function( e, k ) {
		files_to_remove[k] = '../build/customify/' + e;
	} );

	return del.sync(files_to_remove, {force: true});
} );

// usually there is a default task  for lazy people who just wanna type gulp
gulp.task('default', ['start'], function () {
	// silence
});

/**
 * Short commands help
 */

gulp.task('help', function () {

	var $help = '\nCommands available : \n \n' +
		'=== General Commands === \n' +
		'start              (default)Compiles all styles and scripts and makes the theme ready to start \n' +
		'zip                Generate the zip archive \n' +
		'build              Generate the build directory with the cleaned theme \n' +
		'help               Print all commands \n' +
		'=== Style === \n' +
		'styles             Compiles styles in production mode\n' +
		'styles-dev         Compiles styles in development mode \n' +
		'styles-admin       Compiles admin styles \n' +
		'=== Scripts === \n' +
		'scripts            Concatenate all js scripts \n' +
		'scripts-dev        Concatenate all js scripts \n' +
		'=== Watchers === \n' +
		'watch              Watches all js and scss files \n' +
		'styles-watch       Watch only styles\n' +
		'scripts-watch      Watch scripts only \n';

	console.log($help);

});
