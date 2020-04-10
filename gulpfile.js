const plugin = 'customify',
  source_SCSS = 'scss/*.scss',
  dest_CSS = './css/',

  gulp = require('gulp'),
  fs = require('fs'),
  plugins = require('gulp-load-plugins')(),
  del = require('del'),
  cp = require('child_process'),
  cleanCSS = require('gulp-clean-css'),
  commandExistsSync = require('command-exists').sync

require('es6-promise').polyfill();

const scriptsSrc = ['./js/customizer/*.js','./js/*.js','!./js/**/*.min.js'];

// -----------------------------------------------------------------------------
// This is a helper function used to update the Google fonts list and recreate the php file holding it (includes/resources/google.fonts.php).
// -----------------------------------------------------------------------------
function updatePhpGoogleFontsList(done) {
  const endpoint = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyB7Yj842mK5ogSiDa3eRrZUIPTzgiGopls';
  require('request').get({
    url: endpoint,
  }, function(error, response, body) {
    if (error || !body) throw error

    body = JSON.parse(body);

    //require('fs').writeFileSync('post.json', JSON.stringify(body))

    // First lets inspect the body and make sure it is something we can manage.
    if ( typeof body.items === 'undefined' ) {
      log.error( 'There was no `items` entry in the response from Google apis. Please manually check the response from `'+endpoint+'`');
      return done();
    }

    let fontsList = {};
    // Go through the items and clean it up to our liking.
    body.items.map( function(item){
      delete item.kind;
      delete item.version;
      delete item.lastModified;
      delete item.files;

      fontsList[item.family] = item;
    })

    // Start the PHP code list.
    let php = ['<?php'];

    php.push( '// Returns an associative array with fonts.' );
    php.push( 'return json_decode( \'' + JSON.stringify( fontsList ) + '\', true );' );

    require('fs').writeFileSync('includes/resources/google.fonts.php', php.join( '\r\n' ));

    return done();
  })
}
updatePhpGoogleFontsList.description = 'Fetch and recreate the PHP file holding the Google Fonts list.';
gulp.task( 'update-php-google-fonts-list', updatePhpGoogleFontsList  );


// -----------------------------------------------------------------------------
// Styles related
// -----------------------------------------------------------------------------

function logError( err, res ) {
  log.error( 'Sass failed to compile' );
  log.error( '> ' + err.file.split( '/' )[err.file.split( '/' ).length - 1] + ' ' + 'line ' + err.line + ': ' + err.message );
}

var log = require('fancy-log');

function stylesDev () {
  return gulp.src(source_SCSS)
    .pipe(plugins.sass({'sourcemap': true, outputStyle: 'expanded'})).on('error', logError)
    .pipe(plugins.autoprefixer())
    .pipe(plugins.replace(/^@charset \"UTF-8\";\n/gm, ''))
    .pipe(gulp.dest(dest_CSS))
    .pipe(plugins.rtlcss())
    .pipe(plugins.rename(function (path) {
      path.basename += '-rtl';
    }))
    .pipe(gulp.dest(dest_CSS));
}
gulp.task('styles-dev', stylesDev);

function stylesProd () {
  return gulp.src(source_SCSS)
    .pipe(plugins.sass({'sourcemap': false, outputStyle: 'compressed'}))
    .pipe(plugins.autoprefixer())
    .pipe(plugins.replace(/^@charset \"UTF-8\";\n/gm, ''))
    .pipe(cleanCSS())
    .pipe(gulp.dest(dest_CSS))
    .pipe(plugins.rtlcss())
    .pipe(cleanCSS())
    .pipe(plugins.rename(function (path) {
      path.basename += '-rtl';
    }))
    .pipe(gulp.dest(dest_CSS));
}
gulp.task('styles-prod', stylesProd)

function stylesSequence(cb) {
  return gulp.series( 'styles-prod' )(cb);
}
stylesSequence.description = 'Compile styles';
gulp.task( 'styles', stylesSequence );

// Create minified versions of scripts.
function minifyScripts() {
  return gulp.src(scriptsSrc,{base: './js/'})
    .pipe( plugins.terser({
      warnings: true,
      compress: true, mangle: true,
      output: { comments: 'some' }
    }))
    .pipe(plugins.rename({
      suffix: ".min"
    }))
    .pipe(gulp.dest('./js'));
}
gulp.task( 'minify-scripts', minifyScripts );

function scriptsCompileSequence(cb) {
  gulp.series( 'minify-scripts' )(cb);
}
gulp.task( 'scripts', scriptsCompileSequence );

gulp.task('styles-watch', function () {
	return gulp.watch('scss/**/*.scss', stylesDev);
});

gulp.task('scripts-watch', function () {
  return gulp.watch( scriptsSrc, scriptsCompileSequence );
});

gulp.task('watch', function(cb) {
	return gulp.parallel('styles-watch', 'scripts-watch')(cb);
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
		.pipe( plugins.exec( 'rm -Rf ./../build; mkdir -p ./../build/'+plugin+';', {
			silent: true,
			continueOnError: true // default: false
		} ) )
		.pipe(plugins.rsync({
			root: dir,
			destination: '../build/'+plugin+'/',
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
    'browserslist',
		'palettes.md',
    'scss',
	];

	files_to_remove.forEach( function( e, k ) {
		files_to_remove[k] = '../build/'+plugin+'/' + e;
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

  if (!commandExistsSync('dos2unix')) {
    log( c.red( 'Could not ensure that line endings are correct on the build files since you are missing the "dos2unix" utility! You should install it.' ) );
    log( c.red( 'However, this is not a very big deal. The build task will continue.' ) );
  } else {
    cp.execSync('find ./../build -type f -print0 | xargs -0 -n 1 -P 4 dos2unix');
  }

	return done();
}
maybeFixIncorrectLineEndings.description = 'Make sure that all line endings in the files in the build directory are UNIX line endings.';
gulp.task( 'fix-line-endings', maybeFixIncorrectLineEndings );

function buildSequence(cb) {
	return gulp.series( 'styles', 'scripts', 'copy-folder', 'remove-unneeded-files', 'fix-build-dir-permissions', 'fix-build-file-permissions', 'fix-line-endings' )(cb);
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
