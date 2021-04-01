<?php
/**
 * Extra functionality.
 */

if ( ! function_exists('add_customify_base_options') ) {
	/**
	 * This filter is used to change the Customizer Options
	 * You can also copy this function inside your functions.php
	 * file but DO NOT FORGET TO CHANGE ITS NAME
	 *
	 * @param $config array This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
	 * @return $config
	 */
	function add_customify_base_options( $config ) {

		$config['opt-name'] = 'customify_defaults';

		$config['sections'] = [
			/**
			 * Presets - This section will handle other options
			 */
			'presets_section' => [
				'title'    => esc_html__( 'Style Presets', '__plugin_txtd' ),
				'options' => [
					'theme_style'   => [
						'type'      => 'preset',
						'label'     => esc_html__( 'Select a style:', '__plugin_txtd' ),
						'desc' => esc_html__( 'Conveniently change the design of your site with built-in style presets. Easy as pie.', '__plugin_txtd' ),
						'default'   => 'royal',
						'choices_type' => 'awesome',
						'choices'  => [
							'royal' => [
								'label' => esc_html__( 'Royal', '__plugin_txtd' ),
								'preview' => [
									'color-text' => '#ffffff',
									'background-card' => '#615375',
									'background-label' => '#46414c',
									'font-main' => 'Abril Fatface',
									'font-alt' => 'PT Serif',
								],
								'options' => [
									'links_color' => '#8eb2c5',
									'headings_color' => '#725c92',
									'body_color' => '#6f8089',
									'page_background' => '#615375',
									'headings_font' => 'Abril Fatface',
									'body_font' => 'PT Serif',
								]
							],
							'lovely' => [
								'label' => esc_html__( 'Lovely', '__plugin_txtd' ),
								'preview' => [
									'color-text' => '#ffffff',
									'background-card' => '#d15c57',
									'background-label' => '#5c374b',
									'font-main' => 'Playfair Display',
									'font-alt' => 'Playfair Display',
								],
								'options' => [
									'links_color' => '#cc3747',
									'headings_color' => '#d15c57',
									'body_color' => '#5c374b',
									'page_background' => '#d15c57',
									'headings_font' => 'Playfair Display',
									'body_font' => 'Playfair Display',
								]
							],
							'queen' => [
								'label' => esc_html__( 'Queen', '__plugin_txtd' ),
								'preview' => [
									'color-text' => '#fbedec',
									'background-card' => '#773347',
									'background-label' => '#41212a',
									'font-main' => 'Cinzel Decorative',
									'font-alt' => 'Gentium Basic',
								],
								'options' => [
									'links_color' => '#cd8085',
									'headings_color' => '#54323c',
									'body_color' => '#cd8085',
									'page_background' => '#fff',
									'headings_font' => 'Cinzel Decorative',
									'body_font' => 'Gentium Basic',
								]
							],
							'carrot' => [
								'label' => esc_html__( 'Carrot', '__plugin_txtd' ),
								'preview' => [
									'color-text' => '#ffffff',
									'background-card' => '#df421d',
									'background-label' => '#85210a',
									'font-main' => 'Oswald',
									'font-alt' => 'PT Sans Narrow',
								],
								'options' => [
									'links_color' => '#df421d',
									'headings_color' => '#df421d',
									'body_color' => '#7e7e7e',
									'page_background' => '#fff',
									'headings_font' => 'Oswald',
									'body_font' => 'PT Sans Narrow',
								]
							],



							'adler' => [
								'label' => esc_html__( 'Adler', '__plugin_txtd' ),
								'preview' => [
									'color-text' => '#fff',
									'background-card' => '#0e364f',
									'background-label' => '#000000',
									'font-main' => 'Permanent Marker',
									'font-alt' => 'Droid Sans Mono',
								],
								'options' => [
									'links_color' => '#68f3c8',
									'headings_color' => '#0e364f',
									'body_color' => '#45525a',
									'page_background' => '#ffffff',
									'headings_font' => 'Permanent Marker',
									'body_font' => 'Droid Sans Mono'
								]
							],
							'velvet' => [
								'label' => esc_html__( 'Velvet', '__plugin_txtd' ),
								'preview' => [
									'color-text' => '#ffffff',
									'background-card' => '#282828',
									'background-label' => '#000000',
									'font-main' => 'Pinyon Script',
									'font-alt' => 'Josefin Sans',
								],
								'options' => [
									'links_color' => '#000000',
									'headings_color' => '#000000',
									'body_color' => '#000000',
									'page_background' => '#000000',
									'headings_font' => 'Pinyon Script',
									'body_font' => 'Josefin Sans',
								]
							],

						]
					],
				]
			],

			/**
			 * COLORS - This section will handle different elements colors (eg. links, headings)
			 */
			'colors_section' => [
				'title'    => esc_html__( 'Colors', '__plugin_txtd' ),
				'options' => [
					'links_color'   => [
						'type'      => 'color',
						'label'     => esc_html__( 'Links Color', '__plugin_txtd' ),
						'live' => true,
						'default'   => '#6c6e70',
						'css'  => [
							[
								'property'     => 'color',
								'selector' => 'a, .entry-meta a',
							],
						]
					],
					'headings_color' => [
						'type'      => 'color',
						'label'     => esc_html__( 'Headings Color', '__plugin_txtd' ),
						'live' => true,
						'default'   => '#0aa0d9',
						'css'  => [
							[
								'property'     => 'color',
								'selector' => '.site-title a, h1, h2, h3, h4, h5, h6,
												h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
												.widget-title,
												a:hover, .entry-meta a:hover'
							]
						]
					],
					'body_color'     => [
						'type'      => 'color',
						'label'     => esc_html__( 'Body Color', '__plugin_txtd' ),
						'live' => true,
						'default'   => '#2d3033',
						'css'  => [
							[
								'selector' => 'body',
								'property'     => 'color'
							]
						]
					]
				]
			],

			/**
			 * FONTS - This section will handle different elements fonts (eg. headings, body)
			 */
			'typography_section' => [
				'title'    => esc_html__( 'Fonts', '__plugin_txtd' ),
				'options' => [
					'headings_font' => [
						'type'     => 'font',
						'label'    => esc_html__( 'Headings', '__plugin_txtd' ),
						'default'  => 'Playfair Display',
						'selector' => '.site-title a, h1, h2, h3, h4, h5, h6,
										h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
										.widget-title',
						'font_weight' => true,
						'recommended' => [
							'Playfair Display',
							'Oswald',
							'Lato',
							'Open Sans',
							'Exo',
							'PT Sans',
							'Ubuntu',
							'Vollkorn',
							'Lora',
							'Arvo',
							'Josefin Slab',
							'Crete Round',
							'Kreon',
							'Bubblegum Sans',
							'The Girl Next Door',
							'Pacifico',
							'Handlee',
							'Satify',
							'Pompiere'
						]
					],
					'body_font'     => [
						'type'    => 'font',
						'label'   => esc_html__( 'Body Text', '__plugin_txtd' ),
						'default' => 'Lato',
						'selector' => 'html body',
						'recommended' => [
							'Lato',
							'Open Sans',
							'PT Sans',
							'Cabin',
							'Gentium Book Basic',
							'PT Serif',
							'Droid Serif'
						]
					]
				]
			],

			/**
			 * BACKGROUNDS - This section will handle different elements colors (eg. links, headings)
			 */
			'backgrounds_section' => [
				'title'    => esc_html__( 'Backgrounds', '__plugin_txtd' ),
				'options' => [
					'page_background'   => [
						'type'      => 'color',
						'label'     => esc_html__( 'Page Background', '__plugin_txtd' ),
						'live' => true,
						'default'   => '#ffffff',
						'css'  => [
							[
								'property'     => 'background',
								'selector' => 'body, .site',
							]
						]
					],
				]
			],
			/**
			 * LAYOUTS - This section will handle different elements colors (eg. links, headings)
			 */
			'layout_options' => [
				'title'    => esc_html__( 'Layout', '__plugin_txtd' ),
				'options' => [
					'site_title_size' => [
						'type'  => 'range',
						'label' => esc_html__( 'Site Title Size', '__plugin_txtd' ),
						'live' => true,
						'input_attrs' => [
							'min'   => 24,
							'max'   => 100,
							'step'  => 1,
							'data-preview' => true
						],
						'default' => 24,
						'css' => [
							[
								'property' => 'font-size',
								'selector' => '.site-title',
								'media' => 'screen and (min-width: 1000px)',
								'unit' => 'px',
							]
						]
					],
					'page_content_spacing' => [
						'type'  => 'range',
						'label' => 'Page Content Spacing',
						'live' => true,
						'input_attrs' => [
							'min'   => 0,
							'max'   => 100,
							'step'  => 1,
						],
						'default' => 18,
						'css' => [
							[
								'property' => 'padding',
								'selector' => '.site-content',
								'media' => 'screen and (min-width: 1000px)',
								'unit' => 'px',
							]
						]
					]
				]
			]
		];

		return $config;
	}
}
add_filter( 'customify_filter_fields', 'add_customify_base_options', 5, 1 );

