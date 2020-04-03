<template>
  <div>
    <apexchart type="line" width="100%" :options="options" :series="series"></apexchart>
  </div>
</template>

<script>
export default {
    name: 'ProfitLineChart',
    data () {
		return {
			options: {
				chart: {
					id: 'profit',
                    foreColor: '#FFFFFF',
                    height: '100%',
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
				grid: {
					borderColor: '#38393D',
				},
				tooltip: {
					theme: false,
                },
                theme: {
                    mode: 'light', 
                    palette: 'palette1', 
                    monochrome: {
                        enabled: true,
                        color: '#48bb78',
                        shadeTo: 'dark',
                        shadeIntensity: 0.9
                    },
                }
			},
			series: [
				{
					name: 'Total Profit',
                    data: [15000, 10500, 18500, 15500, 21000, 25500, 23400],
                    color: '#394923',
				},
				{
					name: 'Cash Games',
					data: [8589, 7588, 14550, 10000, 14562, 19764, 22107]
				},
				{
					name: 'Tournaments',
					data: [0, 0, 2355, 1145, 5324, 0, 0]
				},
			]
		}
    },
}
</script>

<style>

</style>