<template>
	<div class="flex flex-col relative">
		<div v-if="closeBtn" class="absolute top-0 right-0">
			<button @click="$emit('close')" class="hover:text-muted-light cursor-pointer">
				<i class="fas fa-times-circle fa-2x"></i>
			</button>
		</div>
		<h1 v-if="title" v-text="title" class="text-2xl font-semibold mb-6"></h1>

		<div class="flex-1 flex mb-4 justify-center items-center">
			<span class="text-4xl font-bold mr-3">£</span>
			<input v-model="amount" type="number" class="w-1/2 rounded border border-muted-dark bg-background px-4 py-2 text-3xl">
		</div>
		<div class="flex-1 flex mb-6 justify-center items-center">
			<textarea v-model="comments" placeholder="Add comments" rows=4 class="w-full xl:w-3/4 rounded border border-muted-dark bg-background p-2"></textarea>
		</div>
		<div class="flex justify-around">
			<button @click.prevent="addTransaction(amount)" class="w-1/3 p-3 text-lg uppercase font-bold rounded text-white capitalize bg-red-500">Withdraw</button>
			<button @click.prevent="addTransaction(withdrawalAmount)" class="w-1/3 p-3 text-lg uppercase font-bold rounded text-white capitalize bg-green-500">Deposit</button>
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
		comments: ''
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
				amount: amount
			})
			.then((res) =>{
				this.$emit('close');
				if (amount < 0) {
					this.$snotify.success(`Withdrew £`+(amount * -1)+' from your bankroll.');
				} else {
					this.$snotify.success(`Withdrew £`+amount+' from your bankroll.');
				}
			})
			.catch((err) => {
				console.log(err)
				this.$snotify.error(`Something went wrong.  Please try again.`);
			})
		},
		...mapActions(['addBankrollTransaction'])
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