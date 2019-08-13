<?php
/**
 * This is the PHP file that allows you to play with the Customify config that would normally come from Pixelgrade Cloud.
 *
 * This is file is automatically detected by Customify and loaded when the CUSTOMIFY_SM_LOAD_THEME_ROOT_CONFIG constant is true.
 *
 * Keep the name of this file like you've received it from Pixelgrade Cloud.
 */

// This is where the final config should reside.
// Start with some sane default. We expect to have a sections entry.
$config = [
	'sections' => [],
];

define( 'SM_HEADINGS_FONT', 'Source Sans Pro' );
define( 'SM_SECONDARY_FONT', 'Source Sans Pro' );
define( 'SM_ACCENT_FONT', 'Source Sans Pro' );
define( 'SM_BODY_FONT', 'Source Sans Pro' );
define( 'SM_LOGO_FONT', 'Source Sans Pro' );

// Recommended Fonts List - Headings
$recommended_fonts = array(
	'Oswald',
	'Roboto',
	'Playfair Display',
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
);

// Body
$recommended_body_fonts = apply_filters(
	'customify_theme_recommended_body_fonts',
	array(
		'Roboto',
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
		'Pompiere',
	)
);

$config['sections'] = [

	/**
	 * Fonts - This section will handle all elements fonts (eg. links, headings)
	 */
	'fonts_section' => [
		'title'    => esc_html__( 'Fonts', 'customify' ),
		'priority' => 3, // This will put this section right after Colors section that has a priority of 2.
		'options'  => [
			/**
			 * Header Section
			 */
			'header_section'                               => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Header', 'customify' ) . '</span>',
			],
			'header_site_title_font'         => array(
				'type'        => 'font',
				'label'       => esc_html__( 'Site Title Font', 'customify' ),
				'selector'    => '.page-template-template-homepage .entry-header h1',
				'callback'    => 'typeline_font_cb',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '700',
					'font-size'      => 32,
					'line-height'    => 1.214,
					'letter-spacing' => -0.02,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			),
			'header_navigation_font'         => array(
				'type'        => 'font',
				'label'       => esc_html__( 'Navigation Text', 'customify' ),
				'selector'    => '.storefront-primary-navigation',
				'callback'    => 'typeline_font_cb',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '400',
					'font-size'      => 16,
					'line-height'    => 1.618,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			),

			/**
			 * Main Content Section
			 */
			'main_content'                               => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Main Content', 'customify' ) . '</span>',
			],
			'main_content_page_title_font'         => array(
				'type'        => 'font',
				'label'       => esc_html__( 'Page Title Font', 'customify' ),
				'selector'    => '.page-template-template-homepage .entry-header h1',
				'callback'    => 'typeline_font_cb',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '300',
					'font-size'      => 59,
					'line-height'    => 1.214,
					'letter-spacing' => -0.02,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			),
			'main_content_body_text_font'          => array(
				'type'        => 'font',
				'label'       => esc_html__( 'Body Text Font', 'customify' ),
				'selector'    => 'body',
				'callback'    => 'typeline_body_font_cb',

				'default' => array(
					'font-family'    => SM_BODY_FONT,
					'font-weight'    => '400',
					'font-size'      => 16,
					'line-height'    => 1.618,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			),
			'main_content_quote_block_font'         => array(
				'type'        => 'font',
				'label'       => esc_html__( 'Quote Block Font', 'customify' ),
				'selector'    => 'blockquote',
				'callback'    => 'typeline_font_cb',
				'default'  => array(
					'font-family'    => SM_ACCENT_FONT,
					'font-weight'    => 'italic',
					'font-size'      => 18,
					'line-height'    => 1.67,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			),

			'main_content_heading_1_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Heading 1', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => '.alpha, h1',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '300',
					'font-size'      => 42,
					'line-height'    => 1.214,
					'letter-spacing' => -0.02,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],

			'main_content_heading_2_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Heading 2', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => '.beta, h2',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '300',
					'font-size'      => 32,
					'line-height'    => 1.214,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],

			'main_content_heading_4_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Heading 4', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => '.delta, h4',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '300',
					'font-size'      => 23,
					'line-height'    => 1.6,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],

			'main_content_heading_5_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Heading 5', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => 'h5',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '300',
					'font-size'      => 14,
					'line-height'    => 1.6,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],

			'main_content_heading_6_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Heading 6', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => 'h6',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '300',
					'font-size'      => 11,
					'line-height'    => 1.6,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],

			'main_content_heading_3_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Heading 3', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => '.gamma, h3',

				'default' => array(
					'font-family'    => SM_HEADINGS_FONT,
					'font-weight'    => '300',
					'font-size'      => 26,
					'line-height'    => 1.6,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],

			/**
			 * Buttons Section
			 */
			'buttons_content'                               => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Buttons', 'customify' ) . '</span>',
			],
			'buttons_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Buttons', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => '.added_to_cart, .button, button, input[type=button], input[type=reset], input[type=submit]',

				'default' => array(
					'font-family'    => SM_SECONDARY_FONT,
					'font-weight'    => '600',
					'font-size'      => 14,
					'line-height'    => 1.618,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],


			/**
			 * Cards Section
			 */
			'cards_content'                               => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Cards', 'customify' ) . '</span>',
			],

			'cards_price_font' => [
				'type'     			=> 'font',
				'label'            => esc_html__( 'Price', 'customify' ),
				'desc'             => esc_html__( '', 'customify' ),
				'selector'         => 'ul.products li.product .price',

				'default' => array(
					'font-family'    => SM_BODY_FONT,
					'font-weight'    => '400',
					'font-size'      => 14,
					'line-height'    => 1.7,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),

				// Sub Fields Configuration
				'fields'      => array(
					'font-size'       => array(
						'min'  => 8,
						'max'  => 90,
						'step' => 1,
						'unit' => 'px',
					),
					'line-height'     => array( 0, 2, 0.1, '' ),
					'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
					'text-align'      => false,
					'text-transform'  => true,
					'text-decoration' => false,
				),
			],

		],
	],

	/**
	 * COLORS - This section will handle all elements colors (eg. links, headings)
	 */
	'colors_section' => [
		'title'    => esc_html__( 'Colors', 'customify' ),
		'priority' => 3, // This will put this section right after Style Manager that has a priority of 1.
		'options'  => [
			/**
			 * Header Section
			 */
			'header_section'                               => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Header', 'storefront' ) . '</span>',
			],
			'header_navigation_text_color'                 => [
				'type'    => 'color',
				'label'   => esc_html__( 'Header Text Color', 'customify' ),
				'live'    => true,
				'default' => '#404040',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'p.site-description,
							.site-header,
							.storefront-handheld-footer-bar',
					],
					[
						'media'           => 'screen and ( min-width: 768px )',
						'property'        => 'color',
						'selector'        => '.secondary-navigation ul.menu a:hover',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 25,
							],
						],
					],
					[
						'media'    => 'screen and ( min-width: 768px )',
						'property' => 'color',
						'selector' => '.secondary-navigation ul.menu a,
							.site-header-cart .widget_shopping_cart,
							.site-header .product_list_widget li .quantity',
					],
				],
			],
			'header_navigation_links_color'                => [
				'type'    => 'color',
				'label'   => esc_html__( 'Navigation Links Color', 'customify' ),
				'live'    => true,
				'default' => '#333333',
				'css'     => [
					[
						'property' => 'color',
						'selector' => '.main-navigation ul li a,
							.site-title a,
							ul.menu li a,
							.site-branding h1 a,
							.site-footer .storefront-handheld-footer-bar a:not(.button],
							button.menu-toggle,
							button.menu-toggle:hover,

							.storefront-sticky-add-to-cart a:not(.button],
							a.cart-contents,
							.site-header-cart .widget_shopping_cart a',
					],
					[
						'property' => 'border-color',
						'selector' => 'button.menu-toggle,
							button.menu-toggle:hover',
					],
					[
						'property' => 'background-color',
						'selector' => '.storefront-handheld-footer-bar ul li.cart .count,
							button.menu-toggle:after,
							button.menu-toggle:before,
							button.menu-toggle span:before',
					],
				],
			],
			'header_navigation_links_active_color'         => [
				'type'    => 'color',
				'label'   => esc_html__( 'Links Active Color', 'customify' ),
				'live'    => true,
				'default' => '#282828',
				'css'     => [
					[
						'property' => 'color',
						'selector' => '.main-navigation ul li a:hover,
						.main-navigation ul li:hover > a,
						.site-title a:hover,
						a.cart-contents:hover,
						.site-header-cart .widget_shopping_cart a:hover,
						.site-header-cart:hover > li > a,
						.site-header ul.menu li.current-menu-item > a',
					],
				],
			],
			'header_background_color'                      => [
				'type'    => 'color',
				'label'   => esc_html__( 'Header Background', 'customify' ),
				'live'    => true,
				'default' => '#ffffff',
				'css'     => [
					[
						'property' => 'background-color',
						'selector' => '.site-header,
							.secondary-navigation ul ul,
							.main-navigation ul.menu > li.menu-item-has-children:after,
							.secondary-navigation ul.menu ul,
							.storefront-handheld-footer-bar,
							.storefront-handheld-footer-bar ul li > a,
							.storefront-handheld-footer-bar ul li.search .site-search,
							button.menu-toggle,
							button.menu-toggle:hover',
					],
					[
						'property' => 'color',
						'selector' => '.storefront-handheld-footer-bar ul li.cart .count',
					],
					[
						'property' => 'border-color',
						'selector' => '.storefront-handheld-footer-bar ul li.cart .count',
					],
					[
						'media'           => 'screen and ( min-width: 768px )',
						'property'        => 'background-color',
						'selector'        => '.site-header-cart .widget_shopping_cart,
							.main-navigation ul.menu ul.sub-menu,
							.main-navigation ul.nav-menu ul.children',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 15,
							],
						],
					],
					[
						'media'           => 'screen and ( min-width: 768px )',
						'property'        => 'background-color',
						'selector'        => '.site-header-cart .widget_shopping_cart .buttons,
							.site-header-cart .widget_shopping_cart .total',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 10,
							],
						],
					],
					[
						'media'           => 'screen and ( min-width: 768px )',
						'property'        => 'border-bottom-color',
						'selector'        => '.site-header',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 15,
							],
						],
					],
				],
			],


			/**
			 * Main Content
			 */
			'main_content_section'                         => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Main Content', 'storefront' ) . '</span>',
			],
			'page_title_color'                             => [
				'type'    => 'color',
				'label'   => esc_html__( 'Page Title Color', 'customify' ),
				'live'    => true,
				'default' => '#282828',
				'css'     => [
					[
						'property' => 'color',
						'selector' => '.entry-title,
							.page-template-template-homepage.has-post-thumbnail .type-page.has-post-thumbnail .entry-title',
					],
				],
			],
			'body_text_color'                              => [
				'type'    => 'color',
				'label'   => esc_html__( 'Body Text Color', 'customify' ),
				'live'    => true,
				'default' => '#6d6d6d',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'button,input,textarea,
						.input-text,input[type=email],input[type=password],input[type=search],input[type=text],input[type=url],textarea,
						.pagination .page-numbers li .page-numbers,.woocommerce-pagination .page-numbers li .page-numbers,
						ul.menu li.current-menu-item>a,

						body,
						.secondary-navigation a,
						.onsale,
						.pagination .page-numbers li .page-numbers:not(.current], .woocommerce-pagination .page-numbers li .page-numbers:not(.current)

						.page-template-template-homepage.has-post-thumbnail .type-page.has-post-thumbnail .entry-content,

						p.stars a:before,
						p.stars a:hover~a:before,
						p.stars.selected a.active~a:before,

						.storefront-product-pagination a,
						.storefront-sticky-add-to-cart,

						.woocommerce-tabs ul.tabs li.active a,
						ul.products li.product .price,
						.onsale,
						.widget_search form:before,
						.widget_product_search form:before,

						mark',
					],
					[
						'property'        => 'color',
						'selector'        => '.widget-area .widget a,
							.hentry .entry-header .posted-on a,
							.hentry .entry-header .byline a,

							.woocommerce-breadcrumb a,
							a.woocommerce-review-link,
							.product_meta a',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								5,
							],
						],
					],
					[
						'property'        => 'color',
						'selector'        => '.pagination .page-numbers li .page-numbers.current, .woocommerce-pagination .page-numbers li .page-numbers.current',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								10,
							],
						],
					],
					[
						'property' => 'border-color',
						'selector' => '.onsale',
					],
				],
			],
			'body_link_color'                              => [
				'type'    => 'color',
				'label'   => esc_html__( 'Body Link Color', 'customify' ),
				'live'    => true,
				'default' => '#96588A',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'a,
							.star-rating span:before,
							.quantity .plus, .quantity .minus,
							p.stars a:hover:after,
							p.stars a:after,
							.star-rating span:before,
							#payment .payment_methods li input[type=radio]:first-child:checked+label:before,

							p.stars.selected a.active:before,
							p.stars:hover a:before,
							p.stars.selected a:not(.active):before,
							p.stars.selected a.active:before',
					],
					[
						'property' => 'outline-color',
						'selector' => 'a:focus,
							.button:focus,
							.button.alt:focus,
							.button.added_to_cart:focus,
							.button.wc-forward:focus,
							button:focus,
							input[type="button"]:focus,
							input[type="reset"]:focus,
							input[type="submit"]:focus',
					],
					[
						'property' => 'background-color',
						'selector' => '.widget_price_filter .ui-slider .ui-slider-range,
							.widget_price_filter .ui-slider .ui-slider-handle',
					],
				],
			],
			'body_link_active_color'                       => [
				'type'    => 'color',
				'label'   => esc_html__( 'Body Link Active Color', 'customify' ),
				'live'    => true,
				'default' => '#282828',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'a:hover',
					],
				],
			],

			// [Sub Section] Headings Colors
			'main_content_title_headings_color_section'    => [
				'type' => 'html',
				'html' => '<span class="separator sub-section label">' . esc_html__( 'Headings Color', 'patch' ) . '</span>',
			],
			'main_content_heading_1_color'                 => [
				'type'    => 'color',
				'label'   => esc_html__( 'Heading 1', 'patch' ),
				'live'    => true,
				'default' => '#131315',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'h1, .site-title a',
					],
				],
			],
			'main_content_heading_2_color'                 => [
				'type'    => 'color',
				'label'   => esc_html__( 'Heading 2', 'patch' ),
				'live'    => true,
				'default' => '#131315',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'h2, blockquote',
					],
				],
			],
			'main_content_heading_3_color'                 => [
				'type'    => 'color',
				'label'   => esc_html__( 'Heading 3', 'patch' ),
				'live'    => true,
				'default' => '#131315',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'h3',
					],
				],
			],
			'main_content_heading_4_color'                 => [
				'type'    => 'color',
				'label'   => esc_html__( 'Heading 4', 'patch' ),
				'live'    => true,
				'default' => '#131315',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'h4',
					],
				],
			],
			'main_content_heading_5_color'                 => [
				'type'    => 'color',
				'label'   => esc_html__( 'Heading 5', 'patch' ),
				'live'    => true,
				'default' => '#131315',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'h5',
					],
				],
			],
			'main_content_heading_6_color'                 => [
				'type'    => 'color',
				'label'   => esc_html__( 'Heading 6', 'patch' ),
				'live'    => true,
				'default' => '#131315',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'h6',
					],
				],
			],

			// [Sub Section] Backgrounds
			'main_content_title_backgrounds_color_section' => [
				'type' => 'html',
				'html' => '<span class="separator sub-section label">' . esc_html__( 'Backgrounds', 'patch' ) . '</span>',
			],

			'main_content_content_background_color' => [
				'type'    => 'color',
				'label'   => esc_html__( 'Content Background Color', 'patch' ),
				'live'    => true,
				'default' => '#ffffff',
				'css'     => [
					[
						'property' => 'background-color',
						'selector' => 'body,
							#order_review,
							.storefront-product-pagination a,
							.storefront-sticky-add-to-cart',
					],
					[
						'property'        => 'background-color',
						'selector'        => 'table th,
							#comments .comment-list .comment-content .comment-text,
							.order_details',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 7,
							],
						],
					],
					[
						'property'        => 'background-color',
						'selector'        => 'table tbody td ',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 2,
							],
						],
					],
					[
						'property'        => 'background-color',
						'selector'        => 'table tbody tr:nth-child(2n) td,
							fieldset,
							fieldset legend',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 4,
							],
						],
					],
					[
						'property'        => 'background-color',
						'selector'        => '#payment .payment_methods > li .payment_box,
							#payment .place-order,

							.input-text, input[type=email], input[type=password], input[type=search], input[type=text], input[type=url], textarea',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 5,
							],
						],
					],
					[
						'property'        => 'background-color',
						'selector'        => '#payment .payment_methods > li:not(.woocommerce-notice)',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 10,
							],
						],
					],
					[
						'property'        => 'background-color',
						'selector'        => '#payment .payment_methods > li:not(.woocommerce-notice):hover',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 15,
							],
						],
					],
					[
						'property'        => 'background-color',
						'selector'        => '.pagination .page-numbers li .page-numbers.current, .woocommerce-pagination .page-numbers li .page-numbers.current',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 25,
							],
						],
					],
					[
						'property'        => 'border-bottom-color',
						'selector'        => '.order_details > li',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 28,
							],
						],
					],
					[
						'property' => 'border-top-color',
						'selector' => 'table.cart td.product-remove,
							table.cart td.actions',
					],
					[
						'property' => 'background',
						'selector' => '.order_details:before,
							.order_details:after',
						// 'callback_filter' => 'gradient()'
					],
					[
						'property' => 'color',
						'selector' => '.woocommerce-info, .woocommerce-message, .woocommerce-noreviews, p.no-comments,

							.woocommerce-info a ,.woocommerce-message a, .woocommerce-noreviews a, p.no-comments a,
							.woocommerce-info a:hover, .woocommerce-message a:hover, .woocommerce-noreviews a:hover, p.no-comments a:hover',
					],
				],
			],


			/**
			 * Buttons
			 */
			'buttons_section'                       => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Buttons', 'storefront' ) . '</span>',
			],
			'buttons_text_color'                    => [
				'type'    => 'color',
				'label'   => esc_html__( 'Text Color', 'customify' ),
				'live'    => true,
				'default' => '#333333',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'button, input[type="button"], input[type="reset"], input[type="submit"], .button, .added_to_cart, .widget a.button, .site-header-cart .widget_shopping_cart a.button,

						button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .added_to_cart:hover, .widget a.button:hover, .site-header-cart .widget_shopping_cart a.button:hover,

						.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger,

						.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger:hover,

						.button.loading:after ',
					],
				],
			],
			'buttons_background_color'              => [
				'type'    => 'color',
				'label'   => esc_html__( 'Background Color', 'customify' ),
				'live'    => true,
				'default' => '#eeeeee',
				'css'     => [
					[
						'property' => 'background-color',
						'selector' => 'button, input[type="button"], input[type="reset"], input[type="submit"], .button, .added_to_cart, .widget a.button, .site-header-cart .widget_shopping_cart a.button,

						.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger',
					],
					[
						'property' => 'border-color',
						'selector' => 'button, input[type="button"], input[type="reset"], input[type="submit"], .button, .added_to_cart, .widget a.button, .site-header-cart .widget_shopping_cart a.button',
					],
					// Hover
					[
						'property'        => 'background-color',
						'selector'        => 'button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .added_to_cart:hover, .widget a.button:hover, .site-header-cart .widget_shopping_cart a.button:hover,

						.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger:hover',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 25,
							],
						],
					],
					[
						'property'        => 'border-color',
						'selector'        => 'button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .added_to_cart:hover, .widget a.button:hover, .site-header-cart .widget_shopping_cart a.button:hover,

						.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger:hover',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 25,
							],
						],
					],
				],
			],
			'buttons_alt_text_color'                => [
				'type'    => 'color',
				'label'   => esc_html__( 'Alternate Text Color', 'customify' ),
				'live'    => true,
				'default' => '#ffffff',
				'css'     => [
					[
						'property' => 'color',
						'selector' => 'button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .added_to_cart.alt, .widget-area .widget a.button.alt, .added_to_cart, .widget a.button.checkout',
					],
				],
			],
			'buttons_alt_background_color'          => [
				'type'    => 'color',
				'label'   => esc_html__( 'Alternate Background Color', 'customify' ),
				'live'    => true,
				'default' => '#333333',
				'css'     => [
					[
						'property' => 'background-color',
						'selector' => 'button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .added_to_cart.alt, .widget-area .widget a.button.alt, .added_to_cart, .widget a.button.checkout',
					],
					[
						'property' => 'border-color',
						'selector' => 'button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .added_to_cart.alt, .widget-area .widget a.button.alt, .added_to_cart, .widget a.button.checkout',
					],
					// Hover
					[
						'property'        => 'background-color',
						'selector'        => 'button.alt:hover, input[type="button"].alt:hover, input[type="reset"].alt:hover, input[type="submit"].alt:hover, .button.alt:hover, .added_to_cart.alt:hover, .widget-area .widget a.button.alt:hover, .added_to_cart:hover, .widget a.button.checkout:hover',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 25,
							],
						],
					],
					[
						'property'        => 'border-color',
						'selector'        => 'button.alt:hover, input[type="button"].alt:hover, input[type="reset"].alt:hover, input[type="submit"].alt:hover, .button.alt:hover, .added_to_cart.alt:hover, .widget-area .widget a.button.alt:hover, .added_to_cart:hover, .widget a.button.checkout:hover',
						'filter_value_cb' => [
							'callback' => 'pixcloud_adjust_color_brightness',
							'args'     => [
								- 25,
							],
						],
					],
				],
			],


			/**
			 * Footer
			 */
			'footer_section'                        => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Footer', 'storefront' ) . '</span>',
			],
			'footer_text_color'                     => [
				'type'    => 'color',
				'label'   => esc_html__( 'Footer Text Color', 'customify' ),
				'live'    => true,
				'default' => '#6d6d6d',
				'css'     => [
					[
						'property' => 'color',
						'selector' => '.site-footer',
					],
				],
			],
			'footer_links_color'                    => [
				'type'    => 'color',
				'label'   => esc_html__( 'Footer Links Color', 'customify' ),
				'live'    => true,
				'default' => '#333333',
				'css'     => [
					[
						'property' => 'color',
						'selector' => '.site-footer a:not(.button)',
					],
				],
			],
			'footer_heading_color'                  => [
				'type'    => 'color',
				'label'   => esc_html__( 'Footer Headings Color', 'customify' ),
				'live'    => true,
				'default' => '#333333',
				'css'     => [
					[
						'property' => 'color',
						'selector' => '.site-footer h1, .site-footer h2, .site-footer h3, .site-footer h4, .site-footer h5, .site-footer h6',
					],
				],
			],
			'footer_background_color'               => [
				'type'    => 'color',
				'label'   => esc_html__( 'Footer Background', 'customify' ),
				'live'    => true,
				'default' => '#f0f0f0',
				'css'     => [
					[
						'property' => 'background-color',
						'selector' => '.site-footer',
					],
				],
			],

			/**
			 * Miscellaneous
			 */
			'misc_section'                          => [
				'type' => 'html',
				'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Miscellaneous', 'storefront' ) . '</span>',
			],

			'woocommerce_info_background_color' => [
				'type'    => 'background-color',
				'label'   => esc_html__( 'WooCommerce Info', 'customify' ),
				'live'    => true,
				'default' => '#3d9cd2',
				'css'     => [
					[
						'property' => 'background-color',
						'selector' => '.woocommerce-info, .woocommerce-message, .woocommerce-noreviews, p.no-comments',
					],
				],
			],
		],
	],
];

