<template>
	<div class="flex flex-col relative">
		<div v-if="closeBtn" class="absolute top-0 right-0">
			<button @click="$emit('close')" class="hover:text-muted-light cursor-pointer">
				<i class="fas fa-times-circle fa-2x"></i>
			</button>
		</div>
		<h1 v-if="title" v-text="title" class="text-2xl font-semibold mb-6"></h1>

		<div class="flex w-full justify-center mb-4">
			<span class="text-4xl font-bold mr-3">£</span>
			<div class="w-1/2 flex-col">
				<input
					v-model="amount"
					type="number"
					class="w-full rounded border bg-background px-4 py-2 text-3xl"
					:class="{ 'error-input' : errors.amount }"
					@input="delete errors.amount"
				>
				<span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>
			</div>
		</div>
		<div class="flex flex-col w-full mb-4 ">
			<textarea
				v-model="comments"
				placeholder="Add comments"
				rows=4
				class="w-full xl:w-3/4 rounded border border-muted-dark bg-background p-2"
				:class="{ 'error-input' : errors.amount }"
				@input="delete errors.comments"
			></textarea>
			<span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
		</div>
		<div class="flex justify-around">
			<button @click.prevent="addTransaction(withdrawalAmount)" class="w-1/3 p-3 text-lg uppercase font-bold rounded text-white capitalize bg-red-500">Withdraw</button>
			<button @click.prevent="addTransaction(amount)" class="w-1/3 p-3 text-lg uppercase font-bold rounded text-white capitalize bg-green-500">Deposit</button>
		</div>
	</div>
</template>

<script>
import { mapActions } from 'vuex'

export default {
	name: 'BankrollTransaction',
	props: ['title', 'closeBtn'],
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
	.fade-enter-active, .fade-leave-active {
		transition: background-color 0.25s ease-out;
	}

	.fade-enter, .fade-leave-to {
		background-color: 0;
	}
</style>