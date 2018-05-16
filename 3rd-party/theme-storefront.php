<?php 

// ToDo: Add it to theme function.php

// Theme Name:   Storefront
// Theme URI:    https://woocommerce.com/storefront/
// Author:       Automattic
// Version:      2.3.1

/**
 * Enable support for the Style Manager Customizer section (via Customify).
 */
add_theme_support( 'customizer_style_manager' );

add_filter( 'customify_filter_fields', 'storefront_add_customify_options', 11, 1 );
add_filter( 'customify_filter_fields', 'pixelgrade_add_customify_style_manager_section', 12, 1 );

function storefront_add_customify_options( $options ) {
	$brighten_factor       = 25;
	$darken_factor         = -25;


	$options['sections'] = array(

		/**
		 * COLORS - This section will handle all elements colors (eg. links, headings)
		 */
		'colors_section' => array(
			'title'    => __( 'Colors', 'customify' ),
			'options' => array(
				/**
				 * Header Section
				 */
				'header_section' => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Header', 'storefront' ) . '</span>',
				),
				'header_navigation_text_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Header Text Color', 'customify' ),
					'live' => true,
					'default'   => '#404040',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => 'p.site-description,
								.site-header,
								.storefront-handheld-footer-bar',
						),
						array(
							'media' => 'screen and ( min-width: 768px )',
							'property'     => 'color',
							'selector' => '.secondary-navigation ul.menu a:hover',
							// 'callback_filter_value' => 'adjust_color_brightness($brighten_factor)'
						),
						array(
							'media' => 'screen and ( min-width: 768px )',
							'property'     => 'color',
							'selector' => '.secondary-navigation ul.menu a,
								.site-header-cart .widget_shopping_cart,
								.site-header .product_list_widget li .quantity',
						),
					)
				),
				'header_navigation_links_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Navigation Links Color', 'customify' ),
					'live' => true,
					'default'   => '#333333',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => '.main-navigation ul li a,
								.site-title a,
								ul.menu li a,
								.site-branding h1 a,
								.site-footer .storefront-handheld-footer-bar a:not(.button),
								button.menu-toggle,
								button.menu-toggle:hover,

								.storefront-sticky-add-to-cart a:not(.button),
								a.cart-contents,
								.site-header-cart .widget_shopping_cart a',
						),
						array(
							'property'     => 'border-color',
							'selector' => 'button.menu-toggle,
								button.menu-toggle:hover',
						),
						array(
							'property'     => 'background-color',
							'selector' => '.storefront-handheld-footer-bar ul li.cart .count,
								button.menu-toggle:after,
								button.menu-toggle:before,
								button.menu-toggle span:before',
						),
					)
				),
				'header_navigation_links_active_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Links Active Color', 'customify' ),
					'live' => true,
					'default'   => '#282828',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => '.main-navigation ul li a:hover,
							.main-navigation ul li:hover > a,
							.site-title a:hover,
							a.cart-contents:hover,
							.site-header-cart .widget_shopping_cart a:hover,
							.site-header-cart:hover > li > a,
							.site-header ul.menu li.current-menu-item > a',
						),
					)
				),
				'header_background_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Header Background', 'customify' ),
					'live' => true,
					'default'   => '#ffffff',
					'css'  => array(
						array(
							'property'     => 'background-color',
							'selector' => '.site-header,
								.secondary-navigation ul ul,
								.main-navigation ul.menu > li.menu-item-has-children:after,
								.secondary-navigation ul.menu ul,
								.storefront-handheld-footer-bar,
								.storefront-handheld-footer-bar ul li > a,
								.storefront-handheld-footer-bar ul li.search .site-search,
								button.menu-toggle,
								button.menu-toggle:hover',
						),
						array(
							'property'     => 'color',
							'selector' => '.storefront-handheld-footer-bar ul li.cart .count',
						),
						array(
							'property'     => 'border-color',
							'selector' => '.storefront-handheld-footer-bar ul li.cart .count',
						),
						array(
							'media' => 'screen and ( min-width: 768px )',
							'property'     => 'background-color',
							'selector' => '.site-header-cart .widget_shopping_cart,
								.main-navigation ul.menu ul.sub-menu,
								.main-navigation ul.nav-menu ul.children',
							// 'callback_filter_value' => 'adjust_color_brightness(-15)'
						),
						array(
							'media' => 'screen and ( min-width: 768px )',
							'property'     => 'background-color',
							'selector' => '.site-header-cart .widget_shopping_cart .buttons,
								.site-header-cart .widget_shopping_cart .total',
							// 'callback_filter_value' => 'adjust_color_brightness(-10)'
						),
						array(
							'media' => 'screen and ( min-width: 768px )',
							'property'     => 'border-bottom-color',
							'selector' => '.site-header',
							// 'callback_filter_value' => 'adjust_color_brightness(-15)'
						),
					)
				),



				/**
				 * Main Content
				 */
				'main_content_section' => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Main Content', 'storefront' ) . '</span>',
				),
				'page_title_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Page Title Color', 'customify' ),
					'live' => true,
					'default'   => '#282828',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => '.entry-title,
								.page-template-template-homepage.has-post-thumbnail .type-page.has-post-thumbnail .entry-title',
						),
					)
				),
				'body_text_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Body Text Color', 'customify' ),
					'live' => true,
					'default'   => '#6d6d6d',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => 'button,input,textarea,
							.input-text,input[type=email],input[type=password],input[type=search],input[type=text],input[type=url],textarea,
							.pagination .page-numbers li .page-numbers,.woocommerce-pagination .page-numbers li .page-numbers,
							ul.menu li.current-menu-item>a,

							body,
							.secondary-navigation a,
							.onsale,
							.pagination .page-numbers li .page-numbers:not(.current), .woocommerce-pagination .page-numbers li .page-numbers:not(.current)

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
						),
						array(
							'property'     => 'color',
							'selector' => '.widget-area .widget a,
								.hentry .entry-header .posted-on a,
								.hentry .entry-header .byline a,

								.woocommerce-breadcrumb a,
								a.woocommerce-review-link,
								.product_meta a',
							// 'callback_filter_value' => 'adjust_color_brightness(5)'
						),
						array(
							'property'     => 'color',
							'selector' => '.pagination .page-numbers li .page-numbers.current, .woocommerce-pagination .page-numbers li .page-numbers.current',
							// 'callback_filter_value' => 'adjust_color_brightness(10)'
						),
						array(
							'property'     => 'border-color',
							'selector' => '.onsale',
						),
					)
				),
				'body_link_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Body Link Color', 'customify' ),
					'live' => true,
					'default'   => '#96588A',
					'css'  => array(
						array(
							'property'     => 'color',
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
						),
						array(
							'property'     => 'outline-color',
							'selector' => 'a:focus,
								.button:focus,
								.button.alt:focus,
								.button.added_to_cart:focus,
								.button.wc-forward:focus,
								button:focus,
								input[type="button"]:focus,
								input[type="reset"]:focus,
								input[type="submit"]:focus',
						),
						array(
							'property'     => 'background-color',
							'selector' => '.widget_price_filter .ui-slider .ui-slider-range,
								.widget_price_filter .ui-slider .ui-slider-handle',
						),
					)
				),
				'body_link_active_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Body Link Active Color', 'customify' ),
					'live' => true,
					'default'   => '#282828',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => 'a:hover',
						),
					)
				),

				// [Sub Section] Headings Colors
				'main_content_title_headings_color_section'              => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Headings Color', 'patch' ) . '</span>',
				),
				'main_content_heading_1_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 1', 'patch' ),
					'live'    => true,
					'default' => '#131315',
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h1, .site-title a',
						),
					),
				),
				'main_content_heading_2_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 2', 'patch' ),
					'live'    => true,
					'default' => '#131315',
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h2, blockquote',
						),
					),
				),
				'main_content_heading_3_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 3', 'patch' ),
					'live'    => true,
					'default' => '#131315',
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h3'
						),
					),
				),
				'main_content_heading_4_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 4', 'patch' ),
					'live'    => true,
					'default' => '#131315',
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h4',
						),
					),
				),
				'main_content_heading_5_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 5', 'patch' ),
					'live'    => true,
					'default' => '#131315',
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h5',
						),
					),
				),
				'main_content_heading_6_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 6', 'patch' ),
					'live'    => true,
					'default' => '#131315',
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h6',
						),
					),
				),

				// [Sub Section] Backgrounds
				'main_content_title_backgrounds_color_section'              => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Backgrounds', 'patch' ) . '</span>',
				),

				'main_content_content_background_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Content Background Color', 'patch' ),
					'live'    => true,
					'default' => '#ffffff',
					'css'     => array(
						array(
							'property' => 'background-color',
							'selector' => 'body,
								#order_review,
								.storefront-product-pagination a,
								.storefront-sticky-add-to-cart',
						),
						array(
							'property' => 'background-color',
							'selector' => 'table th,
								#comments .comment-list .comment-content .comment-text,
								.order_details',
							// 'callback_filter_value' => 'adjust_color_brightness(-7)'
						),
						array(
							'property' => 'background-color',
							'selector' => 'table tbody td ',
							// 'callback_filter_value' => 'adjust_color_brightness(-2)'
						),
						array(
							'property' => 'background-color',
							'selector' => 'table tbody tr:nth-child(2n) td,
								fieldset,
								fieldset legend',
							// 'callback_filter_value' => 'adjust_color_brightness(-4)'
						),
						array(
							'property' => 'background-color',
							'selector' => '#payment .payment_methods > li .payment_box,
								#payment .place-order,

								.input-text, input[type=email], input[type=password], input[type=search], input[type=text], input[type=url], textarea',
							// 'callback_filter_value' => 'adjust_color_brightness(-5)'
						),
						array(
							'property' => 'background-color',
							'selector' => '#payment .payment_methods > li:not(.woocommerce-notice)',
							// 'callback_filter_value' => 'adjust_color_brightness(-10)'
						),
						array(
							'property' => 'background-color',
							'selector' => '#payment .payment_methods > li:not(.woocommerce-notice):hover',
							// 'callback_filter_value' => 'adjust_color_brightness(-15)'
						),
						array(
							'property' => 'background-color',
							'selector' => '.pagination .page-numbers li .page-numbers.current, .woocommerce-pagination .page-numbers li .page-numbers.current',
							// 'callback_filter_value' => 'adjust_color_brightness($darken_factor)'
						),
						array(
							'property' => 'border-bottom-color',
							'selector' => '.order_details > li',
							// 'callback_filter_value' => 'adjust_color_brightness(-28)'
						),
						array(
							'property' => 'border-top-color',
							'selector' => 'table.cart td.product-remove,
								table.cart td.actions',
						),
						array(
							'property' => 'background',
							'selector' => '.order_details:before,
								.order_details:after',
							// 'callback_filter' => 'gradient()'
						),
						array(
							'property' => 'color',
							'selector' => '.woocommerce-info, .woocommerce-message, .woocommerce-noreviews, p.no-comments,

								.woocommerce-info a ,.woocommerce-message a, .woocommerce-noreviews a, p.no-comments a,
								.woocommerce-info a:hover, .woocommerce-message a:hover, .woocommerce-noreviews a:hover, p.no-comments a:hover',
						),
					),
				),



				/**
				 * Buttons
				 */
				'buttons_section' => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Buttons', 'storefront' ) . '</span>',
				),
				'buttons_text_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Text Color', 'customify' ),
					'live' => true,
					'default'   => '#333333',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => 'button, input[type="button"], input[type="reset"], input[type="submit"], .button, .added_to_cart, .widget a.button, .site-header-cart .widget_shopping_cart a.button,

							button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .added_to_cart:hover, .widget a.button:hover, .site-header-cart .widget_shopping_cart a.button:hover,

							.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger,

							.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger:hover,

							.button.loading:after ',
						),
					)
				),
				'buttons_background_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Background Color', 'customify' ),
					'live' => true,
					'default'   => '#eeeeee',
					'css'  => array(
						array(
							'property'     => 'background-color',
							'selector' => 'button, input[type="button"], input[type="reset"], input[type="submit"], .button, .added_to_cart, .widget a.button, .site-header-cart .widget_shopping_cart a.button,

							.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger',
						),
						array(
							'property'     => 'border-color',
							'selector' => 'button, input[type="button"], input[type="reset"], input[type="submit"], .button, .added_to_cart, .widget a.button, .site-header-cart .widget_shopping_cart a.button',
						),
						// Hover
						array(
							'property'     => 'background-color',
							'selector' => 'button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .added_to_cart:hover, .widget a.button:hover, .site-header-cart .widget_shopping_cart a.button:hover,

							.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger:hover',
							// 'callback_filter_value' => 'adjust_color_brightness($darken_factor)'
						),
						array(
							'property'     => 'border-color',
							'selector' => 'button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .added_to_cart:hover, .widget a.button:hover, .site-header-cart .widget_shopping_cart a.button:hover,

							.single-product div.product .woocommerce-product-gallery .woocommerce-product-gallery__trigger:hover',
							// 'callback_filter_value' => 'adjust_color_brightness($darken_factor)'
						),
					)
				),
				'buttons_alt_text_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Alternate Text Color', 'customify' ),
					'live' => true,
					'default'   => '#ffffff',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => 'button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .added_to_cart.alt, .widget-area .widget a.button.alt, .added_to_cart, .widget a.button.checkout',
						),
					)
				),
				'buttons_alt_background_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Alternate Background Color', 'customify' ),
					'live' => true,
					'default'   => '#333333',
					'css'  => array(
						array(
							'property'     => 'background-color',
							'selector' => 'button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .added_to_cart.alt, .widget-area .widget a.button.alt, .added_to_cart, .widget a.button.checkout',
						),
						array(
							'property'     => 'border-color',
							'selector' => 'button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .added_to_cart.alt, .widget-area .widget a.button.alt, .added_to_cart, .widget a.button.checkout',
						),
						// Hover
						array(
							'property'     => 'background-color',
							'selector' => 'button.alt:hover, input[type="button"].alt:hover, input[type="reset"].alt:hover, input[type="submit"].alt:hover, .button.alt:hover, .added_to_cart.alt:hover, .widget-area .widget a.button.alt:hover, .added_to_cart:hover, .widget a.button.checkout:hover',
							// 'callback_filter_value' => 'adjust_color_brightness($darken_factor)'
						),
						array(
							'property'     => 'border-color',
							'selector' => 'button.alt:hover, input[type="button"].alt:hover, input[type="reset"].alt:hover, input[type="submit"].alt:hover, .button.alt:hover, .added_to_cart.alt:hover, .widget-area .widget a.button.alt:hover, .added_to_cart:hover, .widget a.button.checkout:hover',
							// 'callback_filter_value' => 'adjust_color_brightness($darken_factor)'
						),
					)
				),



				/**
				 * Footer
				 */
				'footer_section' => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Footer', 'storefront' ) . '</span>',
				),
				'footer_text_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Footer Text Color', 'customify' ),
					'live' => true,
					'default'   => '#6d6d6d',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => '.site-footer',
						),
					)
				),
				'footer_links_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Footer Links Color', 'customify' ),
					'live' => true,
					'default'   => '#333333',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => '.site-footer a:not(.button)',
						),
					)
				),
				'footer_heading_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Footer Headings Color', 'customify' ),
					'live' => true,
					'default'   => '#333333',
					'css'  => array(
						array(
							'property'     => 'color',
							'selector' => '.site-footer h1, .site-footer h2, .site-footer h3, .site-footer h4, .site-footer h5, .site-footer h6',
						),
					)
				),
				'footer_background_color'   => array(
					'type'      => 'color',
					'label'     => __( 'Footer Background', 'customify' ),
					'live' => true,
					'default'   => '#f0f0f0',
					'css'  => array(
						array(
							'property'     => 'background-color',
							'selector' => '.site-footer',
						),
					)
				),

				/**
				 * Miscellaneous
				 */
				'buttons_section' => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-fonts" class="separator section label large">' . esc_html__( 'Miscellaneous', 'storefront' ) . '</span>',
				),

				'woocommerce_info_background_color'   => array(
					'type'      => 'background-color',
					'label'     => __( 'WooCommerce Info', 'customify' ),
					'live' => true,
					'default'   => '#3d9cd2',
					'css'  => array(
						array(
							'property'     => 'background-color',
							'selector' => '.woocommerce-info, .woocommerce-message, .woocommerce-noreviews, p.no-comments',
						),
					)
				)
			)
		)
	);

	return $options;
}

