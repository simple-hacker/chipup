<template>
  <div>
    <apexchart type="line" width="100%" height="500px" :options="options" :series="series"></apexchart>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'ProfitLineChart',
    data () {
		return {
			options: {
				chart: {
					id: 'profit',
					type: 'area',
					foreColor: '#FFFFFF',
					toolbar: {
						show: true,
						tools: {
							download: false,
							selection: false,
							zoomin: true,
							zoomout: true,
							pan: true,
							customIcons: []
						},
					},
				},
				xaxis: {
					type: 'datetime'
				},
				yaxis: {
                    labels: {
                        formatter: (val, opts) => {
                            return this.$n(val, 'currencyNoFraction')
                        },
                    },
                    title: {
                        text: 'Profit'
                    }
                },
				colors: ['#48BB78', '#4851BB', '#BBB248', '#BB488B'],
				grid: {
					borderColor: '#38393D',
				},
				tooltip: {
					theme: false,
					shared: true,
					followCursor: true,
				}
			},
		}
	},
	computed: {
		...mapGetters('filtered_sessions', ['sessionsProfitSeries', 'cashGamesProfitSeries', 'tournamentsProfitSeries']),
		series() {
			return [
				{
					name: 'Total Profit',
					data: this.sessionsProfitSeries
				},
				{
					name: 'Cash Games Profit',
					data: this.cashGamesProfitSeries
				},
				{
					name: 'Tournament Profit',
					data: this.tournamentsProfitSeries
				},
			]
		}
	},
}
</script>

<style>

</style>