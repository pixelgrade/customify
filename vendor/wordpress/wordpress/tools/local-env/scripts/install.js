const dotenv       = require( 'dotenv' );
const dotenvExpand = require( 'dotenv-expand' );
const wait_on = require( 'wait-on' );
const { execSync } = require( 'child_process' );
const { renameSync, readFileSync, writeFileSync } = require( 'fs' );

dotenvExpand( dotenv.config() );

// Create wp-config.php.
wp_cli( 'config create --dbname=wordpress_develop --dbuser=root --dbpass=password --dbhost=mysql --path=/var/www/src --force' );

// Add the debug settings to wp-config.php.
// Windows requires this to be done as an additional step, rather than using the --extra-php option in the previous step.
wp_cli( `config set WP_DEBUG ${process.env.LOCAL_WP_DEBUG} --raw --type=constant` );
wp_cli( `config set WP_DEBUG_LOG ${process.env.LOCAL_WP_DEBUG_LOG} --raw --type=constant` );
wp_cli( `config set WP_DEBUG_DISPLAY ${process.env.LOCAL_WP_DEBUG_DISPLAY} --raw --type=constant` );
wp_cli( `config set SCRIPT_DEBUG ${process.env.LOCAL_SCRIPT_DEBUG} --raw --type=constant` );
wp_cli( `config set WP_ENVIRONMENT_TYPE ${process.env.LOCAL_WP_ENVIRONMENT_TYPE} --type=constant` );

// Move wp-config.php to the base directory, so it doesn't get mixed up in the src or build directories.
renameSync( 'src/wp-config.php', 'wp-config.php' );

install_wp_importer();

// Read in wp-tests-config-sample.php, edit it to work with our config, then write it to wp-tests-config.php.
const testConfig = readFileSync( 'wp-tests-config-sample.php', 'utf8' )
	.replace( 'youremptytestdbnamehere', 'wordpress_develop_tests' )
	.replace( 'yourusernamehere', 'root' )
	.replace( 'yourpasswordhere', 'password' )
	.replace( 'localhost', 'mysql' )
	.concat( "\ndefine( 'FS_METHOD', 'direct' );\n" );

writeFileSync( 'wp-tests-config.php', testConfig );

// Once the site is available, install WordPress!
wait_on( { resources: [ `tcp:localhost:${process.env.LOCAL_PORT}`] } )
	.then( () => {
		wp_cli( 'db reset --yes' );
		wp_cli( `core install --title="WordPress Develop" --admin_user=admin --admin_password=password --admin_email=test@test.com --skip-email --url=http://localhost:${process.env.LOCAL_PORT}` );
	} );

/**
 * Runs WP-CLI commands in the Docker environment.
 *
 * @param {string} cmd The WP-CLI command to run.
 */
function wp_cli( cmd ) {
	execSync( `docker-compose run --rm cli ${cmd}`, { stdio: 'inherit' } );
}

/**
 * Downloads the WordPress Importer plugin for use in tests.
 */
function install_wp_importer() {
	const test_plugin_directory = 'tests/phpunit/data/plugins/wordpress-importer';

	execSync( `docker-compose exec -T php rm -rf ${test_plugin_directory} && svn checkout -r ${process.env.WP_IMPORTER_REVISION} https://plugins.svn.wordpress.org/wordpress-importer/trunk/ ${test_plugin_directory}`, { stdio: 'inherit' } );
}
