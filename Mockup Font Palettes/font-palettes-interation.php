<?php
$config = [
'font-palettes' => [
	'julia' => [
		'preview' => [
			// Font Palette Name
			'title' => "Julia",
			'description' => "A graceful nature, truly tasteful and polished.",

			// Use the following options to style the preview card fonts
			// Including font-family, size, line-height, weight, letter-spacing and text transform	
			'font-title' => [
				'font' 	=> 'font_primary',
				'size'	=> 30,
			],
			'font-description' => [
				'font' 	=> 'font_body',
				'size'	=> 17,
			]
		],

		'fonts' => [
			// Primary is used for main headings [Display, H1, H2, H3]
			'font_primary' => [
				'font-family'				=> "Young Serif",				// Font loaded when a palette is selected
				'font-weights_support'		=> [[400]],							// Load all those fonts weights
				'font-size_points'			=> [14, 1.7], [50, 1.3], [80,1]],	// "Generate" the graph to be used for font-size and line-height
				
				// Define how fonts will look based on their size
				'font-styles' => [
					'min' => 30,
					'max' => 100,
					
					[
						'interval' => [0,14],									// Use the following style from 0px to 31px [including]
						'font-weight'			=> 400,
						'letter-spacing'		=> '0em',
						'text-transform'		=> 'none'
					],
					[32, 43] => [
						'weight'				=> 400,
						'letter-spacing'		=> '0em',
						'text-transform'		=> 'none'
					],
					[44, INF] => [												// Use the following style above 44px
						'weight'				=> 400,
						'letter-spacing'		=> '0em',
						'text-transform'		=> 'none'
					]
				]

			],

			// Secondary font is used for smaller headings [H4, H5, H6], including meta details
			'font_secondary' => [
				'definition' => [
					'font-family'				=> "HK Grotesk",
					'font-weights_support'		=> [[400], [500], [700]],			
					'font-size_points'			=> [[14, 1.7], [50, 1.3], [80,1]],
					'font-styles-intervals' => [
						[0, 14] => [												
							'weight'				=> 400,
							'letter-spacing'		=> '0.08em',
							'text-transform'		=> 'uppercase'
						],
						[14, 18] => [
							'weight'				=> 700,
							'letter-spacing'		=> '0.07em',
							'text-transform'		=> 'uppercase'
						],
						[19, 'INF'] => [
							'weight'				=> 500,
							'letter-spacing'		=> 0,
							'text-transform'		=> 'none'
						]
					]
				]
			],

			// Used for Body Font [eg. entry-content]
			'font_body' => [
				'definition' => [
					'font-family'				=> "PT Serif",
					'font-weights_support'		=> [[400, 'italic'], [700, 'italic']],
					'font-size_points'			=> [[15, 1.7], [17, 1.6], [18, 1.5]],
					
					// Define how fonts will look based on their size
					'font-styles-intervals' => [
						[0, 'INF'] => [
							'weight'				=> 400,
							'letter-spacing'		=> 0,
							'text-transform'		=> 'none',
						]
					]
				]
			],
			
		]
	]
];