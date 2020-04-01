<template>
  <div>
    <apexchart type="line" width="100%" height="500px" :options="options" :series="series"></apexchart>
  </div>
</template>

<script>
export default {
    name: 'Chart',
    data () {
		return {
			options: {
				chart: {
					id: 'bankroll',
					foreColor: '#FFFFFF',
					toolbar: {
						show: true,
						tools: {
							download: false,
							selection: false,
							zoom: false,
							zoomin: true,
							zoomout: true,
							pan: true,
							reset: false,
							customIcons: []
						},
					},
				},
				xaxis: {
					categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
				},
				colors: ['#48bb78'],
				grid: {
					borderColor: '#38393D',
				},
				tooltip: {
					theme: false,
				}
			},
			series: [
				{
					name: 'Total Profit',
					data: [15000, 10500, 18500, 15500, 21000, 25500, 23400]
				},
			]
		}
    },
}
</script>

<style>
.apexcharts-tooltip {
	background: #202125;
	color: #FFF;
	box-shadow: 2px 2px 6px -4px #16171B;
	border: 1px solid #16171B;
}

.apexcharts-tooltip-title {
	background: #16171B;
}

.apexcharts-xaxistooltip {
	background: #16171B;
	color: #FFF;
	box-shadow: 2px 2px 6px -4px #16171B;
	border: 1px solid #16171B;
}
</style>