/**
 * Add the Style Manager cross-theme Customizer section.
 */
$config['sections']['style_manager_section'] = [
	'options' => [
		// Colors connected fields.
		'sm_color_primary'   => [
			'connected_fields' => [
				'body_link_color',
				'main_content_heading_4_color',
				'main_content_heading_5_color',
				'header_navigation_links_active_color',
				'woocommerce_info_background_color',
			],
		],
		'sm_color_secondary' => [
			'connected_fields' => [
				'main_content_heading_6_color',
				'buttons_alt_background_color',
			],
		],

		'sm_dark_primary'    => [
			'connected_fields' => [
				'body_link_active_color',
				'page_title_color',
				'main_content_page_title_color',
				'main_content_heading_1_color',
				'main_content_heading_2_color',
				'main_content_heading_3_color',
				'header_navigation_links_color',
				'footer_links_color',
				'footer_heading_color',

				'buttons_text_color',
			],
		],
		'sm_dark_secondary'  => [
			'connected_fields' => [
				'body_text_color',
				'header_navigation_text_color',
				'footer_text_color',
			],
		],
		'sm_light_primary'   => [
			'connected_fields' => [
				'main_content_content_background_color',
				'buttons_alt_text_color',
			],
		],
		'sm_light_secondary' => [
			'connected_fields' => [
				'header_background_color',
				'buttons_background_color',
				'footer_background_color',
			],
		],
		'sm_light_tertiary'  => [
			'connected_fields' => [],
		],

		// Fonts connected fields.
		'sm_font_primary'  => [
			'connected_fields' => [
				'main_content_page_title_font',
				'main_content_quote_block_font',
				'main_content_heading_1_font',
				'main_content_heading_2_font',
				'main_content_heading_3_font',
			],
		],
		'sm_font_secondary'  => [
			'connected_fields' => [
				'main_content_heading_4_font',
				'main_content_heading_5_font',
				'main_content_heading_6_font',
				'buttons_font',
				'header_navigation_font'
			],
		],
		'sm_font_body'  => [
			'connected_fields' => [
				'main_content_body_text_font',
				'cards_price_font'
			],
		],
	],
];

/**
 * Add "instructions" to remove panels, sections, controls and/or settings.
 */

$config['remove_sections'] = [
	'storefront_footer',
	'storefront_typography',
	'storefront_buttons',
];

$config['remove_controls'] = [
	'storefront_header_background_color',
	'storefront_header_text_color',
	'storefront_header_link_color',
	'background_color',
];

// You don't need to return anything.
