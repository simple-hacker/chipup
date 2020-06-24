<template>
	<div
		@click.prevent="showTransactionDetails(bankrollTransaction)"
		class="flex justify-between items-center p-2 md:p-3 bg-gray-500 hover:bg-gray-450 rounded border-r-4 shadow hover:shadow-2xl cursor-pointer text-white text-sm md:text-lg"
        :class="this.bankrollTransaction.amount < 0 ? 'border-red-500 hover:border-red-400' : 'border-green-500 hover:border-green-400'"
	>
		<div
			class="uppercase"
			v-text="transactionDate"
		>
		</div>
		<div
			class="text-lg md:text-xl font-bold"
			:class="(bankrollTransaction.amount < 0) ? 'text-red-500' : 'text-green-500'"
			v-text="$n(bankrollTransaction.amount, { style: 'currency', currency: bankrollTransaction.currency })"
		>
		</div>
	</div>
</template>

<script>
import moment from 'moment'

import BankrollTransactionDetails from '@components/Bankroll/BankrollTransactionDetails'

export default {
	name: 'BankrollTransactionSummary',
	components: { BankrollTransactionDetails },
	props: {
		bankrollTransaction: Object
	},
	computed: {
		transactionDate() {
			return moment.utc(this.bankrollTransaction.date).format("dddd, Do MMMM YYYY")
		},
	},
	methods: {
		showTransactionDetails() {
			const modalClass = (this.bankrollTransaction.amount < 0) ? 'modal-red' : 'modal-green'
            this.$modal.show(BankrollTransactionDetails, {
                // Modal props
                bankrollTransaction: this.bankrollTransaction,
            }, {
                // Modal Options
				classes: ['modal', modalClass],
				style: 'overflow: visible;',
                height: 'auto',
                width: '95%',
                maxWidth: 600,
            })
        },
	}
}
</script>

<style>

</style>