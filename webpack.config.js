const path = require( 'path' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const WebpackZipPlugin = require('webpack-zip-plugin');
const browserSyncPlugin = require( 'browser-sync-webpack-plugin' );
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const MiniCSSExtractPlugin = require('mini-css-extract-plugin');

const pluginSlug = 'advanced-shipping-methods-for-woocommerce';

const buildFolder = path.resolve( __dirname, pluginSlug );
// const vendorFolder = path.resolve( buildFolder, 'vendor' );

var devFolder = '';
var endPath = '';

const config = env => {

    const pluginList = [];
    console.log(env.NODE_ENV);


	if ( env.LOC === "m1" ) {
		devFolder = '/Users/parkermathewson/mac-sites/wp56tester/wp-content/plugins/' + pluginSlug; // M1
		endPath = '/Users/parkermathewson/Library/Mobile\ Documents/com~apple~CloudDocs/theritesites/completed_pluginsv2'; // M1
		buildPath = '/Users/parkermathewson/theritesites/completed_pluginsv2'; // M1
	}
    if ( env.LOC === "corsair" ) {
        devFolder = '/var/www/wpdev.com/public_html/wp-content/plugins/' + pluginSlug; // Corsair
        endPath = '/home/parkerm34/Documents/theritesites/completed_plugins'; // Corsair
    }
    if ( env.LOC === "mac" ) {
        devFolder = '/Users/parker/sites/localwptest/wp-content/plugins/' + pluginSlug; // Mac
        endPath = '/Users/parker/Documents/theritesites/completed_plugins'; // Mac
    }

    var endFolder = endPath + '/' + pluginSlug;


    if( env.NODE_ENV === 'production' ) {
        pluginList.push(
            new CopyWebpackPlugin( { patterns: [
                    { from: path.resolve( __dirname, 'admin' ) + '/**', to: buildFolder },
                    { from: path.resolve( __dirname, 'includes' ) + '/**', to: buildFolder },
                    { from: path.resolve( __dirname, 'languages' ) + '/**', to: buildFolder },
                    { from: path.resolve( __dirname, 'README.*' ), to: buildFolder },
                    { from: path.resolve( __dirname, 'woo-includes' ) + '/**', to: buildFolder },
                    { from: path.resolve( __dirname, 'LICENSE.txt' ), to: buildFolder },
                    // { from: path.resolve( __dirname, 'CHANGELOG.*' ), to: buildFolder },
                    { from: path.resolve( __dirname, '*.php' ), to: buildFolder },
                    /** Above is for zip folder. Below is for repositories. **/
                    { from: path.resolve( __dirname, 'admin' ) + '/**', to: endFolder },
                    { from: path.resolve( __dirname, 'includes' ) + '/**', to: endFolder },
                    { from: path.resolve( __dirname, 'languages' ) + '/**', to: endFolder },
                    { from: path.resolve( __dirname, 'README.*' ), to: endFolder },
                    { from: path.resolve( __dirname, 'woo-includes' ) + '/**', to: endFolder },
                    { from: path.resolve( __dirname, 'LICENSE.txt' ), to: endFolder },
                    // { from: path.resolve( __dirname, 'CHANGELOG.*' ), to: endFolder },
                    { from: path.resolve( __dirname, '*.php' ), to: endFolder },
                ]
            } ),
			new MiniCSSExtractPlugin({
				filename: "/public/css/" + pluginSlug + ".min.css",
				chunkFilename: "[id].css"
			  }),
            new WebpackZipPlugin({
                initialFile: pluginSlug,
                endPath: buildPath,
                zipName: pluginSlug + '.zip'
            } )
        );
    }
    else {
        pluginList.push(
            new browserSyncPlugin({
                files: [
                    './' + pluginSlug + '.php',
                    './includes/*.php',
                    './includes/**/*.php',
                    './',
                    '!./node_modules',
                    '!./yarn-error.log',
                    '!./*.json',
                    '!./Gruntfile.js',
                    '!./README.md',
                    '!./*.xml',
                    '!./*.yml'
                ],
                reloadDelay: 0
            }),
            new CopyWebpackPlugin( {
                patterns: [
                    { from: path.resolve( __dirname, 'admin' ) + '/**', to: devFolder },
                    { from: path.resolve( __dirname, 'includes' ) + '/**', to: devFolder },
                    { from: path.resolve( __dirname, 'languages' ) + '/**', to: devFolder },
                    { from: path.resolve( __dirname, 'README.*' ), to: devFolder },
                    { from: path.resolve( __dirname, 'woo-includes' ) + '/**', to: devFolder },
                    { from: path.resolve( __dirname, 'LICENSE.txt' ), to: devFolder },
                    // { from: path.resolve( __dirname, 'CHANGELOG.*' ), to: devFolder },
                    { from: path.resolve( __dirname, '*.php' ), to: devFolder }
                ]
            } ),
        );
    }

    return {
        entry: {
            "admin" : path.resolve(__dirname, 'src', 'admin.js')
        },
        output: {
            filename: './dist/' + pluginSlug + '.js',
            path: __dirname,
        },
		optimization: {
			minimizer: [
				new UglifyJsPlugin({
					cache: true,
					parallel: true,
					sourceMap: true // set to true if you want JS source maps
				  }),
				new OptimizeCSSAssetsPlugin({})
			]
		},
        module: {
            rules: [
                {
                    test: /\.js/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['babel-preset-env', 'react']
                        }
                    }
                },{
					test: /\.css$/,
					use: [
						MiniCSSExtractPlugin.loader,
						"css-loader"
					]
				},
            ]
        },
        plugins: pluginList
    };
};


module.exports = config
