<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div>
		<h3>{{ t('suspicious_login', 'Classifier model statistics') + ' (' + this.addressType + ')' }}</h3>
		<p v-if="!stats.active || models.length === 0">
			{{ t('suspicious_login', 'No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured.', {
			days: stats.trainingDataConfig.maxAge
			}) }}
		</p>
		<p v-else>
			{{ t('suspicious_login', 'During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance.', {
			time: relativeModified(models[0].createdAt),
			precision: models[0].precisionN * 100,
			recall: models[0].recallN * 100
			}) }}
			<ModelPerformanceChart :models="models" :styles="chartStyles"></ModelPerformanceChart>
		</p>
	</div>
</template>

<script>
	import ModelPerformanceChart from './ModelPerformanceChart';

	export default {
		name: 'ModelPerformance',
		components: {
			ModelPerformanceChart
		},
		props: {
			title: {
				type: String,
				required: true,
			},
			stats: {
				type: Object,
				required: true,
			},
			addressType: {
				type: String,
				required: true,
			}
		},
		data() {
			return {
				chartStyles: {
					height: '350px',
					position: 'relative',
				}
			};
		},
		computed: {
			models() {
				return this.stats.recentModels.filter(m => m.addressType === this.addressType)
			},
		},
		methods: {
			relativeModified (ts) {
				return OC.Util.relativeModifiedDate(ts * 1000);
			}
		}
	}
</script>

<style scoped>

</style>