<template>
	<div class="flex flex-col">
		<div class="flex flex-col bg-gray-600 rounded shadow p-2">
            <div class="w-full">
				<transaction-amount
                    :currency="currency"
                    :amount="amount"
					:error="errors.amount"
					v-on:clear-error="delete errors.amount"
                    v-on:update-currency="currency = arguments[0]"
                    v-on:update-amount="amount = arguments[0]"
                />
                <span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>
            </div>
            <div class="w-full mt-3">
                <textarea
					v-model="comments"
					placeholder="Add comments"
					rows=4
					class="p-2 bg-gray-500"
					:class="{ 'error-input' : errors.amount }"
					@input="delete errors.comments"
				></textarea>
                <span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
			</div>
			<div class="mt-2 flex p-2">
				<button
					@click.prevent="addTransaction(withdrawalAmount)"
					type="button"
					class="btn btn-red flex-1 mr-1"
				>
					Withdraw
				</button>
				<button
					@click.prevent="addTransaction(transaction.amount)"
					type="button"
					class="btn btn-green flex-1 ml-1"
				>
					Deposit
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import TransactionAmount from '@components/TransactionAmount'

import { mapActions } from 'vuex'

export default {
	name: 'BankrollTransaction',
	components: { TransactionAmount },
    data() {
		return {
			amount: 0,
			currency: this.$store.state.user.currency,
			comments: '',
			errors: {},
		}
	},
	computed: {
		withdrawalAmount() {
			return this.transaction.amount * -1
		}
	},
    methods: {
		...mapActions('bankroll', ['addBankrollTransaction']),
		addTransaction: function(amount) {
			this.addBankrollTransaction({
				amount: amount,
				comments: this.comments
			})
			.then(response =>{
				this.$emit('close')
				if (amount < 0) {
					this.$snotify.warning(`Withdrew £`+(amount * -1).toLocaleString()+' from your bankroll.')
				} else {
					this.$snotify.success(`Deposited £`+parseInt(amount).toLocaleString()+' to your bankroll.')
				}
				this.amount = 0
				this.comments = ''
			})
			.catch(error => {
				this.$snotify.error(error.response.data.message)
				this.errors = error.response.data.errors
			})
		}
	},
}
</script>

<style scoped>
</style>