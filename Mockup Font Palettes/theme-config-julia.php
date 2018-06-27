<?php

// JULIA
// Fonts

// Main Content
'main_content' => (
	'options' => (

		// [Section] FONTS
		
		// Page Title
		'main_content_page_title_font' => (
			// Default options used as fallback	
			'default' => (
				'font-family'    => 'Lora',
				'font-weight'    => '700',
				'font-size'      => 66,
				'line-height'    => 1.2,
				'letter-spacing' => 0,
				'text-transform' => 'none',
			),
			'selectors' 	=> (),

			// Font Palette Rules
			'font-palette' 	=> (
				'font' 	=> 'font_primary',
				'size'	=> 66
			),
		),

		// Heading 3
		'main_content_heading_3_font' => (
			// Default options used as fallback	
			'default' => (
				'font-family'    => 'Lora',
				'font-weight'    => '700',
				'font-size'      => 22,
				'line-height'    => 1.3,
				'letter-spacing' => 0,
				'text-transform' => 'none',
			),
			'selectors' 	=> (),

			// Font Palette Rules
			'font-palette' 	=> (
				'font' 	=> 'font_primary',
				'size'	=> 22
			),
		),














			'main_content_body_text_font' => (
				'default' => (),
			),

			'main_content_paragraph_text_font' => (
				'default' => (
					'font-family'    => 'PT Serif',
					'font-weight'    => '400',
					'font-size'      => 17,
					'line-height'    => 1.6,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),
			),

			'main_content_quote_block_font' => (
				'default' => (
					'font-family'    => "Lora",
					'font-weight'    => '700',
					'font-size'      => 28,
					'line-height'    => 1.17,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),
			),

			// [Sub Section] Headings Fonts
			'main_content_heading_1_font'   => (
				'default' => (
					'font-family'    => 'Lora',
					'font-weight'    => '700',
					'font-size'      => 44,
					'line-height'    => 1,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),
			),

			'main_content_heading_2_font' => (
				'default' => (
					'font-family'    => 'Lora',
					'font-weight'    => '700',
					'font-size'      => 32,
					'line-height'    => 1.25,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),
			),

			'main_content_heading_3_font' => (
				'default' => (
					'font-family'    => 'Lora',
					'font-weight'    => '700',
					'font-size'      => 24,
					'line-height'    => 1.3,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),
				'selector' => 'h3, .h3, .post-navigation .nav-title',
			),

			'main_content_heading_4_font' => (
				'selector' => 'h4, .h4, .comment__author',
				'default' => (
					'font-family'    => 'PT Serif',
					'font-weight'    => '700',
					'font-size'      => 18,
					'line-height'    => 1.15,
					'letter-spacing' => 0,
					'text-transform' => 'none',
				),
			),

			'main_content_heading_5_font' => (
				'selector' => '.entry-content h5, .h5, h5, blockquote cite, blockquote footer,
								.header-meta, .nav-links__label,
								.comment-form label,
								.contact-form>div>.grunion-field-label:not(.checkbox):not(.radio),
								div.wpforms-container[class] .wpforms-form .wpforms-field-label,
								.nf-form-cont .label-above .nf-field-label label,
								#content .sharedaddy[class] .sd-button',
				'default' => (
					'font-family'    => 'Montserrat',
					'font-weight'    => 'regular',
					'font-size'      => 14,
					'line-height'    => 1.2,
					'letter-spacing' => 0.154,
					'text-transform' => 'uppercase',
				),
			),

			'main_content_heading_6_font' => (
				'selector' => 'h6, .h6, .c-author__footer, .comment__metadata, .reply a',
				'default' => (
					'font-family'    => 'Montserrat',
					'font-weight'    => '600',
					'font-size'      => 12,
					'line-height'    => 1.2,
					'letter-spacing' => 0.154,
					'text-transform' => 'uppercase',
				),
			),
		),
	),
);