<template>
	<div class="flex flex-col">
		<div class="flex flex-col bg-gray-600 rounded shadow p-2">
            <div class="w-2/3 mx-auto">
                <input
					v-model="amount"
					type="number"
					class="p-2 bg-gray-500 text-2xl"
					:class="{ 'error-input' : errors.amount }"
					@input="delete errors.amount"
				>
                <span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>
            </div>
            <div class="w-2/3 mx-auto mt-3">
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
					@click.prevent="addTransaction(amount)"
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
import { mapActions } from 'vuex'

export default {
	name: 'BankrollTransaction',
    data() {
		return {
			amount: 0,
			comments: '',
			errors: {},
		}
	},
	computed: {
		withdrawalAmount() {
			return this.amount * -1
		}
	},
    methods: {
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
		},
		...mapActions('bankroll', ['addBankrollTransaction'])
	},
}
</script>

<style scoped>
</style>