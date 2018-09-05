<?php

/**
 *
 * A class to handle the display and dismissal of pixcloud admin notices.
 *
 * This class started from this one https://github.com/jlad26/admin-notice-manager/blob/master/admin-notice-manager/class-admin-notice-manager.php
 *
 */
class Pixcloud_Admin_Notifications_Manager {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Pixcloud_Admin_Notifications_Manager
	 * @access  protected
	 * @since   1.9.0
	 */
	protected static $_instance = null;

	/**
	 * The unique identifier of this admin notice manager.
	 *
	 * @access  protected
	 * @since   1.9.0
	 * @var     string $manager_id The string used to uniquely identify this manager.
	 *                             Used as a prefix to keys when storing in usermeta.
	 */
	protected $manager_id;

	/**
	 * The version of this plugin.
	 *
	 * @access    protected
	 * @since   1.9.0
	 * @var        string $version The current version of the parent plugin.
	 */
	protected $version;

	/**
	 * The url to the assets directory.
	 *
	 * @access  protected
	 * @since   1.9.0
	 * @var     string $url_to_assets_dir Url to the directory where the files admin-notice-manager.js and
	 *                                                admin-notice-manager.css are located. Must include trailing slash.
	 */
	protected $url_to_assets_dir;

	/**
	 * The text domain for translation.
	 *
	 * @access  protected
	 * @since   1.9.0
	 * @var     string $text_domain Text domain for translation.
	 */
	protected $text_domain;

	/**
	 * Notifications to be processed and maybe displayed.
	 *
	 * @access  protected
	 * @since   1.9.0
	 * @var     array $notifications
	 */
	protected $notifications = array();

