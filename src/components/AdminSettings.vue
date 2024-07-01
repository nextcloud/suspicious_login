<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
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
