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
		<ModelPerformance :title="t('suspicious_login', 'IPv4')" :stats="stats" address-type="ipv4"></ModelPerformance>
		<ModelPerformance :title="t('suspicious_login', 'IPv6')" :stats="stats" address-type="ipv6"></ModelPerformance>
	</div>
</template>

<script>
	import ModelPerformance from './ModelPerformance';

	export default {
		name: 'AdminSettings',
		props: {
			stats: {
				type: Object,
				required: true,
			},
		},
		components: {
			ModelPerformance
		}
	}
</script>
