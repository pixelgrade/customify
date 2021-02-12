const UglifyJsPlugin = require( 'uglifyjs-webpack-plugin' );
const path = require( 'path' );

const files = [
  'customizer',
  'customizer-preview',
  'dark-mode',
];

function camelize( str ) {
  const arr = str.split( '-' );

  return arr.slice(1).reduce( ( acc, curr ) => {
    return acc + curr.charAt(0).toUpperCase() + curr.slice(1).toLowerCase();
  }, arr[0] );
}

function kebabize( str ) {
  return str.replace( /([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2' ).toLowerCase();
}

const entries = files.reduce( ( acc, curr ) => {
  const src = `./src/js/${ curr }/index.js`;
  acc[ camelize( curr ) ] = src;
  acc[ `${ camelize( curr ) }.min` ] = src;
  return acc;
}, {} );

module.exports = {
  mode: 'production',
  entry: entries,
  output: {
    path: path.join( __dirname, "dist/js" ),
    filename: pathData => {
      return `${ kebabize( pathData.chunk.name ) }.js`;
    },
    library: [ 'sm', '[name]' ],
    libraryTarget: 'this',
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
