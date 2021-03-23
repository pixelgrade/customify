var gulp = require( 'gulp' ),
  plugins = require( 'gulp-load-plugins' )(),
  fs = require( 'fs' ),
  del = require( 'del' );

if ( fs.existsSync( './tasks/gulpconfig.json' ) ) {
  gulpconfig = require( './gulpconfig.json' );
} else {
  gulpconfig = require( './gulpconfig.example.json' );
  console.warn( "Don't forget to create your own gulpconfig.json from gulpconfig.json.example" );
}

var slug = gulpconfig.slug;

// -----------------------------------------------------------------------------
// Copy plugin folder outside in a build folder.
// -----------------------------------------------------------------------------
function copyFolder() {
  var dir = process.cwd();
  return gulp.src( './*' )
             .pipe( plugins.exec( 'rm -Rf ./../build; mkdir -p ./../build/' + slug + ';', {
               silent: true,
               continueOnError: true // default: false
             } ) )
             .pipe( plugins.rsync( {
               root: dir,
               destination: '../build/' + slug + '/',
               // archive: true,
               progress: false,
               silent: true,
               compress: false,
               recursive: true,
               emptyDirectories: true,
               clean: true,
               exclude: ['node_modules']
             } ) );
}

copyFolder.description = 'Copy plugin production files to a build folder';
gulp.task( 'build:copy-folder', copyFolder );

// -----------------------------------------------------------------------------
// Remove unneeded files and folders from the build folder.
// -----------------------------------------------------------------------------
async function removeUnneededFiles() {
  const files_to_remove = [];
  const contents = fs.readFileSync( '.zipignore', 'utf8' );

  // Files that should not be present in build
  contents.split( /[\r\n]/ ).forEach( function( path ) {
    path = path.trim();

    // We will skip line starting with # since those are comments (as per the .gitignore standard).
    if ( path && !path.startsWith('#') ) {
      files_to_remove.push( '../build/' + slug + '/' + path );
    }
  } );

  return del( files_to_remove, {force: true} );
}

removeUnneededFiles.description = 'Remove unneeded files and folders from the build folder';
gulp.task( 'build:remove-unneeded-files', removeUnneededFiles );

gulp.task( 'build:folder', gulp.series(
  'build:copy-folder',
  'build:remove-unneeded-files'
) );
