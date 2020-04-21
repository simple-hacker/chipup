<template>
	<div class="flex justify-between items-center p-2 border border-muted-dark shadow bg-card hover:bg-muted-dark cursor-pointer text-white">
		<div class="uppercase">{{ transactionDate }}</div>
		<div
			class="text-lg font-bold"
			:class="(bankrollTransaction.amount < 0) ? 'text-red-500' : 'text-green-500'"
		>
			{{ transactionAmount }}
		</div>
	</div>
</template>

<script>
export default {
	name: 'BankrollTransactionSummary',
	props: {
		bankrollTransaction: Object
	},
	computed: {
		transactionDate() {
			return new Date(this.bankrollTransaction.updated_at).toDateString()
		},
		transactionAmount() {
			return Vue.prototype.currency.format(this.bankrollTransaction.amount / 100);
		}
	}
}
</script>

<style>

</style>