/**
 * Add the Style Manager cross-theme Customizer section.
 *
 * @param array $options
 *
 * @return array
 */
function pixelgrade_add_customify_style_manager_section( $options ) {
	// If the theme hasn't declared support for style manager, bail.
	if ( ! current_theme_supports( 'customizer_style_manager' ) ) {
		return $options;
	}

	if ( ! isset( $options['sections']['style_manager_section'] ) ) {
		$options['sections']['style_manager_section'] = array();
	}

	// The section might be already defined, thus we merge, not replace the entire section config.
	$options['sections']['style_manager_section'] = array_replace_recursive( $options['sections']['style_manager_section'], array(
		'options' => array(
			'sm_color_primary' => array(
				'connected_fields' => array(
					'body_link_color',
					'main_content_heading_4_color',
					'main_content_heading_5_color',
					'header_navigation_links_active_color',
					'woocommerce_info_background_color'
				),
			),
			'sm_color_secondary' => array(
				'connected_fields' => array(
					'main_content_heading_6_color',
					'buttons_alt_background_color'
				),
			),

			'sm_dark_primary' => array(
				'connected_fields' => array(
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

				),
			),
			'sm_dark_secondary' => array(
				'connected_fields' => array(
					'body_text_color',
					'header_navigation_text_color',
					'footer_text_color',
				),
			),
			'sm_light_primary' => array(
				'connected_fields' => array(
					'main_content_content_background_color',
					'buttons_alt_text_color',
				),
			),
			'sm_light_secondary' => array(
				'connected_fields' => array(
					'header_background_color',
					'buttons_background_color',
					'footer_background_color'
				),
			),
			'sm_light_tertiary' => array(
				'connected_fields' => array(
					
				),
			),
		),
	) );

	return $options;
}