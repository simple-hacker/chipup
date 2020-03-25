<template>
  <div class="flex flex-col">
      <div class="w-full text-5xl sm:text-6xl xl:text-5xl text-green-600 font-extrabold text-center">
        <number
            ref="dashboard-bankroll"
            :from="0"
            :to="10000"
            :duration="1"
            :format="(number) => '£'+number.toFixed(2)"
            easing="Power1.easeOut"
        />
    </div>
	<div class="flex w-full my-3 justify-around">
		<button @click.prevent="bankrollTransactionModal" class="w-1/3 p-3 uppercase text-white text-lg md:text-xl font-bold border border-green-600 bg-green-500 hover:bg-green-600">Deposit</button>
		<button @click.prevent="bankrollTransactionModal" class="w-1/3 p-3 uppercase text-white text-lg md:text-xl font-bold border border-red-600 bg-red-500 hover:bg-red-600">Withdraw</button>
	</div>
	<div class="flex w-full mt-3 border rounded flex flex-col">
		<apexchart type="bar" width="100%" height="200px" :options="options" :series="series"></apexchart>
	</div>
  </div>
</template>

<script>
import TestModal from '../Modals/Test'

export default {
	name: 'Bankroll',
	data () {
		return {
			options: {
				chart: {
					id: 'bankroll'
				},
				xaxis: {
					type: 'category',
      				categories: ['Bankroll Transactions'],
					labels: {
						show: false
					}
				},
				yaxis: {
					labels: {
						show: false
					}
				},
				colors: ['#48bb78', '#f56565'],
				plotOptions: {
					bar: {
						horizontal: true,
					}
				},
			},
			series: [
				{
					name: 'Deposits',
					data: [10000]
				},
				{
					name: 'Withdrawals',
					data: [4585]
				},
			]
		}
	},
	methods: {
		bankrollTransactionModal() {
			this.$modal.show(TestModal);
		}
	}
}
</script>

<style>

</style>