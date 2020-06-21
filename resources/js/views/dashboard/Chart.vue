<template>
	<div>
		<apexchart type="line" :options="options" :series="series"></apexchart>
	</div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'Chart',
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
					height: '100%',
					width: '100%',
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
		...mapGetters('sessions', ['sessionsProfitSeries']),
		...mapGetters('cash_games', ['cashGamesProfitSeries']),
		...mapGetters('tournaments', ['tournamentsProfitSeries']),
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
		},
	},
}
</script>

<style>

</style>