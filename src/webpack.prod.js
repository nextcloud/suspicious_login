/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
const { merge } = require('webpack-merge')
const SPDXplugin = require('./WebpackSPDXPlugin.js')
const common = require('./webpack.common.js')

const merged = merge(common, {
	mode: 'production',
	devtool: 'source-map'
})

merged.plugins.push(new SPDXplugin())
merged.optimization = {
	minimizer: [{
		apply: (compiler) => {
			// Lazy load the Terser plugin
			const TerserPlugin = require('terser-webpack-plugin')
			new TerserPlugin({
				extractComments: false,
				terserOptions: {
					format: {
						comments: false,
					},
					compress: {
						passes: 2,
					},
				},
			}).apply(compiler)
		},
	}]
}

module.exports = merged
