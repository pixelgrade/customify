var gulp = require( 'gulp' ),
  fs = require( 'fs' ),
  cp = require( 'child_process' ),
  plugins = require( 'gulp-load-plugins' )();

if ( fs.existsSync( './tasks/gulpconfig.json' ) ) {
  gulpconfig = require( './gulpconfig.json' );
} else {
  gulpconfig = require( './gulpconfig.example.json' );
  console.warn( "Don't forget to create your own gulpconfig.json from gulpconfig.json.example" );
}

var slug = gulpconfig.slug;

function maybeFixBuildDirPermissions( done ) {
  cp.execSync( 'find ./../build -type d -exec chmod 755 {} \\;' );
  return done();
}

maybeFixBuildDirPermissions.description = 'Make sure that all directories in the build directory have 755 permissions.';
gulp.task( 'build:fix:dir-permissions', maybeFixBuildDirPermissions );

function maybeFixBuildFilePermissions( done ) {
  cp.execSync( 'find ./../build -type f -exec chmod 644 {} \\;' );
  return done();
}

maybeFixBuildFilePermissions.description = 'Make sure that all files in the build directory have 644 permissions.';
gulp.task( 'build:fix:file-permissions', maybeFixBuildFilePermissions );

function maybeFixIncorrectLineEndings( done ) {
  cp.execSync( 'find ./../build -type f -print0 | xargs -0 -n 1 -P 4 dos2unix' );
  return done();
}

maybeFixIncorrectLineEndings.description = 'Make sure that all line endings in the files in the build directory are UNIX line endings.';
gulp.task( 'build:fix:line-endings', maybeFixIncorrectLineEndings );

gulp.task( 'build:fix', gulp.series(
  'build:fix:dir-permissions',
  'build:fix:file-permissions',
  'build:fix:line-endings',
) );
