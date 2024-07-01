/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
const { merge } = require('webpack-merge');
const nodeExternals = require('webpack-node-externals')
const path = require('path');

const common = require('./webpack.common.js');

module.exports = merge(common, {
	mode: 'development',
	context: path.resolve(__dirname, 'src'),
	devtool: 'inline-cheap-module-source-map',
	externals: [nodeExternals()]
})
