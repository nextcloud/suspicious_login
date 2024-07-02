<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script>
	import {Line} from 'vue-chartjs'

	export default {
		name: 'ModelPerformanceChart',
		extends: Line,
		props: {
			models: {
				type: Array,
				required: true,
			},
		},
		data () {
			const sorted = this.models.concat().sort((m1, m2) => m1.createdAt - m2.createdAt);

			return {
				chartData: {
					labels: sorted
						.map(m => m.createdAt)
						.map(ts => {
							const d = new Date();
							d.setTime(ts * 1000);
							return d.toLocaleDateString();
						}),
					datasets: [
						{
							label: t('suspicious_login', 'Precision'),
							fill: false,
							borderColor: "rgba(45,45,45,0.8)",
							borderDash: [5, 15],
							data: sorted.map(m => m.precisionN * 100),
						},
						{
							label: t('suspicious_login', 'Recall'),
							fill: false,
							borderColor: "rgba(80,80,80,0.8)",
							data: sorted.map(m => m.recallN * 100),
						}
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					scales: {
						yAxes: [{
							display: true,
							ticks: {
								beginAtZero: true
							}
						}]
					}
				}
			}
		},
		mounted () {
			this.renderChart(this.chartData, this.options);
		}
	}
</script>
