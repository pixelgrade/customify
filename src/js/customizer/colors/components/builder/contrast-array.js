const optimalContrastArray = Array.from( Array( 12 ) ).map( ( x, i ) => {
  return Math.pow( 21, i / 11 );
} );

//	https://medium.com/envoy-design/designing-an-accessible-color-scheme-again-fd35cfa9d796
const contrastRangesArray = [
  [1, 1],
  [1.07, 1.17],
  [1.21, 1.31],
  [1.5, 1.91],
  [2.1, 2.63],
  [3, 3.5],
  [4.51, 4.67],
  [6, 7],
  [8.75, 10.5],
  [11.67, 15],
  [16.15, 19.1],
  [21, 21],
];

// powers of 21 ^ 1/10 but with small adjustments for the lighter colors
const myOptimalContrastArray = [
  1,
  1.07, // 1.32
  1.25, // 1.74
  1.8,  // 2.29
  2.63, // 3.03
  3.99,

  5.26,
  6.94,
  9.15,
  12.07, // fg1
  15.92, // fg2
  19 // almost black (21)
];

const myOptimalContrastArray2 = [
  1, // 21 ^ 0
  1.079, // 21 ^ 0.025

  1.35588, // 21 ^ 0.1
  1.83841, // 21 ^ 0.2
  2.49267, // 21 ^ 0.3
  3.37977, // 21 ^ 0.4

  4.58257, // 21 ^ 0.5
  6.21343, // 21 ^ 0.6
  8.42468, // 21 ^ 0.7
  11.42287, // 21 ^ 0.8
  15.48807, // 21 ^ 0.9

  19.4609, // 21 ^ 0.975
//  21, // 21 ^ 1
];

const mathematicArray = [
  1,
  1.1,
  1.25,
  1.5275,
  2.3332,
  3, // AA
  4.5825, // AA
  7, // AAA
  9,
  13.7475,
  16.8,
  19,
//  21,
];

const minContrastArray = contrastRangesArray.map( x => x[0] );
const maxContrastArray = contrastRangesArray.map( x => x[1] );

export default myOptimalContrastArray;
