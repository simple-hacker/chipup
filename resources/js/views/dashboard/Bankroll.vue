<template>
	<div class="flex flex-col">
		<div class="w-full text-5xl sm:text-6xl xl:text-5xl text-green-500 font-extrabold text-center">
			<number
				ref="dashboard-bankroll"
				:from="0"
				:to="10000.00"
				:duration="1"
				:format="(number) => '£'+number.toLocaleString()"
				easing="Power1.easeOut"
			/>
		</div>
		<div class="flex w-full my-3 border border-muted-dark rounded flex flex-col">
			<apexchart type="bar" width="100%" height="200px" :options="options" :series="series"></apexchart>
		</div>
		<div class="flex w-full mt-3 justify-end">
			<button @click.prevent="bankrollTransactionModal" class="btn-green">Manage Bankroll</button>
		</div>
	</div>
</template>

<script>
import BankrollTransaction from '../../components/Bankroll/BankrollTransaction';

export default {
	name: 'Bankroll',
	data () {
		return {
			options: {
				chart: {
					id: 'bankroll',
					foreColor: '#FFFFFF',
				},
				dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
						return Vue.prototype.currency.format(val);
                    },
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
				grid: {
					borderColor: '#38393D'
				},
				tooltip: {
					theme: false,
				}
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
			this.$modal.show(BankrollTransaction, {
				title: 'Manage Bankroll',
				closeBtn: true,
			}, {
				classes: 'bg-card text-white p-4 rounded-lg border border-muted-dark',
				minHeight: 150,
				height: 'auto',
			});
		}
	}
}
</script>

<style>

</style>