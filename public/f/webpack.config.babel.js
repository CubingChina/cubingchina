import path  from 'path'
import webpack  from 'webpack'
import autoprefixer  from 'autoprefixer'
import AssetMapPlugin  from './js/asset-map-plugin'
import ExtractTextPlugin  from 'extract-text-webpack-plugin'
const DEVELOPMENT = process.env.NODE_ENV === 'develop'

function getName(name) {
  if (!DEVELOPMENT) {
    name = name.replace(/\.(\w+)(\?|$)/, '.min.$1$2')
  }
  return name
}

module.exports = {
  entry: {
    common: ['jquery', 'bootstrap', 'bootstrap/dist/css/bootstrap.css', 'font-awesome/css/font-awesome.css'],
    main: path.resolve('js/main.js')
  },
  output: {
    path: path.resolve('build'),
    filename: getName('js/[name].js?[chunkhash:8]'),
    chunkFilename: getName('js/[name].js?[chunkhash:8]'),
    publicPath: '/f/build/'
  },
  module: {
    rules: [
    {
      test: /\.css$/,
      loader: ExtractTextPlugin.extract({
        fallback: 'style-loader',
        use: 'css-loader!postcss-loader'
      })
    },
    {
      test: /\.less$/,
      exclude: /node_modules/,
      loader: ExtractTextPlugin.extract({
        fallback: 'style-loader',
        use: 'css-loader!postcss-loader!less-loader'
      })
    },
    {
      test: /\.js$/,
      exclude: /node_modules/,
      loader: 'babel-loader'
    },
    {
      test: /\.json$/,
      loader: 'json-loader'
    },
    {
      test: /\.(png|jpe?g|gif|ico)$/,
      use: [
        {
          loader: 'url-loader',
          options: {
            name: 'images/[hash:8].[ext]',
            limit: 1024
          }
        }
      ]
    },
    {
      test: /\.(otf|eot|svg|ttf|woff2?)(\?v=\d+\.\d+\.\d+)?$/,
      use: [
        {
          loader: 'url-loader',
          options: {
            name: 'fonts/[hash:8].[ext]',
            limit: 1024
          }
        }
      ]
    }]
  },
  resolve: {
    modules: ['node_modules', path.resolve('js')],
    extensions: ['.js', '.vue', '.json', '.css', '.less']
  },
  plugins: [
    new webpack.LoaderOptionsPlugin({
      options: {
        context: path.resolve(''),
        postcss: [autoprefixer()],
        babel: {
          presets: ['es2017'],
          sourceMap: true
        }
      }
    }),
    new AssetMapPlugin(path.resolve('assets-map.json')),
    new ExtractTextPlugin(getName('css/[name].css?[contenthash:8]')),
    new webpack.optimize.CommonsChunkPlugin({
      names: ['common'],
    }),
    new webpack.ProvidePlugin({
        jQuery: 'jquery',
        $: 'jquery',
    })
  ],
  devtool: 'source-map'
}
