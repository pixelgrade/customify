<?php
/**
 * Views: Settings page
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

declare ( strict_types = 1 );

namespace PixelgradeLT\Records;

use PixelgradeLT\Records\PackageType\BasePackage;

/**
 * @global BasePackage[] $packages
 * @global string $packages_permalink
 * @global array $system_checks
 */

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a href="#pixelgradelt_records-settings" class="nav-tab nav-tab-active"><?php esc_html_e( 'Settings', 'pixelgradelt_records' ); ?></a>
		<a href="#pixelgradelt_records-packages" class="nav-tab"><?php esc_html_e( 'Packages', 'pixelgradelt_records' ); ?></a>
		<a href="#pixelgradelt_records-status" class="nav-tab"><?php esc_html_e( 'Status', 'pixelgradelt_records' ); ?></a>
	</h2>

	<div id="pixelgradelt_records-settings" class="pixelgradelt_records-tab-panel is-active">
		<p>
			<?php esc_html_e( 'Your PixelgradeLT Records repository is available at:', 'pixelgradelt_records' ); ?>
			<a href="<?php echo esc_url( $packages_permalink ); ?>"><?php echo esc_html( $packages_permalink ); ?></a>
		</p>
		<p>
			<?php
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Need to update global variable.
			$allowed_html = [ 'code' => [] ];
			printf(
				/* translators: 1: <code>repositories</code>, 2: <code>composer.json</code> */
				esc_html__( 'Add it to the %1$s list in your project\'s %2$s, like so:', 'pixelgradelt_records' ),
				'<code>repositories</code>',
				'<code>composer.json</code>'
			);
			?>
		</p>

		<pre class="pixelgradelt_records-repository-snippet"><code>{
	"repositories": [
		{
			"type": "composer",
			"url": "<?php echo esc_url( get_packages_permalink( [ 'base' => true ] ) ); ?>"
		}
	]
}</code></pre>

		<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
			<?php settings_fields( 'pixelgradelt_records' ); ?>
			<?php do_settings_sections( 'pixelgradelt_records' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>

	<div id="pixelgradelt_records-packages" class="pixelgradelt_records-tab-panel">
		<?php require $this->plugin->get_path( 'views/packages.php' ); ?>
	</div>

	<div id="pixelgradelt_records-status" class="pixelgradelt_records-tab-panel">
		<?php require $this->plugin->get_path( 'views/status.php' ); ?>
	</div>
</div>
