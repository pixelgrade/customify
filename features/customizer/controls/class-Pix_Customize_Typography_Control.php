<?php

/**
 * Class Pix_Customize_Typography_Control
 * A complex Typography Control
 */
class Pix_Customize_Typography_Control extends Pix_Customize_Control {
	public $type = 'typography';
	public $backup = null;
	public $font_weight = true;
	public $subsets = true;
	public $load_all_weights = false;
	public $recommended = array();

	protected static $google_fonts = null;

	private static $std_fonts = array(
		"Arial, Helvetica, sans-serif"                          => "Arial, Helvetica, sans-serif",
		"'Arial Black', Gadget, sans-serif"                     => "'Arial Black', Gadget, sans-serif",
		"'Bookman Old Style', serif"                            => "'Bookman Old Style', serif",
		"'Comic Sans MS', cursive"                              => "'Comic Sans MS', cursive",
		"Courier, monospace"                                    => "Courier, monospace",
		"Garamond, serif"                                       => "Garamond, serif",
		"Georgia, serif"                                        => "Georgia, serif",
		"Impact, Charcoal, sans-serif"                          => "Impact, Charcoal, sans-serif",
		"'Lucida Console', Monaco, monospace"                   => "'Lucida Console', Monaco, monospace",
		"'Lucida Sans Unicode', 'Lucida Grande', sans-serif"    => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
		"'MS Sans Serif', Geneva, sans-serif"                   => "'MS Sans Serif', Geneva, sans-serif",
		"'MS Serif', 'New York', sans-serif"                    => "'MS Serif', 'New York', sans-serif",
		"'Palatino Linotype', 'Book Antiqua', Palatino, serif"  => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
		"Tahoma,Geneva, sans-serif"                             => "Tahoma, Geneva, sans-serif",
		"'Times New Roman', Times,serif"                        => "'Times New Roman', Times, serif",
		"'Trebuchet MS', Helvetica, sans-serif"                 => "'Trebuchet MS', Helvetica, sans-serif",
		"Verdana, Geneva, sans-serif"                           => "Verdana, Geneva, sans-serif",
	);


