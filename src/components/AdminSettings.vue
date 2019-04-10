<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<div id="suspicious-login-admin-settings" class="section">
		<h2>{{ t('suspicious_login', 'Suspicious login detection') }}</h2>
		<p>
			{{ t('suspicious_login', 'The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address.' )}}
		</p>
		<h3>{{ t('suspicious_login', 'Training data statistics') }}</h3>
		<p>
			{{ t('suspicious_login', 'So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples.', {
				total: stats.trainingDataStats.loginsCaptured,
				distinct: stats.trainingDataStats.loginsAggregated,
			}) }}
		</p>
		<h3>{{ t('suspicious_login', 'Classifier model statistics') }}</h3>
		<p v-if="!stats.active">
			{{ t('suspicious_login', 'No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured.', {
				days: stats.trainingDataConfig.maxAge
			}) }}
		</p>
		<p v-else>
			{{ t('suspicious_login', 'During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance.', {
				time: relativeModified(stats.recentModels[0].createdAt),
				precision: stats.recentModels[0].precisionN * 100,
				recall: stats.recentModels[0].recallN * 100
			}) }}
			<ModelPerformanceChart :models="stats.recentModels" :styles="chartStyles"/>
		</p>
	</div>
</template>

<script>
	import Axios from 'nextcloud-axios';
	import ModelPerformanceChart from './ModelPerformanceChart';

	export default {
		name: 'AdminSettings',
		props: {
			stats: {
				type: Object,
				required: true,
			},
		},
		components: {
			ModelPerformanceChart
		},
		data() {
			return {
				chartStyles: {
					height: '350px',
					position: 'relative',
				}
			};
		},
		methods: {
			relativeModified (ts) {
				return OC.Util.relativeModifiedDate(ts * 1000);
			}
		}
	}
</script>
