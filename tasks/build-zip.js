var gulp = require( 'gulp' ),
	fs = require( 'fs' ),
	plugins = require( 'gulp-load-plugins')();

if ( fs.existsSync( './tasks/gulpconfig.json' ) ) {
  gulpconfig = require( './gulpconfig.json' );
} else {
  gulpconfig = require( './gulpconfig.example.json' );
  console.warn( "Don't forget to create your own gulpconfig.json from gulpconfig.json.example" );
}

var slug = gulpconfig.slug;

// -----------------------------------------------------------------------------
// Create the plugin installer archive and delete the build folder
// -----------------------------------------------------------------------------
function makeZip() {
	var versionString = '';
  // get plugin version from the main plugin file
  var contents = fs.readFileSync("./" + slug + ".php", "utf8");

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
	           .pipe( plugins.exec('cd ./../; rm -rf ' + slug[0].toUpperCase() + slug.slice(1) + '*.zip; cd ./build/; zip -r -X ./../' + slug[0].toUpperCase() + slug.slice(1) + versionString + '.zip ./; cd ./../; rm -rf build'));
}
makeZip.description = 'Create the plugin installer archive and delete the build folder';
gulp.task( 'build:zip', makeZip );