	/**
	 * Constructor.
	 *
	 * Supplied $args override class property defaults.
	 *
	 * If $args['settings'] is not defined, use the $id as the setting ID.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Manager $manager
	 * @param string $id
	 * @param array $args
	 */
	public function __construct( $manager, $id, $args = array() ) {
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		$this->manager = $manager;
		$this->id = $id;
		if ( empty( $this->active_callback ) ) {
			$this->active_callback = array( $this, 'active_callback' );
		}
		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

		// Process settings.
		if ( empty( $this->settings ) ) {
			$this->settings = $id;
		}

		$settings = array();
		if ( is_array( $this->settings ) ) {
			foreach ( $this->settings as $key => $setting ) {
				$settings[ $key ] = $this->manager->get_setting( $setting );
			}
		} else {
			$this->setting = $this->manager->get_setting( $this->settings );
			$settings['default'] = $this->setting;
		}
		$this->settings = $settings;

		$this->load_google_fonts();

//		$this->generate_google_fonts_json();
	}

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {

		$values = json_decode( $this->value() ); ?>
		<label class="customify_typography">
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif;

			$this_id = str_replace('[', '_', $this->id );
			$this_id = str_replace(']', '_', $this_id ); ?>

			<input class="customify_typography_values" type="hidden" <?php $this->link(); ?> value='<?php echo $this->value(); ?>'/>
			<select class="customify_typography_font_family" id="<?php echo $this_id; ?>" data-placeholder="--<?php _e('Select option', 'customify_txtd'); ?>--">
				<?php
				if ( ! empty( $this->recommended ) ) {
					echo '<optgroup label="' . __('Recommended', 'customify_txtd') . '">';
					foreach ( $this->recommended as $key => $font ) {

						if ( ! isset(self::$google_fonts[$key]) ) {
							continue;
						}

						$font = self::$google_fonts[$key];
						self::output_google_font_option($key, $font);
					}
					echo "</optgroup>";
				}

				if ( PixCustomifyPlugin::get_plugin_option( 'typography_standard_fonts' ) ) {

					echo '<optgroup label="' . __('Standard fonts', 'customify_txtd') . '">';
					foreach ( self::$std_fonts as $key => $font ) {
						echo '<option value="' . $font . '">' . $font . '</option>';
					}
					echo "</optgroup>";
				}

				if ( PixCustomifyPlugin::get_plugin_option( 'typography_google_fonts' ) ) {

					if ( PixCustomifyPlugin::get_plugin_option( 'typography_group_google_fonts' ) ) {

						$grouped_google_fonts = array();
						foreach ( self::$google_fonts as $key => $values ) {
							if ( isset( $values['category'] ) ) {
								$grouped_google_fonts[$values['category']][] = $values;
							}
						}

						foreach ( $grouped_google_fonts as $group_name => $group ) {
							echo '<optgroup label="' . __('Google fonts', 'customify_txtd') . ' ' . $group_name . '">';
							foreach ( $group as $key => $font ) {
								self::output_google_font_option($key, $font);
							}
							echo "</optgroup>";
						}

					} else {
						echo '<optgroup label="' . __('Google fonts', 'customify_txtd') . '">';
						foreach ( self::$google_fonts as $key => $font ) {
							self::output_google_font_option($key, $font);
						}
						echo "</optgroup>";
					}
				} ?>
			</select>
			<?php
			if ( $this->font_weight ) { ?>
				<br/>Font Weight<br/>
				<span class="customify_typography_font_weight">
					<?php if ( isset( $values['font_weight'] ) && ! empty( $values['font_weight'] ) ) {
						foreach ( $values['font_weight'] as $weight ) {
//							var_dump($weight);
						}
					} ?>
				</span>
				<br/>
			<?php
			}

			if ( $this->subsets ) { ?>
				<br/>Subsets<br/>
				<span class="customify_typography_font_subsets">
					<?php if ( isset( $values['subsets'] ) && ! empty( $values['subsets'] ) ) {
						foreach ( $values['subsets'] as $subsets ) {
//							var_dump($subsets);
						}
					} ?>
				</span>
				<br/>
			<?php
			}

			if ( isset( $this->backup ) ) { ?>
				<br/>
				<span class="title"><?php _e('Backup Font', 'customify_txtd'); ?></span>
				<select name="<?php echo str_replace( '_control', '', $this->id ); ?>[backup]" class="customify_typography_backup" data-tags="true" data-placeholder="--<?php _e('Select option', 'customify_txtd'); ?>--">
					<?php
					if ( PixCustomifyPlugin::get_plugin_option( 'typography_standard_fonts' ) ) {
						foreach ( self::$std_fonts as $key => $font ) {
							echo '<option value="' . $font . '">' . $font . '</option>';
						}
					} ?>
				</select>
			<?php } ?>
		</label>
	<?php }

	protected static function output_google_font_option( $key, $font) {
		$data = '';

		if ( isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
			$data .= ' data-variants=\'' . json_encode( $font['variants'], JSON_FORCE_OBJECT ) . '\'';
		}

		if ( isset( $font['subsets'] ) && ! empty( $font['subsets'] ) ) {
			$data .= ' data-subsets=\'' . json_encode( $font['subsets'], JSON_FORCE_OBJECT ) . '\'';
		}

		echo '<option value="' . $font['family'] . '"'. $data .'>' . $font['family'] . '</option>';
	}

	protected function load_google_fonts() {

		$fonts_path = plugin_dir_path( __FILE__ ) . 'resources/google.fonts.php';

		if ( file_exists( $fonts_path ) ) {
			self::$google_fonts = require( $fonts_path );
		}

 		if ( !empty( self::$google_fonts ) ) {
			return self::$google_fonts;
		}

		return false;
	}

	protected function generate_google_fonts_json() {

		$fonts_path = plugin_dir_path( __FILE__ ) . 'resources/google.fonts.php';

		$new_array = array();
		foreach (self::$google_fonts as $key => $font ){
			$new_array[$font->family] = $font;
		}

		file_put_contents( plugin_dir_path( __FILE__ ) . 'resources/google.fonts.json', json_encode($new_array) );
	}
}
