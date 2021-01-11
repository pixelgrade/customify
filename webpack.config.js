const UglifyJsPlugin = require( 'uglifyjs-webpack-plugin' );
const path = require( 'path' );

module.exports = {
  entry: {
    './dist/js/customizer/color-palettes': './src/js/customizer/color-palettes/index.js',
    './dist/js/customizer/color-palettes.min': './src/js/customizer/color-palettes/index.js',
  },
  output: {
    path: path.resolve( __dirname ),
    filename: '[name].js'
  },
  externals: {
    jquery: 'jQuery',
    lodash: 'lodash',
  },
  optimization: {
    minimize: true,
    minimizer: [
      new UglifyJsPlugin( {
        include: /\.min\.js$/
      } )
    ]
  }
};
