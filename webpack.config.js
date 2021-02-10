const UglifyJsPlugin = require( 'uglifyjs-webpack-plugin' );
const path = require( 'path' );

module.exports = {
  entry: {
    './dist/js/color-palettes': './src/js/color-palettes/index.js',
    './dist/js/color-palettes.min': './src/js/color-palettes/index.js',
    './dist/js/customizer': './src/js/customizer/index.js',
    './dist/js/customizer.min': './src/js/customizer/index.js',
    './dist/js/dark-mode': './src/js/dark-mode/index.js',
    './dist/js/dark-mode.min': './src/js/dark-mode/index.js',
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
  },
};
