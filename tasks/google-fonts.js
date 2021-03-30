var gulp = require( 'gulp' ),
  fs = require( 'fs' ),
  request = require('request'),
  plugins = require( 'gulp-load-plugins' )();

if ( fs.existsSync( './tasks/gulpconfig.json' ) ) {
  gulpconfig = require( './gulpconfig.json' );
} else {
  gulpconfig = require( './gulpconfig.example.json' );
  console.warn( "Don't forget to create your own gulpconfig.json from gulpconfig.json.example" );
}

// -----------------------------------------------------------------------------
// This is a helper function used to update the Google fonts list and recreate the php file holding it (includes/resources/google.fonts.php).
// -----------------------------------------------------------------------------
function updatePhpGoogleFontsList(done) {
  const endpoint = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyAn2JiVvY0QL1T1430udIHS-nB3vBnjrf4';
  request.get({
    url: endpoint,
  }, function(error, response, body) {
    if (error || !body) throw error

    body = JSON.parse(body);

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

    fs.writeFileSync('resources/google.fonts.php', php.join( '\r\n' ));

    return done();
  })
}
updatePhpGoogleFontsList.description = 'Fetch and recreate the PHP file holding the Google Fonts list.';
gulp.task( 'update-php-google-fonts-list', updatePhpGoogleFontsList  );