add_action( 'after_switch_theme', function( $old_theme_name, $old_theme ) {
	$current_theme = wp_get_theme();
	// If the current theme is a child theme, show a notice.
	if ( $current_theme->exists()
	     && $old_theme->exists()
	     && $current_theme->get_template() === $old_theme->get_stylesheet() ) {
		add_action( 'admin_notices', 'customify_child_theme_migrate_theme_mods_notice' );
	}
}, 100, 2 );

/**
 * Provide a notice allowing for theme mods migration from the parent theme to the current child theme.
 */
function customify_child_theme_migrate_theme_mods_notice() {
	global $pagenow;

	// We only show the notice on the themes dashboard, and if we are allowed to.
	if ( 'themes.php' !== $pagenow
	     || ! is_child_theme()
	     || true !== apply_filters( 'customify_allow_child_theme_mod_migrate_notice', true )
	     || ! current_user_can( 'manage_options' ) ) {

		return;
	}

	$parent_theme = wp_get_theme( get_template() );
	if ( ! $parent_theme->exists() ) {
		return;
	}

	ob_start(); ?>
	<div class="customify-notice__container updated notice fade is-dismissible">
		<h3><?php echo sprintf( __( 'You have activated a child theme for "%s". Good for you!', '__plugin_txtd' ), $parent_theme->get('Name') ); ?></h3>
		<p>
			<?php echo wp_kses_post( __( 'If you have already <strong>set up things in the Customizer,</strong> you may want to <strong>keep those customizations</strong> so you don\'t start over.', '__plugin_txtd' ) ); ?>
		</p>
		<p>
			<?php echo wp_kses_post( __( 'So, the question is simple: <strong>would you like to migrate all theme-specific options (theme mods) from the parent theme to the child one?</strong>', '__plugin_txtd' ) ); ?>
		</p>
		<p>
			<?php echo wp_kses_post( __( 'All parent theme customizations will remain in place, while those of the active child theme will be overwritten, if any.', '__plugin_txtd' ) ); ?>
		</p>
		<form class="customify-notice-form" method="post">
			<noscript><input type="hidden" name="customify-notice-no-js" value="1"/></noscript>

			<p>
				<button class="customify-notice-button button button-primary js-handle-customify">
					<span class="customify-notice-button__text"><?php esc_html_e( 'Yes, migrate customizations', '__plugin_txtd' ); ?></span>
				</button>
				<button type="submit" class="customify-dismiss-button button button-secondary js-dismiss-customify"><?php esc_html_e( 'No, thank you', '__plugin_txtd' ); ?></button>
				&nbsp;<span class="message js-plugin-message" style="font-style:italic"></span>
			</p>

			<?php wp_nonce_field( 'customify_migrate_customizations_from_parent_to_child_theme', 'nonce-customify_theme_mods_migrate' ); ?>
		</form>
	</div>
	<script>
		(function ($) {
			$(function () {
				let $noticeContainer = $('.customify-notice__container'),
					$button = $noticeContainer.find('.js-handle-customify'),
					$buttonText = $noticeContainer.find('.customify-notice-button__text'),
					$dismissButton = $noticeContainer.find('.js-dismiss-customify'),
					$statusMessage = $noticeContainer.find('.js-plugin-message')

				$button.on('click', function (e) {
					e.preventDefault();

					$buttonText.html("<?php esc_html_e( 'Migrating customizations..' ,'__plugin_txtd'); ?>")
					$button.attr('disabled', true)
					$dismissButton.hide()

					// Do an AJAX call to migrate the theme_mods.
					$.ajax({
						url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
						type: 'post',
						data: {
							action: 'customify_migrate_customizations_from_parent_to_child_theme',
							nonce_migrate: $noticeContainer.find('#nonce-customify_theme_mods_migrate').val()
						}
					})
					.done(function(response) {
						if (typeof response.success !== 'undefined' && response.success) {
							$statusMessage.html("<?php esc_html_e( 'Successfully migrated the parent customizations! Enjoy crafting your site!', '__plugin_txtd' ); ?>")
							$buttonText.html("<?php esc_html_e( 'Finished migration', '__plugin_txtd' ); ?>")
						} else {
							$statusMessage.html("<?php esc_html_e( 'Something went wrong and we couldn\'t migrate the customizations.' ,'__plugin_txtd'); ?>")
							$buttonText.html("<?php esc_html_e( 'Migration error' ,'__plugin_txtd'); ?>")
						}
					})
					.fail(function() {
						$statusMessage.html("<?php esc_html_e( 'Something went wrong and we couldn\'t migrate the customizations.' ,'__plugin_txtd'); ?>")
						$buttonText.html("<?php esc_html_e( 'Migration error' ,'__plugin_txtd'); ?>")
					})
				})

				// Dismiss the notice.
				$dismissButton.on('click', function (e) {
					e.preventDefault();

					$noticeContainer.slideUp();
				})
			})
		})(jQuery)
	</script>
	<?php
	echo ob_get_clean();
}

