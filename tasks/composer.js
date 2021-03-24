var gulp = require( 'gulp' ),
  fs = require( 'fs' ),
  cp = require( 'child_process' ),
  merge = require('merge-stream'),
  plugins = require( 'gulp-load-plugins' )();

if ( fs.existsSync( './tasks/gulpconfig.json' ) ) {
  gulpconfig = require( './gulpconfig.json' );
} else {
  gulpconfig = require( './gulpconfig.example.json' );
  console.warn( "Don't forget to create your own gulpconfig.json from gulpconfig.json.example" );
}

gulp.task( 'composer:delete_lock_and_vendor', function () {
  return gulp.src( [ 'composer.lock', 'vendor' ] , { allowEmpty: true, read: false } )
    .pipe( plugins.clean() );
} );
gulp.task( 'composer:delete_prefixed_vendor_libraries', function () {
  return gulp.src(
    [
      'vendor/cedaro/wp-plugin',
      'vendor/pimple/pimple',
      'vendor/psr/container',
      'vendor/psr/log',
      'vendor/symfony/polyfill-mbstring',
      'vendor/symfony/polyfill-php72',
    ],
    { allowEmpty: true, read: false }
  )
    .pipe( plugins.clean() );
} );
gulp.task( 'composer:create_vendor_prefixed_folder', function () {
  return gulp.src( '*.*', { read: false } )
    .pipe( gulp.dest( './vendor_prefixed' ) );
} );
gulp.task( 'composer:prefix_lite', function ( cb ) {
  exec( 'composer prefix-dependencies-lite', function ( err, stdout, stderr ) {
    console.log( stdout );
    console.log( stderr );
    cb( err );
  } );
} );
gulp.task( 'composer:prefix', function ( cb ) {
  exec( 'composer prefix-dependencies', function ( err, stdout, stderr ) {
    console.log( stdout );
    console.log( stderr );
    cb( err );
  } );
} );

/**
 * Update namespace of certain files that php-scoper can't patch.
 */
gulp.task( 'composer:prefix_outside_files', function () {
  return merge(

    gulp.src( [ 'vendor_prefixed/symfony/polyfill-mbstring/bootstrap.php' ], { allowEmpty: true } )
      .pipe( plugins.replace( /use Symfony\\Polyfill\\Mbstring/gm, 'use Customify\\Vendor\\Symfony\\Polyfill\\Mbstring' ) )
      .pipe( gulp.dest( 'vendor_prefixed/symfony/polyfill-mbstring/' ) ),

    gulp.src( [ 'vendor_prefixed/symfony/polyfill-mbstring/Resources/mb_convert_variables.php8' ], { allowEmpty: true } )
      .pipe( plugins.replace( /use Symfony\\Polyfill\\Mbstring/gm, 'use Customify\\Vendor\\Symfony\\Polyfill\\Mbstring' ) )
      .pipe( gulp.dest( 'vendor_prefixed/symfony/polyfill-mbstring/Resources/' ) ),

    gulp.src( [ 'vendor_prefixed/symfony/polyfill-php72/bootstrap.php' ], { allowEmpty: true } )
      .pipe( plugins.replace( /use Symfony\\Polyfill\\Php72/gm, 'use Customify\\Vendor\\Symfony\\Polyfill\\Php72' ) )
      .pipe( gulp.dest( 'vendor_prefixed/symfony/polyfill-php72/' ) )
  );
} );
