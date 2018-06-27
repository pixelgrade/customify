font-palette →
	name: “brave”,
	fonts →

		font_primary → 
			definition →
				font-family				: “Young Serif”
				font-weight				: [400]
				font-size_points		: [20, 1], [50, 2.9], [100, 3]
				line-height_points		: [14, 1.7], [50, 1.3], [80,1]
			
			elements →
				h1 → 
					size				: 35%,
					weight				: 400,
					letter-spacing		: 0,
					text-transform		: none
				h2 → 
					size				: 25%,
					weight				: 400,
					letter-spacing		: 0,
					text-transform		: none
				h3 → 
					size				: 5%,
					weight				: 400,
					letter-spacing		: 0,
					text-transform		: none
				+
		
		font_secondary → 
			definition →
				font-family				: “HK Grotesk”
				font-weight				: [400], [500], [700]
				font-size_points		: [20, 1], [50, 2.9], [100, 3]
				line-height_points		: [14, 1.7], [50, 1.3], [80,1]
			
			elements →
				h4 → 
					size				: 19px,
					weight				: 500,
					letter-spacing		: 0,
					text-transform		: none
				h5 → 
					size				: 14px,
					weight				: 700,
					letter-spacing		: 0.07em,
					text-transform		: uppercase
				h6 → 
					size				: 12px,
					weight				: 400,
					letter-spacing		: 0.08em,
					text-transform		: uppercase
				+

		font_body → 
			definition →
				font-family				: “Lora”
				font-weight				: [400, italic], [700, italic]
				font-size_points		: [14, 1], [50, 2.9]
				line-height_points		: [14, 1.7], [50, 1.3]
			
			elements →
				body → 
					size				: 18px,
					weight				: 400,
					letter-spacing		: 0,
					text-transform		: none
				+

	