/**
 * Process ajax call to migrate customizations from parent to current child theme.
 */
function customify_migrate_customizations_from_parent_to_child_theme() {
	// Check nonce.
	check_ajax_referer( 'customify_migrate_customizations_from_parent_to_child_theme', 'nonce_migrate' );

	$parent_theme = wp_get_theme( get_template() );
	if ( ! $parent_theme->exists() ) {
		wp_send_json_error();
	}

	// Migrate theme mods
	$parent_theme_mods = get_option( "theme_mods_" . $parent_theme->get_stylesheet() );
	// We need to exclude certain theme_mods since they are not needed by the child theme.
	$excluded = [
		'pixcare_license',
		'pixcare_new_theme_version',
		'pixcare_install_notice_dismissed',
	];
	foreach ( $excluded as $exclude ) {
		unset( $parent_theme_mods[ $exclude ] );
	}
	// Finally, write the new theme mods for the active child theme.
	if ( ! update_option( "theme_mods_" . get_option( 'stylesheet' ), $parent_theme_mods ) ) {
		wp_send_json_error( esc_html__( 'Could not update the child theme theme_mods.', '__plugin_txtd' ) );
	}

	// Redirect if this is not an ajax request.
	if ( isset( $_POST['pixcare-notice-no-js'] ) ) {

		// Go back to where we came from.
		wp_safe_redirect( wp_get_referer() );
		exit();
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_customify_migrate_customizations_from_parent_to_child_theme', 'customify_migrate_customizations_from_parent_to_child_theme' );

/**
 * Migrate data from the simple Dark Mode control to Advanced Dark Mode Control, if the current theme supports it.
 */
function customify_migrate_to_advanced_dark_mode_control() {
	// Bail if the current theme doesn't support the advanced control.
	$supports_advanced_dark_mode = (bool) current_theme_supports( 'style_manager_advanced_dark_mode' );
	if ( ! $supports_advanced_dark_mode ) {
		return;
	}

	$advanced_dark_mode = get_option( 'sm_dark_mode_advanced', null );
	// Bail if we already have advanced control data saved.
	if ( ! is_null( $advanced_dark_mode ) ) {
		return;
	}

	// Bail if there isn't a simple dark mode option saved.
	$simple_dark_mode = get_option( 'sm_dark_mode', null );
	if ( is_null( $simple_dark_mode ) ) {
		return;
	}

	// If the simple control value was on, we have work to do.
	if ( 'on' === $simple_dark_mode ) {
		$old_sm_dark_primary_final    = get_option( 'sm_dark_primary_final' );
		$old_sm_dark_secondary_final  = get_option( 'sm_dark_secondary_final' );
		$old_sm_dark_tertiary_final   = get_option( 'sm_dark_tertiary_final' );
		$old_sm_light_primary_final   = get_option( 'sm_light_primary_final' );
		$old_sm_light_secondary_final = get_option( 'sm_light_secondary_final' );
		$old_sm_light_tertiary_final  = get_option( 'sm_light_tertiary_final' );

		update_option( 'sm_dark_mode_advanced', 'on' );
		update_option( 'sm_dark_mode', 'off' );
		update_option( 'sm_dark_primary_final', $old_sm_light_primary_final );
		update_option( 'sm_dark_secondary_final', $old_sm_light_secondary_final );
		update_option( 'sm_dark_tertiary_final', $old_sm_light_tertiary_final );
		update_option( 'sm_light_primary_final', $old_sm_dark_primary_final );
		update_option( 'sm_light_secondary_final', $old_sm_dark_secondary_final );
		update_option( 'sm_light_tertiary_final', $old_sm_dark_tertiary_final );
	} else {
		update_option( 'sm_dark_mode_advanced', 'off' );
	}
}
add_action( 'admin_init', 'customify_migrate_to_advanced_dark_mode_control' );


function sm_get_color_select_darker_config( $label, $selector, $default, $properties = [ 'color' ] ) {
	return sm_get_color_select_dark_config( $label, $selector, $default, $properties, true );
}
function sm_get_color_select_dark_config( $label, $selector, $default, $properties = [ 'color' ], $isDarker = false ) {

	$callback = 'sm_color_select_dark_cb';

	$choices = [
		'background' => esc_html__( 'Background', '__plugin_txtd' ),
		'dark'       => esc_html__( 'Dark', '__plugin_txtd' ),
		'accent'     => esc_html__( 'Accent', '__plugin_txtd' ),
	];

	if ( $isDarker ) {
		$callback = 'sm_color_select_darker_cb';

		$choices = [
			'background' => esc_html__( 'Background', '__plugin_txtd' ),
			'darker'     => esc_html__( 'Dark', '__plugin_txtd' ),
			'accent'     => esc_html__( 'Accent', '__plugin_txtd' ),
		];
	}

	$css = [];

	if ( ! is_array( $properties ) ) {
		$properties = [ $properties ];
	}

	foreach ( $properties as $property ) {
		$css[] = [
			'property'        => $property,
			'selector'        => $selector,
			'callback_filter' => $callback,
		];
	}

	return [
		'type'    => 'select_color',
		'label'   => esc_html__( $label, '__plugin_txtd' ),
		'live'    => true,
		'default' => $default,
		'css'     => $css,
		'choices' => $choices,
	];
}

function sm_color_select_dark_cb( $value, $selector, $property ) {
	return $selector . ' { ' . $property . ': var(--sm-current-' . $value . '-color); }' . PHP_EOL;
}

function sm_color_select_dark_cb_customizer_preview() {
	$js = "";

	$js .= "
function sm_color_select_dark_cb(value, selector, property) {
    return selector + ' {' + property + ': var(--sm-current-' + value + '-color);' + '}';
}" . PHP_EOL;

	wp_add_inline_script( 'customify-previewer-scripts', $js );
}
add_action( 'customize_preview_init', 'sm_color_select_dark_cb_customizer_preview', 20 );

function sm_color_select_darker_cb( $value, $selector, $property ) {
	return $selector . ' { ' . $property . ': var(--sm-current-' . $value . '-color); }' . PHP_EOL;
}

function sm_color_select_darker_cb_customizer_preview() {
	$js = "";

	$js .= "
function sm_color_select_darker_cb(value, selector, property) {
    return selector + ' {' + property + ': var(--sm-current-' + value + '-color);' + '}';
}" . PHP_EOL;

	wp_add_inline_script( 'customify-previewer-scripts', $js );
}
add_action( 'customize_preview_init', 'sm_color_select_darker_cb_customizer_preview', 20 );

function sm_get_color_switch_darker_config( $label, $selector, $default, $properties = [ 'color' ] ) {
	return sm_get_color_switch_dark_config( $label, $selector, $default, $properties, true );
}

function sm_get_color_switch_dark_config( $label, $selector, $default, $properties = [ 'color' ], $isDarker = false ) {

	$css = [];
	$callback = 'sm_color_switch_dark_cb';

	if ( ! is_array( $properties ) ) {
		$properties = [ $properties ];
	}

	if ( $isDarker ) {
		$callback = 'sm_color_switch_darker_cb';
	}

	foreach ( $properties as $property ) {
		$css[] = [
			'property'        => $property,
			'selector'        => $selector,
			'callback_filter' => $callback,
		];
	}

	return [
		'type'    => 'sm_switch',
		'label'   => esc_html__( $label, '__plugin_txtd' ),
		'live'    => true,
		'default' => $default,
		'css'     => $css,
		'choices' => [
			'off' => esc_html__( 'Off', '__plugin_txtd' ),
			'on'  => esc_html__( 'On', '__plugin_txtd' ),
		],
	];
}
function sm_color_switch_dark_cb( $value, $selector, $property ) {
	$output = '';
	$color = 'fg1';

	if ( $value === 'on' ) {
		$color = 'accent';
	}

	if ( ! empty( $color ) ) {
		$output .= $selector . ' {' . $property . ': var(--sm-current-' . $color . '-color); }' . PHP_EOL;
	}

	return $output;
}
function sm_color_switch_dark_cb_customizer_preview() {
	$js = "";

	$js .= "
function sm_color_switch_dark_cb(value, selector, property) {
    var color = value === 'on' ? 'accent' : 'fg1';
    return selector + ' { ' + property + ': var(--sm-current-' + color + '-color); }';
}" . PHP_EOL;

	wp_add_inline_script( 'customify-previewer-scripts', $js );
}
add_action( 'customize_preview_init', 'sm_color_switch_dark_cb_customizer_preview', 20 );

function sm_color_switch_darker_cb( $value, $selector, $property ) {
	$output = '';
	$color = 'fg2';

	if ( $value === 'on' ) {
		$color = 'accent';
	}

	if ( ! empty( $color ) ) {
		$output .= $selector . ' {' . $property . ': var(--sm-current-' . $color . '-color); }' . PHP_EOL;
	}

	return $output;
}
function sm_color_switch_darker_cb_customizer_preview() {
	$js = "";

	$js .= "
function sm_color_switch_darker_cb(value, selector, property) {
	var color = value === 'on' ? 'accent' : 'fg2';
	return selector + ' { ' + property + ': var(--sm-current-' + color + '-color); }';
}" . PHP_EOL;

	wp_add_inline_script( 'customify-previewer-scripts', $js );
}
add_action( 'customize_preview_init', 'sm_color_switch_darker_cb_customizer_preview', 20 );