	/**
	 * The supported notification types and their conversions.
	 *
	 * @access  protected
	 * @since  1.9.0
	 * @var array
	 */
	protected $types = array(
		'info'             => 'info',
		'standard_info'    => 'info',
		'success'          => 'success',
		'standard_success' => 'success',
		'warning'          => 'warning',
		'standard_warning' => 'warning',
		'error'            => 'error',
		'standard_error'   => 'error',
		'custom'           => 'custom',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.9.0
	 *
	 * @param array $args
	 */
	protected function __construct( $args = array() ) {
		$this->init( $args );
	}

	/**
	 * Initialize the notifications manager.
	 *
	 * @since  1.9.0
	 *
	 * @param   array $args {
	 *
	 *      @type    string $manager_id Unique id for this manager. Used as a prefix for database keys.
	 *      @type    string $plugin_name Name of the parent plugin.
	 *      @type    string $url_to_assets_dir Path to directory containing admin-notice-manager.js.
	 *      @type    string $text_domain Text domain for translation.
	 *      @type    string $version Plugin version.
	 *      @type    array $notifications Array of array of notifications with keys the notification id
	 *  }
	 */
	public function init( array $args ) {
		// We will only initialize in the WP dashboard (admin area).
		if ( ! is_admin() ) {
			return;
		}

		$defaults = array(
			'manager_id'        => 'pixcloud_anm',
			'plugin_name'       => 'Unnamed plugin',
			'url_to_assets_dir' => '',
			'text_domain'       => 'default',
			'version'           => '',
			'notifications'   => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Set manager id, text domain and version.
		$this->manager_id  = empty( $args['manager_id'] ) ? 'pixcloud_anm' : $args['manager_id'];
		$this->text_domain = $args['text_domain'];
		$this->version     = $args['version'];
		$this->plugin_name = $args['plugin_name'];
		$this->notifications = $args['notifications'];

		// Add hooks, but only if we are not uninstalling the plugin.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			$this->add_hooks();
		}

		// Set url to js directory, adding trailing slash if needed. Defaults to this file's directory.
		if ( empty( $args['url_to_assets_dir'] ) ) {
			$args['url_to_assets_dir'] = plugin_dir_url( __FILE__ );
		}
		if ( '/' != substr( $args['url_to_assets_dir'], - 1 ) ) {
			$args['url_to_assets_dir'] .= '/';
		}
		$this->url_to_assets_dir = $args['url_to_assets_dir'];

		// Make sure that the conditions processing logic is loaded.
		require_once 'class-notification-conditions.php';
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.9.0
	 */
	public function add_hooks() {
		// Add action to load remote notifications.
		add_action( 'admin_init', array( $this, 'maybe_load_remote_notifications' ) );

		// Add actions to display notices as needed.
		add_action( 'admin_notices', array( $this, 'display_notices' ) );

		// Add JS and ajax processing to handle dismissal of persistent notices.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_' . $this->manager_id . '_dismiss_admin_notice', array( $this, 'dismiss_notice' ) );

		// Add notifications data to requests.
		add_filter( 'customify_pixelgrade_cloud_request_data', array( $this, 'add_data_to_request' ), 10, 1 );
	}

	public function maybe_load_remote_notifications() {
		if ( empty( $this->notifications ) ) {
			$this->notifications = $this->convert_remote_notifications_config( $this->get_remote_notifications_config() );
		}
	}

	/**
	 * Get the remote notifications configuration.
	 *
	 * @since 1.9.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get_remote_notifications_config( $skip_cache = false ) {
		// Make sure that the Design Assets class is loaded.
		require_once dirname(__FILE__ ) . '/../lib/class-customify-design-assets.php';

		// Get the design assets data.
		$design_assets = Customify_Design_Assets::instance()->get( $skip_cache );
		if ( false === $design_assets || empty( $design_assets['notifications'] ) ) {
			$notifications_config = array();
		} else {
			$notifications_config = $design_assets['notifications'];
		}

		return apply_filters( 'customify_get_remote_notifications', $notifications_config );
	}

	public function convert_remote_notifications_config( $notifications_config ) {
		$notifications = array();
		if ( empty( $notifications_config ) || ! is_array( $notifications_config ) ) {
			return $notifications;
		}

		foreach ( $notifications_config as $notification_config ) {
			// Make sure we have something to work with.
			if ( empty( $notification_config['hashid'] ) || empty( $notification_config['options'] ) || ! is_array( $notification_config['options'] ) ) {
				continue;
			}

			$options = $notification_config['options'];

			$options['id'] = $notification_config['hashid'];

			// Make sure we support the notification type.
			if ( empty( $options['type']) || ! array_key_exists( $options['type'], $this->types ) ) {
				continue;
			}
			// Use the standard (converted) type.
			$options['type'] = $this->types[ $options['type'] ];

			// Handle the empty message.
			if ( empty( $options['message'] ) ) {
				continue;
			}

			// Parse the details.
			$notification = $this->parse_notice_args( $options );

			if ( ! empty( $notification ) ) {
				$notifications[ $notification_config['hashid'] ] = $notification;
			}
		}

		return $notifications;
	}

	/**
	 * Parse notice args.
	 *
	 * @access  protected
	 * @since  1.9.0
	 *
	 * @param   array $notice Array of user-set args
	 *
	 * @return  array|false Notice with defaults validated and set as necessary, or false if values not validated.
	 */
	protected function parse_notice_args( $notice ) {
		if ( empty( $notice ) ) {
			return false;
		}

		// Set the notice arguments using defaults where necessary. (user_ids should always be set for an opt out notice.)
		$defaults = array(
			'type'                  => 'error',
			'message'               => '',
			'container_classes'     => array(),
			'wrap_tag'              => 'p',
			'user_ids'              => array(),
			'screen_ids'            => array(),
			'post_ids'              => array(),
			'persistent'            => false,
			'dismissable'           => true,
			'no_js_dismissable'     => false,
			'dismiss_for_all_users' => false,
		);

		$notice = wp_parse_args( $notice, $defaults );

		// Set the notice id if not already set.
		if ( ! isset( $notice['id'] ) ) {
			$notice['id'] = md5( $notice['message'] . $notice['type'] );
		}

		// Set the screen ids if not already set. (Will always already be set for opt out notices.)
		if ( ! isset( $notice['screen_ids'] ) ) {

			// Default is generally all screens...
			$notice['screen_ids'] = array();

			// ...but for persistent notices we set default to current screen if we can
			if ( $notice['persistent'] && $screen_id = $this->get_current_screen_id() ) {
				$notice['screen_ids'] = array( $screen_id );
			}
		}

		// If notice is not dismissable, set no-js-dismissable to false as well.
		if ( ! $notice['dismissable'] ) {
			$notice['no_js_dismissable'] = false;
		}

		// Validate all values.
		foreach ( $notice as $key => $value ) {

			switch ( $key ) {
				case 'id' :
					if ( ! is_string( $value ) && ! is_int( $value ) ) {
						return false;
					}
					break;
				case 'message' :
					if ( ! is_string( $value ) && ! is_array( $value ) ) {
						return false;
					}
					if ( empty( $value ) ) {
						return false;
					}
					break;
				case 'wrap_tag' :
					if ( ! is_string( $value ) ) {
						return false;
					}
					break;
				case 'user_ids' :
					if ( ! is_array( $value ) ) {
						return false;
					} else {
						foreach ( $value as $user_info ) {
							if ( ! is_int( $user_info ) && ! is_string( $user_info ) ) {
								return false;
								break;
							}
						}
					}
					break;
				case 'screen_ids' :
					if ( ! is_array( $value ) ) {
						return false;
					} else {
						foreach ( $value as $screen_id ) {
							if ( ! is_string( $screen_id ) ) {
								return false;
								break;
							}
						}
					}
					break;
				case 'post_ids' :
					if ( ! is_array( $value ) ) {
						return false;
					} else {
						foreach ( $value as $post_id ) {
							if ( ! is_int( $post_id ) ) {
								return false;
								break;
							}
						}
					}
					break;
				case 'persistent' :
				case 'dismissable' :
				case 'dismiss_for_all_users' :
					if ( ! is_bool( $value ) ) {
						return false;
					}
					break;
				default:
					break;
			}
		}

		return $notice;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param    string $hook Hook
	 *
	 * @hooked admin_enqueue_scripts
	 */
	public function enqueue_scripts( $hook ) {
		// Note that handle is not specific to the manager id so if we are using two plugins with the notice manager files are only loaded once.
		wp_enqueue_script( 'pixcloud-admin-notice-manager-js', $this->url_to_assets_dir . 'admin-notice-manager.js', array( 'jquery' ), $this->version );
		wp_enqueue_style( 'pixcloud-admin-notice-manager-css', $this->url_to_assets_dir . 'admin-notice-manager.css', array(), $this->version );
	}

	/**
	 * Add a notification.
	 *
	 * @since  1.9.0
	 *
	 * @param	array	$notice {
	 * 		@type	string			$id					Unique id for this notice. Default is hashed value of some of the notice parameters.
	 *													Setting an id is recommended however - otherwise non-unique ids are possible and may
	 *													cause unexpected deletion of notices. Updating messages when they are changed by the
	 *													developer gets fiddly too.
	 * 		@type	string			$message			Message to be displayed.
	 * 		@type	string			$wrap_tag			Tag to wrap message in. Default is 'p'. Set to empty string or false for no wrap.
	 * 		@type	string			$type				One of 'success', 'error', warning', 'info'. Default is 'error'.
	 * 		@type	array			$user_ids			Empty array means all users. Default is all users (not current user).
	 *													For example: array( 3, 'administrator', 55, 153, 'editors' ) will set the message
	 *													for users with ids of 3, 55 and 153, and for all users that are administrators or editors.
	 * 		@type	array			$screen_ids			Array of screen ids on which message should be displayed.
	 * 													Set to empty array for all screens. If left unset the current screen is set if possible,
	 *													it is recommended to explicitly specify the desired screen rather than leaving unset.
	 *													If during testing the notice is set on a screen that is then not viewed because of a redirect
	 *													(e.g. options), changing the screen in the notice args will have no effect because the notice
	 *													has been stored in the db and will not be updated.
	 *													Default is all screens (empty array).
	 * 		@type	array			$post_ids			Array of post ids on which message should be displayed. Empty array means all posts.
	 *													Default is all posts.
	 * 		@type	string			$persistent			True for persistent, false for one-time. Default is false.
	 * 		@type	bool			$dismissable		Whether notice is dismissable. Default is true.
	 * 		@type	bool			$no_js_dismissable	Whether to give option to dismiss notice if no js. Only applies when $dismissable is true.
	 *													Default is false. Caution should be used in setting this to true. The act of dismissing the
	 *													notice refreshes the screen so any changed data on screen will be lost. This could be extremely
	 *													frustrating for a user who has just entered or updated loads of data (e.g., when editing a post).
	 * 		@type	bool			$dismiss_for_all_users		Whether to delete notice for all users or just the user that has dismissed the notice.
	 *													Only applies when $dismissable is true. Default is false.
	 * }
	 * @return  array|WP_Error|bool   Notice that has been set by user, or error if notice has failed.
	 */
	public function add_notification( array $notice ) {

		// If not set, set user ids to empty array to indicate all users.
		if ( ! isset( $notice['user_ids'] ) ) {
			$notice['user_ids'] = array();
		}

		// If required, set default screen ids to all screens.
		if ( ! isset( $notice['screen_ids'] ) ) {
			$notice['screen_ids'] = array();
		}

		$notice = $this->parse_notice_args( $notice );

		$notice_id = $notice['id'];
		unset( $notice['id'] );

		$notices = $this->notifications;

		// Add new notice to existing notices. NB this over-writes a notice with the same id.
		$notices[ $notice_id ] = $notice;

		// Update notices.
		$this->notifications = $notices;

		return $notice;

	}

	/**
	 * Add notifications.
	 *
	 * @since  1.9.0
	 *
	 * @param   array $notices Array of notices. If notice id is not set, key of array is used.
	 *
	 * @return  array Array with keys as notice ids and values as either notice set or WP Error object.
	 */
	public function add_notifications( array $notices ) {

		$results = array();

		foreach ( $notices as $key => $notice ) {
			if ( ! isset( $notice['id'] ) ) {
				$notice['id'] = $key;
			}
			$results[] = $this->add_notification( $notice );
		}

		return $results;

	}

	/**
	 * Parse user ids, converting roles to user ids.
	 *
	 * @access  protected
	 * @since  1.9.0
	 *
	 * @param   array $user_ids
	 *
	 * @return  array $user_ids Parsed user ids
	 */
	protected function parse_user_roles( array $user_ids ) {

		// Convert user roles to user ids and add them.
		if ( ! empty( $user_ids ) ) {

			$user_roles = array();
			foreach ( $user_ids as $key => $user_info ) {
				if ( is_string( $user_info ) ) {
					$user_roles[] = $user_info;
					unset( $user_ids[ $key ] );
				}
			}
			if ( ! empty( $user_roles ) ) {
				$args          = array(
					'count_total' => false,
					'fields'      => 'ID',
					'role__in'    => $user_roles,
				);
				$role_user_ids = get_users( $args );

				if ( ! empty( $role_user_ids ) ) {
					$user_ids = array_unique( array_merge( $user_ids, $role_user_ids ) );
				}
			}
		}

		return $user_ids;

	}


	/**
	 * Get current screen id.
	 *
	 * @since  1.9.0
	 *
	 * @access    protected
	 * @return    int    $screen_id        id of current screen, 0 if not available
	 */
	protected function get_current_screen_id() {
		$screen_id = 0;
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( ! empty( $screen ) ) {
				$screen_id = $screen->id;
			}
		}

		return $screen_id;
	}

	/**
	 * Returns html for a dismiss on redirect link.
	 *
	 * @since  1.9.0
	 *
	 * @param    array $args {
	 *
	 *      @type        string $content Html to display as link.
	 *      @type        string $redirect Redirect url. Set as empty string for no redirect. Default is no redirect.
	 *      @type        string $new_tab If true, link is opened in a new window / tab (equivalent to target="_blank". Default is false.
	 *                                   Only works on browsers with js enabled.
	 *      @type        array $classes Array of classes for the button. Default is array( pixcloud_anm-link ) which styles as a link.
	 *  }
	 * @return string Button html (styled by default as link)
	 */
	public function dismiss_on_redirect_link( array $args ) {

		$defaults = array(
			'content'  => 'Undefined',
			'redirect' => '',
			'new_tab'  => false,
			'classes'  => array( 'pixcloud_anm-link' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$classes = array( 'pixcloud_anm-dismiss' );
		if ( ! empty( $args['classes'] ) && is_array( $args['classes'] ) ) {
			$classes = array_merge( $args['classes'], $classes );
		}
		$classes = implode( ' ', $classes );

		// Add button with value of redirect url.
		return '<button type="submit" class="' . $classes . '" name="pixcloud_anm-redirect" data-newtab="' . intval( $args['new_tab'] ) . '" value="' . esc_attr( $args['redirect'] ) . '">' . $args['content'] . '</button>';

	}

	/**
	 * Returns html for button that triggers a specific action hook.
	 *
	 * @since  1.9.0
	 *
	 * @param    array $args {
	 *
	 *      @type        string $content Html to display as button / link content.
	 *      @type        string $event String to identify dismiss event. The action triggered will be
	 *                                                "{$manager_id}_user_notice_dismissed_{$notice_id}_{$event}" and the dismissing
	 *                                                user id is passed as an argument to the action. Leave unset for no specific action to be fired.
	 *      @type        array $classes Array of classes for the button. Default is array( pixcloud_anm-link ) which styles as a link.
	 *  }
	 * @return string
	 */
	public function dismiss_event_button( array $args ) {

		$defaults = array(
			'content' => 'Undefined',
			'event'   => '',
			'classes' => array( 'pixcloud_anm-link' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$classes = array( 'pixcloud_anm-dismiss', 'pixcloud_anm-event' );
		if ( ! empty( $args['classes'] ) && is_array( $args['classes'] ) ) {
			$classes = array_merge( $args['classes'], $classes );
		}
		$classes = implode( ' ', $classes );

		// Add button with value of event.
		return '<button type="submit" class="' . $classes . '" name="pixcloud_anm-event" value="' . esc_attr( $args['event'] ) . '">' . $args['content'] . '</button>';

	}

	/**
	 * Display user notices.
	 *
	 * @since  1.9.0
	 *
	 * @hooked admin_notices
	 */
	public function display_notices() {

		$screen_id = $this->get_current_screen_id();
		$user_id   = get_current_user_id();

		$this->display_opt_out_notices( $screen_id, $user_id );
	}

	/**
	 * Display opt out notices.
	 *
	 * @access    protected
	 * @since  1.9.0
	 *
	 * @param int $screen_id Current screen id (0 if not known).
	 * @param int $user_id   Current user id.
	 */
	protected function display_opt_out_notices( $screen_id, $user_id ) {

		global $post;
		$post_id = is_object( $post ) ? $post->ID : 0;

		$notices = $this->notifications;

		if ( ! empty( $notices ) ) {
			$user_notifications_details = get_user_meta( $user_id, $this->manager_id . '_notifications', true );

			foreach ( $notices as $notice_id => $notice ) {

				$notice['id'] = $notice_id;

				// If screen ids have been specified, check whether notice should be displayed
				if ( ! empty( $notice['screen_ids'] ) ) {
					if ( ! in_array( $screen_id, $notice['screen_ids'] ) ) {
						continue;
					}
				}

				// If post ids have been specified, check whether notice should be displayed
				if ( ! empty( $notice['post_ids'] ) ) {
					if ( ! in_array( $post_id, $notice['post_ids'] ) ) {
						continue;
					}
				}

				// If user ids or roles have been specified, check whether notice should be displayed to the current user.
				if ( ! empty( $notice['user_ids'] ) ) {
					// Convert roles to user ids
					$user_ids = $this->parse_user_roles( $notice['user_ids'] );

					if ( ! in_array( $user_id, $user_ids ) ) {
						continue;
					}
				}

				// Check whether user has already dismissed this notice
				if ( ! empty( $user_notifications_details ) ) {
					if ( array_key_exists( $notice['id'], $user_notifications_details ) && ! empty( $user_notifications_details[ $notice['id'] ]['dismissed'] ) ) {
						continue;
					}
				}

				// Check the start and end dates.
				if ( ! empty( $notice['start_date'] ) && time() < strtotime( $notice['start_date'] ) ) {
					continue;
				}
				if ( ! empty( $notice['end_date'] ) && strtotime( $notice['end_date'] ) < time() ) {
					continue;
				}

				$display = true;
				// If the notice has local conditions, process them.
				if ( ! empty( $notice['local_conditions'] ) ) {
					$display = Pixcloud_Notification_Conditions::process( $notice['local_conditions'] );
				}

				// Display the notice with option to filter display.
				if ( true === apply_filters( $this->manager_id . '_display_notice', $display, $notice ) ) {

					if ( 'custom' !== $notice['type'] ) {
						$this->display_standard_notice( $notice );
					} else {
						$this->display_custom_notice( $notice );
					}

					// Register the notice display
					$this->register_user_notice_display( $notice['id'], $user_id );

					// Remove notice once viewed if this is a one-time notice.
					if ( ! $notice['persistent'] ) {
						$this->dismiss_notice_for_user( $notice['id'], $user_id );
					}
				}
			}
		}
	}

	/**
	 * Display standard notice.
	 *
	 * @access protected
	 * @since  1.9.0
	 *
	 * @param array $notice array of notice parameters.
	 */
	protected function display_standard_notice( $notice ) {

		// Add classes to the notice container as needed.
		$container_classes = array(
			'notice',
			'notice-' . esc_attr( $notice['type'] ),
		);
		if ( $notice['dismissable'] ) {
			$container_classes[] = 'is-dismissible';
		}
		if ( $notice['persistent'] && $notice['dismissable'] ) {
			$container_classes[] = 'notice-manager-ajax';
		}

		// Parse any content tags.
		$notice['message'] = self::parse_content_tags( $notice['message'] );

		// Convert newlines to <br>s.
		$notice['message'] = nl2br( $notice['message'] );

		if ( ! empty( $notice['wrap_tag'] ) ) {
			$notice['message'] = '<' . $notice['wrap_tag'] . '>' . $notice['message'] . '</' . $notice['wrap_tag'] . '>';
		}

		// Display the notice markup.
		?>
		<div id="<?php echo esc_attr( $this->manager_id . '-' . $notice['id'] ); ?>" class="<?php echo implode( ' ', $container_classes ); ?>">
			<form class="pixcloud_anm-form"
			      action="<?php echo admin_url( 'admin-ajax.php?action=' . $this->manager_id . '_dismiss_admin_notice' ); ?>"
			      method="post">
				<?php if ( in_array( 'notice-manager-ajax', $container_classes ) ) { ?>
					<input type="hidden" class="pixcloud_anm-id" value="<?php echo esc_attr( $this->manager_id ); ?>"/>
					<input type="hidden" class="pixcloud_anm-notice-id" name="noticeID"
					       value="<?php echo esc_attr( $this->manager_id . '-' . $notice['id'] ); ?>"/>
					<noscript><input type="hidden" name="pixcloud_anm-no-js" value="1"/></noscript>
					<?php wp_nonce_field( $this->manager_id . '_dismiss_admin_notice', 'nonce-pixcloud_anm-' . $this->manager_id . '-' . $notice['id'] );
				}
				if ( $notice['no_js_dismissable'] ) {
					?>
					<noscript>
						<table>
							<tr>
								<td style="width: 100%">
					</noscript><?php
				}

				echo $notice['message'];

				if ( $notice['no_js_dismissable'] ) {
					?>
					<noscript>
						</td>
						<td>
							<button type="submit" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'customify' ); ?></span>
							</button>
						</td>
						</tr></table>
					</noscript>
				<?php } ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display custom notice.
	 *
	 * @access protected
	 * @since  1.9.0
	 *
	 * @param array $notice array of notice parameters.
	 */
	protected function display_custom_notice( $notice ) {

		// Add classes to the notice container as needed.
		$container_classes = array(
			'notice', // this class is needed by WordPress to properly identify notices and move/enhance them with JS.
		);

		// Merge with the custom classes received.
		if (  ! empty( $notice['container_classes' ] ) || is_array( $notice['container_classes'] ) ) {
			$container_classes = array_merge( $container_classes, $notice['container_classes'] );
		}

		if ( $notice['dismissable'] ) {
			$container_classes[] = 'is-dismissible';
		}
		if ( $notice['persistent'] && $notice['dismissable'] ) {
			$container_classes[] = 'notice-manager-ajax';
		}

		// Standardize the message since we can receive the markup directly or a complex data structure with markup, CSS, and so on.
		if ( is_string( $notice['message'] ) ) {
			$notice['message'] = array(
					'markup' => $notice['message'],
			);
		}

		// Bail if we don't have any markup.
		if ( empty( $notice['message']['markup'] ) ) {
			return;
		}

		// Parse any content tags.
		$notice['message']['markup'] = self::parse_content_tags( $notice['message']['markup'] );

		// Output the custom CSS.
		if ( ! empty( $notice['message']['css'] ) ) { ?>
			<style type="text/css">
				<?php echo $notice['message']['css']; ?>
			</style>
		<?php }

		// Display the notice markup.
		?>
		<div id="<?php echo esc_attr( $this->manager_id . '-' . $notice['id'] ); ?>" class="<?php echo implode( ' ', $container_classes ); ?>">
			<form class="pixcloud_anm-form"
			      action="<?php echo admin_url( 'admin-ajax.php?action=' . $this->manager_id . '_dismiss_admin_notice' ); ?>"
			      method="post">
				<?php if ( in_array( 'notice-manager-ajax', $container_classes ) ) { ?>
					<input type="hidden" class="pixcloud_anm-id" value="<?php echo esc_attr( $this->manager_id ); ?>"/>
					<input type="hidden" class="pixcloud_anm-notice-id" name="noticeID"
					       value="<?php echo esc_attr( $this->manager_id . '-' . $notice['id'] ); ?>"/>
					<noscript><input type="hidden" name="pixcloud_anm-no-js" value="1"/></noscript>
					<?php wp_nonce_field( $this->manager_id . '_dismiss_admin_notice', 'nonce-pixcloud_anm-' . $this->manager_id . '-' . $notice['id'] );
				}
				if ( $notice['no_js_dismissable'] ) {
					?>
					<noscript>
						<table>
							<tr>
								<td style="width: 100%">
					</noscript><?php
				}

				echo $notice['message']['markup'];

				if ( $notice['no_js_dismissable'] ) {
					?>
					<noscript>
						</td>
						<td>
							<button type="submit" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'customify' ); ?></span></button>
						</td>
						</tr></table>
					</noscript>
				<?php } ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Replace any content tags present in the content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function parse_content_tags( $content ) {
		$original_content = $content;

		// Allow others to alter the content before we do our work
		$content = apply_filters( 'pixcloud_notifications_before_parse_content_tags', $content );

		// Now we will replace all the supported tags with their value
		// %year%
		$content = str_replace( '%year%', date( 'Y' ), $content );

		// %site-title% or %site_title%
		$content = str_replace( '%site-title%', get_bloginfo( 'name' ), $content );
		$content = str_replace( '%site_title%', get_bloginfo( 'name' ), $content );

		// Handle the current user tags.
		if ( false !== strpos( $content, '%user_first_name%' ) ||
		     false !== strpos( $content, '%user_last_name%' ) ||
		     false !== strpos( $content, '%user_nickname%' ) ||
		     false !== strpos( $content, '%user_display_name%' ) ) {
			$user = wp_get_current_user();

			if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
				// %first_name%
				if ( ! empty( $user->first_name ) ) {
					$content = str_replace( '%user_first_name%', $user->first_name, $content );
				} else {
					// Fallback to display_name.
					$content = str_replace( '%user_first_name%', $user->display_name, $content );
				}
				// %last_name%
				$content = str_replace( '%user_last_name%', $user->last_name, $content );
				// %display_name%
				$content = str_replace( '%user_nickname%', $user->display_name, $content );
				// %nickname%
				$content = str_replace( '%user_display_name%', $user->nickname, $content );
			}
		}

		// %active_theme%
		$content = str_replace( '%active_theme%', Pixcloud_Notification_Conditions::get_active_theme_name(), $content );

		// %customify_version%
		$content = str_replace( '%customify_version%', Pixcloud_Notification_Conditions::get_customify_version(), $content );

		// %style_manager_version%
		$content = str_replace( '%style_manager_version%', Pixcloud_Notification_Conditions::get_style_manager_version(), $content );

		// %current_color_palette%
		$content = str_replace( '%current_color_palette%', Pixcloud_Notification_Conditions::get_current_color_palette_label(), $content );

		/*
		 * URLs.
		 */
		// %home_url%
		$content = str_replace( '%home_url%', home_url(), $content );

		// %customizer_url%
		$content = str_replace( '%customizer_url%', wp_customize_url(), $content );
		// %customizer_style_manager_url%
		$section_link = add_query_arg( array( 'autofocus[panel]' => 'style_manager_panel' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_style_manager_url%', $section_link, $content );
		// %customizer_style_manager_colors_url%
		$section_link = add_query_arg( array( 'autofocus[section]' => 'sm_color_palettes_section' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_style_manager_colors_url%', $section_link, $content );
		// %customizer_style_manager_fonts_url%
		$section_link = add_query_arg( array( 'autofocus[section]' => 'sm_font_palettes_section' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_style_manager_fonts_url%', $section_link, $content );
		// %customizer_theme_options_url%
		$section_link = add_query_arg( array( 'autofocus[panel]' => 'theme_options_panel' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_theme_options_url%', $section_link, $content );
		// %customizer_menus_url%
		$section_link = add_query_arg( array( 'autofocus[panel]' => 'nav_menus' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_menus_url%', $section_link, $content );
		// %customizer_widgets_url%
		$section_link = add_query_arg( array( 'autofocus[panel]' => 'widgets' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_widgets_url%', $section_link, $content );
		// %customizer_homepage_settings_url%
		$section_link = add_query_arg( array( 'autofocus[section]' => 'static_front_page' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_homepage_settings_url%', $section_link, $content );
		// %customizer_site_identity_url%
		$section_link = add_query_arg( array( 'autofocus[section]' => 'publish_settings' ), admin_url( 'customize.php' ) );
		$content = str_replace( '%customizer_site_identity_url%', $section_link, $content );

		// %pixelgrade_care_dashboard_url%
		$content = str_replace( '%pixelgrade_care_dashboard_url%', admin_url( 'admin.php?page=pixelgrade_care' ), $content );
		// %pixelgrade_care_themes_url%
		$content = str_replace( '%pixelgrade_care_themes_url%', admin_url( 'admin.php?page=pixelgrade_themes' ), $content );

		// Allow others to alter the content after we did our work
		return apply_filters( 'pixcloud_notifications_after_parse_content_tags', $content, $original_content );
	}

	/**
	 * Process ajax call to dismiss notice.
	 *
	 * @since  1.9.0
	 */
	public function dismiss_notice() {

		if ( isset( $_POST['noticeID'] ) ) {

			$notice_id = sanitize_text_field( $_POST['noticeID'] );

			// Check nonce.
			check_ajax_referer( $this->manager_id . '_dismiss_admin_notice', 'nonce-pixcloud_anm-' . $notice_id );

			// Sanitize message ID after stripping off the '[manager_id]-'.
			$notice_id = str_replace( $this->manager_id . '-', '', sanitize_text_field( $_POST['noticeID'] ) );

			// Get notice info.
			if ( $user = wp_get_current_user() ) {

				// Get event if there was one.
				$event = isset( $_POST['pixcloud_anm-event'] ) ? sanitize_text_field( $_POST['pixcloud_anm-event'] ) : false;

				if ( ! empty( $this->notifications[ $notice_id ] ) && ! empty( $this->notifications[ $notice_id ]['dismissable'] ) ) {

					if ( ! empty( $this->notifications[ $notice_id ]['dismiss_for_all_users'] ) ) {
						// Dismiss for all users
						$this->dismiss_notice_for_all_users( $notice_id, $this->notifications[ $notice_id ], $event );
					} else {
						// Dismiss notice just for the current user.
						$this->dismiss_notice_for_user( $notice_id, $user->ID, $event );
					}

					// Redirect if this is not an ajax request.
					if ( isset( $_POST['pixcloud_anm-no-js'] ) ) {

						// If a redirect has been set, use it.
						if ( isset( $_POST['pixcloud_anm-redirect'] ) ) {
							if ( ! empty( $_POST['pixcloud_anm-redirect'] ) ) {
								wp_redirect( $_POST['pixcloud_anm-redirect'] );
								exit();
							}
						}

						// If not redirected, go back to where we came from.
						wp_safe_redirect( wp_get_referer() );
						exit();
					}
				}
			}
		}

		wp_die();

	}

	/**
	 * Add dismissal to all users for a notice.
	 *
	 * @access protected
	 * @since  1.9.0
	 *
	 * @param string       $notice_id Unique ID of message.
	 * @param array        $notice   The notice details.
	 * @param string|false $event
	 *
	 * @return bool $dismissed True if notice was dismissed successfully.
	 */
	protected function dismiss_notice_for_all_users( $notice_id, $notice, $event = false ) {

		$dismissed = true;

		// If user ids have been specified, check whether notice should be displayed to this user
		if ( ! empty( $notice['user_ids'] ) ) {
			// Convert roles to user ids
			$user_ids = $this->parse_user_roles( $notice['user_ids'] );
		} else {
			$user_ids = get_users( array( 'fields' => 'id' ) );
		}

		if ( ! empty( $user_ids ) && is_array( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				// We are only interested in failures. One failure is enough to return false overall.
				$dismissed = $this->dismiss_notice_for_user( $notice_id, $user_id, $event ) ? $dismissed : false;
			}
		}

		return $dismissed;
	}

	/**
	 * Add dismissal to a user for a notice.
	 *
	 * @access protected
	 * @since  1.9.0
	 *
	 * @param string       $notice_id Unique ID of message.
	 * @param int          $user_id   User id for whom message should be dismissed.
	 * @param string|false $event
	 *
	 * @return bool $dismissed True if notice was dismissed successfully.
	 */
	protected function dismiss_notice_for_user( $notice_id, $user_id, $event = false ) {

		$dismissed = false;
		if ( ! $notices = get_user_meta( $user_id, $this->manager_id . '_notifications', true ) ) {
			$notices = array();
		}

		if ( ! array_key_exists( $notice_id, $notices ) ) {
			$notices[ $notice_id ] = array(
				'dismissed' => true,
			);
			$dismissed = update_user_meta( $user_id, $this->manager_id . '_notifications', $notices );
		} elseif ( empty( $notices[ $notice_id ]['dismissed'] ) ) {
			$notices[ $notice_id ]['dismissed'] = true;
			$dismissed = update_user_meta( $user_id, $this->manager_id . '_notifications', $notices );
		}

		// Allow for other actions on dismissal of notice.
		if ( $dismissed ) {
			do_action( $this->manager_id . '_user_notice_dismissed_' . $notice_id, $user_id );
			if ( $event ) {
				do_action( $this->manager_id . '_user_notice_dismissed_' . $notice_id . '_' . $event, $user_id );
			}
		}

		return $dismissed;

	}

	/**
	 * Register display of a notice for a user.
	 *
	 * @access    protected
	 * @since  1.9.0
	 *
	 * @param string $notice_id Unique ID of notice.
	 * @param int    $user_id   User id for whom the notice should be registered as displayed.
	 *
	 * @return bool $displayed True if notice was registered as displayed successfully.
	 */
	protected function register_user_notice_display( $notice_id, $user_id ) {

		$registered = false;
		if ( ! $notices = get_user_meta( $user_id, $this->manager_id . '_notifications', true ) ) {
			$notices = array();
		}

		if ( ! array_key_exists( $notice_id, $notices ) ) {
			$notices[ $notice_id ] = array(
				'displayed' => true,
			);
			$registered = update_user_meta( $user_id, $this->manager_id . '_notifications', $notices );
		} elseif ( empty( $notices[ $notice_id ]['displayed'] ) ) {
			$notices[ $notice_id ]['displayed'] = true;
			$registered = update_user_meta( $user_id, $this->manager_id . '_notifications', $notices );
		}

		// Allow for other actions on register display of notice.
		if ( $registered ) {
			do_action( $this->manager_id . '_user_notice_displayed_' . $notice_id, $user_id );
		}

		return $registered;

	}

	/**
	 * Get all user ids with notifications data.
	 *
	 * @since  1.9.0
	 *
	 * @access    protected
	 * @return        array        Array of user ids.
	 */
	protected function get_users_with_notifications_data() {

		$args = array(
			'meta_query' => array(
				array(
					'key'     => $this->manager_id . '_notifications',
					'compare' => '!=',
					'value'   => '',
				),
			),
			'fields'     => 'ID',
		);

		return get_users( $args );

	}

	/**
	 * Remove notifications data from a user.
	 *
	 * @since  1.9.0
	 *
	 * @param   int $user_id User ID from whom to remove dismissals. Set to 0 for all users.
	 * @param   array $notice_ids Array of notice ids to remove
	 */
	public function remove_user_notifications_data( $user_id = 0, array $notice_ids ) {

		if ( ! empty( $notice_ids ) ) {

			if ( $user_notifications_details = get_user_meta( $user_id, $this->manager_id . '_notifications', true ) ) {

				$update = false;
				foreach ( $notice_ids as $notice_id ) {
					if ( ! empty( $user_notifications_details[ $notice_id ] ) ) {
						unset( $user_notifications_details[ $notice_id ] );
						$update = true;
					}
				}

				if ( $update ) {
					if ( empty( $user_notifications_details ) ) {
						delete_user_meta( $user_id, $this->manager_id . '_notifications' );
					} else {
						update_user_meta( $user_id, $this->manager_id . '_notifications', $user_notifications_details );
					}
					return;
				}
			}
		}

		delete_metadata( 'user', $user_id, $this->manager_id . '_notifications', false, true );
	}

	/**
	 * Remove all notifications data from a user.
	 *
	 * @since  1.9.0
	 *
	 * @param   int $user_id User ID from whom to remove dismissals. Set to 0 for all users.
	 */
	public function remove_user_data( $user_id = 0 ) {
		delete_metadata( 'user', $user_id, $this->manager_id . '_notifications', false, true );
	}

	/**
	 * Remove all data from the database. (Can be called on plugin uninstall, for example.)
	 *
	 * @since  1.9.0
	 */
	public function remove_all_data() {
		$this->remove_user_data();
	}

	public function add_data_to_request( $data ) {
		// Do nothing if data is already there.
		if ( isset( $data['notifications_data'] ) ) {
			return $data;
		}

		$notifications_data = array();

		$user_ids = $this->get_users_with_notifications_data();
		if ( ! empty( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				$user_notifications_data = get_user_meta( $user_id, $this->manager_id . '_notifications', true );
				if ( ! empty( $user_notifications_data ) ) {
					foreach ( $user_notifications_data as $id => $user_notification_data ) {
						// We will send data user agnostic.
						// We will rely on the fact that displayed and dismissed is boolean (so they can be translated to 0 and 1),
						// and simply do the sum of them.
						if ( ! isset( $notifications_data[ $id ] ) ) {
							$notifications_data[ $id ] = array(
								'seen_by_users' => 0,
								'dismissed_by_users' => 0,
							);
						}

						if ( ! empty( $user_notification_data['displayed'] ) ) {
							$notifications_data[ $id ]['seen_by_users'] ++;
						}

						if ( ! empty( $user_notification_data['dismissed'] ) ) {
							$notifications_data[ $id ]['dismissed_by_users'] ++;
						}
					}
				}
			}
		}

		if ( ! empty( $notifications_data ) ) {
			$data['notifications_data'] = $notifications_data;
		}

		return $data;
	}

	/**
	 * Main Pixcloud_Admin_Notifications_Manager Instance
	 *
	 * Ensures only one instance of Pixcloud_Admin_Notifications_Manager is loaded or can be loaded.
	 *
	 * @since  1.9.0
	 * @static
	 *
	 * @param array $args The arguments to initialize the notifications manager.
	 * @return Pixcloud_Admin_Notifications_Manager Main Pixcloud_Admin_Notifications_Manager instance
	 */
	public static function instance( $args = array() ) {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $args );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  null );
	} // End __wakeup ()

}