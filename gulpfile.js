var plugin = 'customify',
	source_SCSS = 'scss/**/*.scss',
	dest_CSS = './css/',

	gulp = require('gulp'),
	fs = require('fs'),
	plugins = require('gulp-load-plugins')(),
	del = require('del'),
	cp = require('child_process')

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
function stylesDev() {
	return gulp.src(source_SCSS)
		.pipe(plugins.sass({'sourcemap': true, outputStyle: 'expanded'}))
		.on('error', function (e) {
			console.log(e.message);
		})
		.pipe(plugins.autoprefixer())
		.pipe(plugins.replace(/^@charset \"UTF-8\";\n/gm, ''))
		.pipe(gulp.dest(dest_CSS));
}
gulp.task('styles-dev', stylesDev);

function stylesProd() {
	return gulp.src(source_SCSS)
		.pipe(plugins.sass({'sourcemap': false, outputStyle: 'compressed'}))
		.pipe(plugins.autoprefixer())
		.pipe(plugins.replace(/^@charset \"UTF-8\";\n/gm, ''))
		.pipe(gulp.dest(dest_CSS, {"mode": "0644"}))
}
gulp.task('styles', stylesProd);

gulp.task('styles-watch', function () {
	return gulp.watch(source_SCSS, stylesDev);
});

// javascript stuff
function scriptsMain() {
	return gulp.src(jsFiles)
		.pipe(plugins.concat('main.js'))
		.pipe(plugins.beautify({indentSize: 2}))
		.pipe(gulp.dest('./assets/js/', {"mode": "0644"}));
}
gulp.task('scripts', scriptsMain);

gulp.task('scripts-watch', function () {
	return gulp.watch(source_SCSS, scriptsMain);
});

gulp.task('watch', function () {
	gulp.watch(source_SCSS, stylesDev);
	gulp.watch(source_SCSS, scriptsMain);
});

// usually there is a default task for lazy people who just wanna type gulp
gulp.task('start', function (cb) {
	return gulp.series( 'styles', 'scripts' )(cb);
});

// ============
// TASKS FOR CREATING THE PLUGIN ZIP FILE
// ============

// -----------------------------------------------------------------------------
// Copy plugin folder outside in a build folder
// -----------------------------------------------------------------------------
function copyFolder() {
	var dir = process.cwd();
	return gulp.src( './*' )
		.pipe( plugins.exec( 'rm -Rf ./../build; mkdir -p ./../build/customify;', {
			silent: true,
			continueOnError: true // default: false
		} ) )
		.pipe(plugins.rsync({
			root: dir,
			destination: '../build/customify/',
			// archive: true,
			progress: false,
			silent: true,
			compress: false,
			recursive: true,
			emptyDirectories: true,
			clean: true,
			exclude: ['node_modules']
		}));
}
copyFolder.description = 'Copy plugin production files to a build folder';
gulp.task( 'copy-folder', copyFolder );


// -----------------------------------------------------------------------------
// Remove unneeded files and folders from the build folder
// -----------------------------------------------------------------------------
function removeUnneededFiles() {

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

	return del( files_to_remove, {force: true} );
}
removeUnneededFiles.description = 'Remove unneeded files and folders from the build folder';
gulp.task( 'remove-unneeded-files', removeUnneededFiles );

function maybeFixBuildDirPermissions(done) {

	cp.execSync('find ./../build -type d -exec chmod 755 {} \\;');

	return done();
}
maybeFixBuildDirPermissions.description = 'Make sure that all directories in the build directory have 755 permissions.';
gulp.task( 'fix-build-dir-permissions', maybeFixBuildDirPermissions );

function maybeFixBuildFilePermissions(done) {

	cp.execSync('find ./../build -type f -exec chmod 644 {} \\;');

	return done();
}
maybeFixBuildFilePermissions.description = 'Make sure that all files in the build directory have 644 permissions.';
gulp.task( 'fix-build-file-permissions', maybeFixBuildFilePermissions );

function maybeFixIncorrectLineEndings(done) {

	cp.execSync('find ./../build -type f -print0 | xargs -0 -n 1 -P 4 dos2unix');

	return done();
}
maybeFixIncorrectLineEndings.description = 'Make sure that all line endings in the files in the build directory are UNIX line endings.';
gulp.task( 'fix-line-endings', maybeFixIncorrectLineEndings );

function buildSequence(cb) {
	return gulp.series( 'copy-folder', 'remove-unneeded-files', 'fix-build-dir-permissions', 'fix-build-file-permissions', 'fix-line-endings' )(cb);
}
buildSequence.description = 'Sets up the build folder';
gulp.task( 'build', buildSequence );

// -----------------------------------------------------------------------------
// Create the plugin installer archive and delete the build folder
// -----------------------------------------------------------------------------
function makeZip() {
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
		.pipe(plugins.exec('cd ./../; rm -rf ' + plugin[0].toUpperCase() + plugin.slice(1) + '*.zip; cd ./build/; zip -r -X ./../' + plugin[0].toUpperCase() + plugin.slice(1) + versionString + '.zip ./; cd ./../; rm -rf build'));

}
makeZip.description = 'Create the plugin installer archive and delete the build folder';
gulp.task( 'make-zip', makeZip );

function zipSequence(cb) {
	return gulp.series( 'build', 'make-zip' )(cb);
}
zipSequence.description = 'Creates the zip file';
gulp.task( 'zip', zipSequence  );


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
