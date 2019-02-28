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
			{{ t('suspicious_login', 'The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if new IPs come from suspicious IP addresses.' )}}
		</p>
		<template v-if="!loading">
			<h3>{{ t('suspicious_login', 'Training data statistics') }}</h3>
			<p>
				{{ t('suspicious_login', 'So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples.', {
 					total: stats.trainingDataStats.loginsCaptured,
 					distinct: stats.trainingDataStats.loginsAggregated,
				}) }}
			</p>
			<h3>{{ t('suspicious_login', 'Classifier statistics') }}</h3>
			<p v-if="!stats.active">
				{{ t('suspicious_login', 'No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured.', {
 					days: stats.trainingDataConfig.maxAge
				}) }}
			</p>
			<p v-else>
				{{ t('suspicious_login', 'During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins, whereas {precision}% of the logins classified as suspicious are indeed suspicious.', {
					time: relativeModified(stats.recentModels[0].createdAt),
					precision: stats.recentModels[0].precisionN * 100,
					recall: stats.recentModels[0].recallN * 100
				}) }}
			</p>
		</template>
	</div>
</template>

<script>
	import Axios from 'nextcloud-axios';

	export default {
		name: 'AdminSettings',
		mounted () {
			this.fetchStats();
		},
		data() {
			return {
				loading: true,
				stats: {},
			};
		},
		methods: {
			fetchStats () {
				Axios.get(OC.generateUrl('/apps/suspicious_login/settings/statistics'))
					.then(resp => resp.data)
					.then(stats => {
						this.stats = stats;
						this.loading = false;
					})
					.catch(console.error.bind(this));
			},
			relativeModified (ts) {
				return OC.Util.relativeModifiedDate(ts * 1000);
			}
		}
	}
</script>

<style scoped>

</style>
