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
	public $typekit_fonts = array();
	public $current_value;

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

		$this->typekit_fonts = get_option( 'typekit_fonts' );


		$this->current_value = $this->value();
//		$this->generate_google_fonts_json();
	}

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {

		$current_value = $this->value();

		// if this value was an array, well it was wrong
		if ( is_array( $current_value ) ) {

			$current_value['font_family'] = $current_value['font-family'];
			unset( $current_value['font-family'] );
			$current_value = json_encode($current_value);
		}

		$values = json_decode( $current_value );

		$font_family = '';
		if ( isset( $values->font_family ) ) {
			$font_family = $values->font_family;
		}

		if ( isset( $values->load_all_weights ) ) {
			$this->load_all_weights = $values->font_load_all_weights;
		} ?>
		<label class="customify_typography">
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif;

			$this_id = str_replace('[', '_', $this->id );
			$this_id = str_replace(']', '_', $this_id );
			$select_data = '';
			if ( $this->load_all_weights ) {
				$select_data .= ' data-load_all_weights="true"';
			}

			/**
			 * This input will hold the values of this typography field
			 */ ?>
			<input class="customify_typography_values" id="<?php echo $this_id; ?>" type="hidden" <?php $this->link(); ?> value='<?php echo $current_value; ?>'/>
			<select class="customify_typography_font_family"<?php echo $select_data;?>>
				<?php

				if ( ! empty( $this->typekit_fonts ) ) {
					echo '<optgroup label="' . __('Typekit', 'customify_txtd') . '">';
					foreach ( $this->typekit_fonts as $key => $font ) {
						self::output_font_option($font['css_names'][0], $font_family, $font, 'typekit' );
					}
					echo "</optgroup>";
				}

				if ( ! empty( $this->recommended ) ) {
					echo '<optgroup label="' . __('Recommended', 'customify_txtd') . '">';
					foreach ( $this->recommended as $key => $font ) {

						if ( ! isset(self::$google_fonts[$key]) ) {
							continue;
						}

						$font = self::$google_fonts[$key];
						self::output_font_option($key, $font_family, $font);
					}
					echo "</optgroup>";
				}

				if ( PixCustomifyPlugin::get_plugin_option( 'typography_standard_fonts' ) ) {

					echo '<optgroup label="' . __('Standard fonts', 'customify_txtd') . '">';
					foreach ( self::$std_fonts as $key => $font ) {
						self::output_font_option($key, $font_family, $font, 'std');

					}
					echo "</optgroup>";
				}

				if ( PixCustomifyPlugin::get_plugin_option( 'typography_google_fonts' ) ) {

					if ( PixCustomifyPlugin::get_plugin_option( 'typography_group_google_fonts' ) ) {

						$grouped_google_fonts = array();
						foreach ( self::$google_fonts as $key => $font ) {
							if ( isset( $font['category'] ) ) {
								$grouped_google_fonts[$font['category']][] = $font;
							}
						}

						foreach ( $grouped_google_fonts as $group_name => $group ) {
							echo '<optgroup label="' . __('Google fonts', 'customify_txtd') . ' ' . $group_name . '">';
							foreach ( $group as $key => $font ) {
								self::output_font_option($key, $font_family, $font);
							}
							echo "</optgroup>";
						}

					} else {
						echo '<optgroup label="' . __('Google fonts', 'customify_txtd') . '">';
						foreach ( self::$google_fonts as $key => $font ) {
							self::output_font_option($key, $font_family, $font);
						}
						echo "</optgroup>";
					}
				} ?>
			</select>
		</label>
		<ul class="options">
			<?php
			if (! $this->load_all_weights && $this->font_weight &&  ( isset( $values->variants ) && ! empty( $values->variants ) ) ) { ?>
				<li class="customify_subsets_wrapper">
					<label><?php _e('Font Weight', 'customify_txtd');?></label>
					<select class="customify_typography_font_weight">
						<?php
						foreach ( $values->variants as $weight ) {
							echo '<option value="'. $weight . '. "> ' .  $weight . '</option>';
						} ?>
					</select>
				</li>
				<?php
			}

			if ( $this->subsets && ( isset( $values->subsets ) && ! empty( $values->subsets ) ) ) { ?>
				<li class="customify_subsets_wrapper">
					<label><?php _e('Subsets', 'customify_txtd'); ?></label>
					<select multiple class="customify_typography_font_subsets">
						<?php

						$selected = array();

						if ( isset( $values->selected_subsets ) ) {
							$selected = $values->selected_subsets;
						}

						foreach ( $values->subsets as $key => $subset ) {
							$attrs = '';
							if ( in_array( $subset, (array)$selected ) ) {
								$attrs .= ' selected="selected"';
							}

							echo '<option value="'. $subset . '. "'.$attrs.'> ' . $subset . '</option>';
						}
						?>
					</select>
				</li>
			<?php } ?>
		</ul>
	<?php }

	/**
	 * This method makes an <option> tag from the given params
	 * @param $key
	 * @param $font_family
	 * @param $font
	 * @param string $type
	 */
	protected static function output_font_option( $key, $font_family, $font, $type = 'google' ) {
		$data = '';

		$data .= ' data-type="' . $type . '"';

		if ( $type === 'google' ) {

			if ( isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data .= ' data-variants=\'' . json_encode( (object) $font['variants'] ) . '\'';
			}

			if ( isset( $font['subsets'] ) && ! empty( $font['subsets'] ) ) {
				$data .= ' data-subsets=\'' . json_encode( (object) $font['subsets'] ) . '\'';
			}

			$selected = ( $font_family === $font['family'] ) ? ' selected="selected" ' : '';
			echo '<option value="' . $font['family'] . '"'. $selected . $data .'>' . $font['family'] . '</option>';
		} elseif ( $type === 'typekit' ) {

			$selected = ( $font_family === $key) ? ' selected="selected" ' : '';
			echo '<option class="typekit_font" value="' . $key . '"'. $selected . $data .'>' . $font['name'] . '</option>';
		}  else {
			$selected = ( $font_family === $font) ? ' selected="selected" ' : '';
			echo '<option class="std_font" value="' . $font . '"'. $selected . $data .'>' . $font . '</option>';
		}
	}

	/**
	 * Load the google fonts list from the local file
	 * @return bool|mixed|null
	 */
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

	/**
	 * This method is used only to update the google fonts json file
	 */
	protected function generate_google_fonts_json() {

		$fonts_path = plugin_dir_path( __FILE__ ) . 'resources/google.fonts.php';

		$new_array = array();
		foreach (self::$google_fonts as $key => $font ){
			// unset unused data
			unset( $font['kind'] );
			unset( $font['version'] );
			unset( $font['lastModified'] );
			unset( $font['files'] );
			$new_array[$font['family']] = $font;
		}

		file_put_contents( plugin_dir_path( __FILE__ ) . 'resources/google.fonts.json', json_encode($new_array) );
	}
}
