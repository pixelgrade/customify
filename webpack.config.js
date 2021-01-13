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
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
              '@babel/preset-react',
            ],
          }
        }
      },
      {
        test: /\.s[ac]ss$/i,
        use: [
          // Creates `style` nodes from JS strings
          "style-loader",
          // Translates CSS into CommonJS
          "css-loader",
          // Compiles Sass to CSS
          "sass-loader",
        ],
      },
    ],
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
