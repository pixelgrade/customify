<?php

final class Customify_Importer_Controller {

	protected $customify;
	protected static $steps = array();

	function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the oEmbed REST API route.
	 *
	 * @since 4.4.0
	 */
	public function register_routes() {

		register_rest_route( 'customify/1.0', 'import', array(
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'import_step' ),
				'args'     => array(
					'action'     => array(
						'required'          => true,
						'sanitize_callback' => 'esc_url_raw',
					),
					'option_key' => array(
						'required'          => true,
						'sanitize_callback' => 'esc_url_raw',
					),
					'step_id'    => array(
						'required' => true,
					),
				),
			),
		) );
	}

	/**
	 * Callback for the API endpoint.
	 *
	 * Returns the JSON object for the post.
	 *
	 * @since 4.4.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 */
	public function import_step( $request ) {
		if ( ! isset( $_POST['option_key'] ) ) {
			wp_send_json_error( esc_html__( 'Missing option key', 'customify' ) );
		}

		$option_key = $_POST['option_key'];
		if ( ! isset( $_POST['step_id'] ) ) {
			wp_send_json_error( esc_html__( 'Missing step id', 'customify' ) );
		}

		$step_id     = $_POST['step_id'];
		$import_step = $this->get_customify_field_data( $option_key, $step_id );

		if ( isset( $_POST['step_type'] ) && ! empty( $_POST['step_type'] ) && in_array( $_POST['step_type'], array(
				'wp_option',
				'recall',
				'remote'
			) )
		) {
			$import_step['type'] = $_POST['step_type'];
		}

		switch ( $import_step['type'] ) {

			case 'wp_option' : {
				$value = $import_step['value'];

				if ( isset( $import_step['base64_encoded'] ) && $import_step['base64_encoded'] ) {
					$value = base64_decode( $value );
					$value = json_decode( $value, true );
					if ( empty( $value ) ) {
						wp_send_json_error( esc_html__( 'Wrong value, I cannot decode', 'customify' ) );
					}
				}

				// first check if the value actually changes
				$current_value = get_option( $step_id );
				if ( $current_value === $value ) {
					wp_send_json_success( esc_html__( 'This option is already here', 'customify' ) );
				}

				$updated = update_option( $step_id, $value );

				if ( ! $updated ) {
					wp_send_json_error( esc_html__( 'I can\'t import this!', 'customify' ) );
				} else {
					wp_send_json_success( 'awesome' );
				}

				break;
			}

			case 'widgets' : {

				if ( isset( $import_step['base64_encoded'] ) && $import_step['base64_encoded'] ) {
					$decoded = base64_decode( $import_step['value'] );
					$decoded = json_decode( $decoded, true );
					if ( empty( $decoded ) ) {
						wp_send_json_error( esc_html__( 'Wrong value, I cannot decode', 'customify' ) );
					}

					$updated = $this->import_widget_data( $decoded );
				} else {
					$updated = $this->import_widget_data( $import_step['value'] );
				}

				if ( ! $updated ) {
					wp_send_json_error( esc_html__( 'I can\'t import this!', 'customify' ) );
				} else {
					wp_send_json_success( 'awesome' );
				}

				break;
			}

			case 'xml' : {
				if ( ! isset( $import_step['file'] ) || ! file_exists( $import_step['file'] ) ) {
					wp_send_json_error( esc_html__( 'No file', 'customify' ) );
				}
				break;
			}

			case 'remote' : {
				if ( ! isset( $import_step['discover_url'] ) ) {
					wp_send_json_error( esc_html__( 'No url', 'customify' ) );
				}
				$data = wp_remote_get( $import_step['discover_url'] );
				$data = wp_remote_retrieve_body( $data );

				$data = json_decode( $data, true );

				if ( $data['success'] && ! empty( $data['data'] ) ) {
					$data = $data['data'];
				} else {
					wp_send_json_error( 'i don\'t evan kno\'' );
				}

				$data = $this->process_remote_data( $data );
				break;
			}

			case 'recall' : {

				if ( ! isset( $_POST['recall_type'] ) ) {
					wp_send_json( array(
						'success' => false,
						'code'    => 'no_type',
						'message' => esc_html__( 'No recall type', 'customify' )
					) );
				}

				if ( ! isset( $_POST['recall_data'] ) ) {
					wp_send_json( array(
						'success' => false,
						'code'    => 'no_data',
						'message' => esc_html__( 'No recall data', 'customify' )
					) );
				}

				switch ( $_POST['recall_type'] ) {

					case 'post_type' : {
						wp_send_json( $_POST );
						$this->import_post_types( $_POST['recall_type'], $_POST['recall_data'] );
						break;
					}

					case 'taxonomy' : {
						$this->import_taxonomies( $_POST['recall_type'], $_POST['recall_data'] );
						break;
					}

					case 'wp_option' : {
						$wp_options       = $_POST['recall_data'];
						$already_imported = false;

						if ( ! empty( $wp_options ) ) {

							foreach ( $wp_options as $value ) {
								// first check if the value actually changes
								$current_value = get_option( $step_id );
								if ( $current_value === $value ) {
									$already_imported = true;
									continue;
								}

								$updated = update_option( $step_id, $value );
							}
						}

						if ( $already_imported ) {
							wp_send_json( array(
								'success' => false,
								'code'    => 'already_imported',
								'message' => esc_html__( 'This is already imported', 'customify' )
							) );
						}
						wp_send_json( array(
							'success' => true,
							'code'    => 'imported',
							'message' => esc_html__( 'Done', 'customify' )
						) );
						break;
					}

					default : {
						wp_send_json( array(
							'success' => false,
							'code'    => 'wrong_recall_type',
							'message' => esc_html__( 'wrong recall type', 'customify' )
						) );
						break;
					}
				}

				wp_send_json( array(
					'success' => false,
					'code'    => 'what',
					'message' => esc_html__( 'I dont think i should be here', 'customify' )
				) );
				break;
			}

			default : {
				wp_send_json( array(
					'success' => false,
					'code'    => 'wrong_type',
					'message' => esc_html__( 'Wrong import type', 'customify' )
				) );
				break;
			}
		}

		// look for this step
		return 'you should be here';
	}

	protected function import_post_types( $post_type, $data ) {
		wp_send_json( $post_type );
		$result = array();
		if ( is_array( $data ) && isset( $data['results'] ) && ! empty( $data['results'] ) ) {
				$result[ $post_type ] = array();
				$post_exists          = false;
				if ( post_type_exists( $post_type ) && is_array( $data['results'] ) && ! empty( $data['results'] ) ) {

				foreach ( $data['results'] as $id => $post_args ) {

					$post_exists = get_page_by_title( $post_args['post_title'] );

					if ( $post_exists ) {
						continue;
					}

					$args = array_intersect_key( (array) $post_args
						, array(
							'post_author'           => 0,
							'post_date'             => 0,
							'post_date_gmt'         => 0,
							'post_content'          => 0,
							'post_content_filtered' => 0,
							'post_title'            => 0,
							'post_excerpt'          => 0,
							'post_status'           => 'published',
							'post_type'             => 'post',
							'ping_status'           => 'closed',
							'post_password'         => '',
							'post_name'             => '',
							'to_ping'               => '',
							'pinged'                => '',
							'post_modified'         => 0,
							'post_modified_gmt'     => 0,
							'post_parent'           => 0,
							'menu_order'            => '',
							'post_mime_type'        => '',
						) );

					/**
					 * ID
					 * comment_count
					 * comment_status
					 * filter
					 * guid */

					/**
					 * @TODO Still needed
					 * 'guid'
					 * (string) Global Unique ID for referencing the post. Default empty.
					 * 'tax_input'
					 * (array) Array of taxonomy terms keyed by their taxonomy name. Default empty.
					 * 'meta_input'
					 *
					 * All metadata
					 */

					$result[ $post_type ][ $id ] = wp_insert_post( $args );
				}


				wp_send_json($data);

				if ( ! empty( $post_exists ) ) {
					wp_send_json( array(
						'success' => false,
						'code'    => 'exists',
						'message' => esc_html__( 'They are already here', 'customify' )
					) );
				} elseif ( empty( $result[ $post_type ] ) ) {
					wp_send_json( array(
						'success' => false,
						'code'    => 'empty',
						'message' => esc_html__( 'Nothing to import', 'customify' )
					) );
				}
			}
		}
	}

	protected function import_taxonomies( $tax, $data ) {
		$result = array();

		if ( is_array( $data ) && isset( $data['results'] ) && ! empty( $data['results'] ) ) {

			$result[ $tax ] = array();
			$term_exists    = false;

			if ( taxonomy_exists( $tax ) && is_array( $data['results'] ) && ! empty( $data['results'] ) ) {
				foreach ( $data['results'] as $id => $term_args ) {

					$term_exists = term_exists( $term_args['name'], $tax );
					if ( $term_exists !== 0 && $term_exists !== null ) {
						continue;
					}

					$args = array_intersect_key( (array) $term_args
						, array(
							'description' => '',
							'parent'      => 0,
							'slug'        => '',
						) );

					$result[ $tax ][ $id ] = wp_insert_term( $term_args['name'], $tax, $args );
				}
			}

			wp_send_json( $result[ $tax ] );

			if ( ! empty( $term_exists ) ) {
				wp_send_json( array(
					'success' => false,
					'code'    => 'exists',
					'message' => esc_html__( 'They are already here', 'customify' )
				) );
			} elseif ( empty( $result[ $tax ] ) ) {
				wp_send_json( array(
					'success' => false,
					'code'    => 'empty',
					'message' => esc_html__( 'Nothing to import', 'customify' )
				) );
			}
		}


		wp_send_json( $result );
	}

	protected function process_remote_data( $data ) {

		if ( isset( $data['wp_options'] ) && ! empty( $data['wp_options'] ) ) {
			$this->add_step( 'wp_options', 'wp_option', $data['wp_options'] );
		}

		if ( isset( $data['widgets'] ) && ! empty( $data['widgets'] ) ) {
			$this->add_step( 'widgets', 'widgets', $data['widgets'] );
		}

		if ( isset( $data['widgets'] ) && ! empty( $data['widgets'] ) ) {}

		// select what you can get from the export
		if ( isset( $data['taxonomies'] ) && ! empty( $data['taxonomies'] ) ) {

			foreach ( $data['taxonomies'] as $tax => $term_args ) {

				if ( ! taxonomy_exists( $tax ) ) {
					unset( $data['taxonomies'][ $tax ] );
					continue;
				}

				$term_exists = false;

				if ( ! isset( $term_args['results'] ) || empty( $term_args['results'] ) ) {
					$this->add_step( $tax, 'taxonomy', array( 'error' => 'empty' ) );
					continue;
				}

				foreach ( $term_args['results'] as $term_id => $term ) {
					$term_exists = term_exists( $term['name'], $tax );
					if ( $term_exists !== 0 && $term_exists !== null ) {
						unset( $data['taxonomies'][ $tax ]['results'][ $term_id ] );
					}
				}

				// now if after the check if this is still  empty .. .sry for ya
				if ( empty( $data['taxonomies'][ $tax ] ) && ! empty( $term_exists ) ) {
					$data['taxonomies'][ $tax ] = 'already_imported';
				}

				$this->add_step( $tax, 'taxonomy', $data['taxonomies'][ $tax ] );
			}
		}

		if ( isset( $data['post_types'] ) && ! empty( $data['post_types'] ) ) {
			foreach ( $data['post_types'] as $post_type => $post_type_args ) {

				if ( ! post_type_exists( $post_type ) || empty( $post_type_args['results'] ) ) {
					continue;
				}

				$post_exists = false;

				if ( ! isset( $post_type_args['results'] ) || empty( $post_type_args['results'] ) ) {
					$this->add_step( $post_type, 'post_type', array( 'error' => 'empty' ) );
					continue;
				}

				foreach ( $post_type_args['results'] as $post_id => $post ) {
					$post_exists = get_page_by_title( $post['post_title'], OBJECT, $post_type );
					if ( ! empty( $post_exists ) ) {
						unset( $data['post_types'][ $post_type ]['results'][ $post_id ] );
					}
				}

				// if still empty good by
				if ( empty( $data['post_types'][ $post_type ] ) && ! empty( $post_exists ) ) {
					$data['post_types'][ $post_type ] = 'already_imported';
				}

				$this->add_step( $post_type, 'post_type', $data['post_types'][ $post_type ] );
			}

			unset( $data['post_types'] );
		}

		wp_send_json_success( $this->get_steps() );

		return $data;
	}

	protected function add_step( $id, $type, $data ) {
		self::$steps[] = array(
			'id' => $id,
			'type' => $type,
			'data' => $data
		);
	}

	protected function get_steps(  ) {
		return self::$steps;
	}

	protected function get_customify_field_data( $option_key, $step_id ) {

		$options = PixCustomifyPlugin()->get_options_configs();

		if ( ! isset( $options[ $option_key ] ) ) {
			wp_send_json_error( 'inexistent key' );
		}

		$option_config = $options[ $option_key ];

		if ( ! isset( $options[ $option_key ]['imports'] ) ) {
			wp_send_json_error( 'where is imports????' );
		}

		$imports = $options[ $option_key ]['imports'];

		if ( isset( $imports[ $step_id ] ) ) {
			return $imports[ $step_id ];
		}

		return false;
	}


	/**
	 * Parse JSON import file and load data - pass to import
	 */
	function import_widget_data( $json_data ) {

		if ( empty( $json_data ) ) {
			return false;
		}

		//first let's remove all the widgets in the sidebars to avoid a big mess
		$sidebars_widgets = wp_get_sidebars_widgets();
		foreach ( $sidebars_widgets as $sidebarID => $widgets ) {
			if ( $sidebarID != 'wp_inactive_widgets' ) {
				$sidebars_widgets[ $sidebarID ] = array();
			}
		}
		wp_set_sidebars_widgets( $sidebars_widgets );

		$sidebar_data = $json_data[0];
		$widget_data  = $json_data[1];

		foreach ( $sidebar_data as $title => $sidebar ) {
			$count = count( $sidebar );
			for ( $i = 0; $i < $count; $i ++ ) {
				$widget               = array();
				$widget['type']       = trim( substr( $sidebar[ $i ], 0, strrpos( $sidebar[ $i ], '-' ) ) );
				$widget['type-index'] = trim( substr( $sidebar[ $i ], strrpos( $sidebar[ $i ], '-' ) + 1 ) );
				if ( ! isset( $widget_data[ $widget['type'] ][ $widget['type-index'] ] ) ) {
					unset( $sidebar_data[ $title ][ $i ] );
				}
			}
			$sidebar_data[ $title ] = array_values( $sidebar_data[ $title ] );
		}

		$sidebar_data = array( array_filter( $sidebar_data ), $widget_data );

		if ( ! self::parse_import_data( $sidebar_data ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Import widgets
	 */
	public static function parse_import_data( $import_array ) {
		$sidebars_data = $import_array[0];
		$widget_data   = $import_array[1];

		$current_sidebars = get_option( 'sidebars_widgets' );
		$new_widgets      = array();

		foreach ( $sidebars_data as $import_sidebar => $import_widgets ) :
			$current_sidebars[ $import_sidebar ] = array();
			foreach ( $import_widgets as $import_widget ) :

				//if the sidebar exists
				//if ( isset( $current_sidebars[$import_sidebar] ) ) :
				$title               = trim( substr( $import_widget, 0, strrpos( $import_widget, '-' ) ) );
				$index               = trim( substr( $import_widget, strrpos( $import_widget, '-' ) + 1 ) );
				$current_widget_data = get_option( 'widget_' . $title );
				$new_widget_name     = self::get_new_widget_name( $title, $index );
				$new_index           = trim( substr( $new_widget_name, strrpos( $new_widget_name, '-' ) + 1 ) );

				if ( ! empty( $new_widgets[ $title ] ) && is_array( $new_widgets[ $title ] ) ) {
					while ( array_key_exists( $new_index, $new_widgets[ $title ] ) ) {
						$new_index ++;
					}
				}
				$current_sidebars[ $import_sidebar ][] = $title . '-' . $new_index;
				if ( array_key_exists( $title, $new_widgets ) ) {
					$new_widgets[ $title ][ $new_index ] = $widget_data[ $title ][ $index ];
					if ( ! empty( $new_widgets[ $title ]['_multiwidget'] ) ) {
						$multiwidget = $new_widgets[ $title ]['_multiwidget'];
						unset( $new_widgets[ $title ]['_multiwidget'] );
						$new_widgets[ $title ]['_multiwidget'] = $multiwidget;
					} else {
						$new_widgets[ $title ]['_multiwidget'] = null;
					}
				} else {
					$current_widget_data[ $new_index ] = $widget_data[ $title ][ $index ];
					if ( ! empty( $current_widget_data['_multiwidget'] ) ) {
						$current_multiwidget = $current_widget_data['_multiwidget'];
						$new_multiwidget     = $widget_data[ $title ]['_multiwidget'];
						$multiwidget         = ( $current_multiwidget != $new_multiwidget ) ? $current_multiwidget : 1;
						unset( $current_widget_data['_multiwidget'] );
						$current_widget_data['_multiwidget'] = $multiwidget;
					} else {
						$current_widget_data['_multiwidget'] = null;
					}
					$new_widgets[ $title ] = $current_widget_data;
				}

				//endif;
			endforeach;
		endforeach;

		if ( isset( $new_widgets ) && isset( $current_sidebars ) ) {
			update_option( 'sidebars_widgets', $current_sidebars );

			foreach ( $new_widgets as $title => $content ) {
				update_option( 'widget_' . $title, $content );
			}

			return true;
		}

		return false;
	}

	public static function get_new_widget_name( $widget_name, $widget_index ) {
		$current_sidebars = get_option( 'sidebars_widgets' );
		$all_widget_array = array();
		foreach ( $current_sidebars as $sidebar => $widgets ) {
			if ( ! empty( $widgets ) && is_array( $widgets ) && $sidebar != 'wp_inactive_widgets' ) {
				foreach ( $widgets as $widget ) {
					$all_widget_array[] = $widget;
				}
			}
		}
		while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array ) ) {
			$widget_index ++;
		}
		$new_widget_name = $widget_name . '-' . $widget_index;

		return $new_widget_name;
	}
}