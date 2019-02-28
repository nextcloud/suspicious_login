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
