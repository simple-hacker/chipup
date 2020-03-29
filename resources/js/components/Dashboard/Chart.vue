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
					foreColor: '#FFFFFF'
				},
				xaxis: {
					categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
				},
				colors: ['#48bb78'],
				grid: {
					borderColor: '#38393D',
				}
			},
			series: [
				{
					name: 'Bankroll',
					data: [15000, 10500, 18500, 15500, 21000, 25500, 23400]
				},
			]
		}
    },
}
</script>

<style>

</style>