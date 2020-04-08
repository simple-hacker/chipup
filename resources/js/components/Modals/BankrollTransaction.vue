<template>
	<div class="flex flex-col justify-around h-100">
		<h1 class="text-2xl font-semibold">Manage Bankroll</h1>

		<div class="flex-1 flex mt-6 justify-center items-center">
			<span class="text-4xl font-bold mr-3">£</span>
			<input v-model="bankrollAmount" type="text" class="w-1/2 rounded border border-muted-dark bg-background px-4 py-2 text-3xl">
		</div>
		<div class="flex-1 flex mt-6 justify-center items-center">
			<textarea v-model="comments" placeholder="Add comments" class="w-2/3 rounded border border-muted-dark bg-background p-2"></textarea>
		</div>
		<div class="flex mt-6 justify-around">
			<button @click="save" class="w-1/3 p-3 text-lg uppercase font-bold rounded text-white capitalize bg-red-500">Withdraw</button>
			<button @click="save" class="w-1/3 p-3 text-lg uppercase font-bold rounded text-white capitalize bg-green-500">Deposit</button>
		</div>
	</div>
</template>

<script>
export default {
    name: 'BankrollTransactionModal',
    data() {
      return {
		bankrollAmount: 0,
		comments: ''
      }
	},
    methods: {
		save() {
			this.$emit('close');
			this.$snotify.success('Well done');
		},
    }
}
</script>

<style scoped>
	.vue-js-switch {
		font-size: 16px !important;
	}

	.fade-enter-active, .fade-leave-active {
		transition: background-color 0.25s ease-out;
	}

	.fade-enter, .fade-leave-to {
		background-color: 0;
	}
</style>