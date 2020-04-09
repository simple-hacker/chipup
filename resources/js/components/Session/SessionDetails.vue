<template>
	<div class="flex flex-col w-full relative">
		<div class="absolute top-5 right-5">
			<button @click="$emit('close')" class="hover:text-muted-light cursor-pointer">
				<i class="fas fa-times-circle fa-2x"></i>
			</button>
		</div>
		<div class="text-center text-6xl font-bold"
			:class="(session.profit > 0) ? 'text-green-500' : 'text-red-500'"
		>
			{{ formattedProfit }}
		</div>
		<div class="flex flex-col md:flex-row md:justify-around items-center text-xl">
			<div class="mb-2 md:mb-0">
				<i class="fas fa-map-marker-alt mr-3"></i><span>Aspers Casino</span>
			</div>
			<div class="mb-2 md:mb-0">
				<i class="fas fa-stream mr-3"></i><span>£1/£2 No Limit Holdem</span>
			</div>
		</div>
		<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Session Start</div>
				<div class="self-center">Saturday 15th March 22:30</div>
			</div>
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Session End</div>
				<div class="self-center">Saturday 16th March 02:45</div>
			</div>
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Session Duration</div>
				<div class="self-center">4:15</div>
			</div>
		</div>
		<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
			<div class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Buy Ins</div>
				<div class="flex flex-col self-center">
					<div>£100.00</div>
					<div>£100.00</div>
				</div>
			</div>
			<div class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Cash Out</div>
				<div class="flex flex-col self-center">
					<div>£275.85</div>
				</div>
			</div>
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Rebuys</div>
				<div class="flex flex-col self-center">
					<div>£20.00</div>
					<div>£20.00</div>
				</div>
			</div>
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Add Ons</div>
				<div class="flex flex-col self-center">
					<div>£50.00</div>
				</div>
			</div>
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-start md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Expenses</div>
				<div class="flex flex-col self-center w-full">
					<div class="flex justify-end md:justify-around">
						<div class="order-last md:order-first">£4.50</div>
						<div class="mr-3 md:mr-0">Comments</div>
					</div>
				</div>
			</div>
		</div>
		<div class="grid grid-cols-2 gap-2 md:gap-3 mt-2 md:mt-4">
			<div class="col-span-2 md:col-span-1 col-start-1 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Stats</div>
				<div class="flex flex-col border border-muted-dark rounded-lg">
					<div class="flex justify-between border border-muted-dark p-3">
						<span>Profit</span>
						<span class="text-lg font-semibold text-green-500">
							<number
								ref="stats-profit-session"
								:from="0"
								:to="254.21"
								:duration="1"
								:format="(number) => '£'+number.toLocaleString()"
								easing="Power1.easeOut"
							/>
						</span>
					</div>
					<div class="flex justify-between border border-muted-dark p-3">
						<span>Profit / hour</span>
						<span class="text-lg font-semibold text-green-500">
							<number
								ref="stats-profit-session"
								:from="0"
								:to="30.55"
								:duration="1"
								:format="(number) => '£'+number.toLocaleString()"
								easing="Power1.easeOut"
							/>
						</span>
					</div>
					<div class="flex justify-between border border-muted-dark p-3">
						<span>ROI</span>
						<span class="text-lg font-semibold text-green-500">
							<number
								ref="stats-average-roi"
								:from="0"
								:to="23.54"
								:duration="1"
								:format="(number) => number.toFixed(2)+'%'"
								easing="Power1.easeOut"
							/>
						</span>
					</div>
				</div>
			</div>
			<div class="col-span-2 md:col-span-1 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Comments</div>
				<div>Comments</div>
			</div>
		</div>
		<div class="flex md:justify-end mt-4 mb-2">
			<button @click.prevent="deleteSession" type="button" class="md:order-last mr-3 md:mr-0 bg-red-500 hover:bg-red-600 focus:bg-red-600 rounded text-white text-sm px-4 py-2"><i class="fas fa-trash mr-3"></i><span>Delete</span></button>
			<button type="button" class="md:order-first md:mr-3 bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2"><i class="fas fa-edit mr-3"></i><span>Edit</span></button>
		</div>
	</div>
</template>

<script>
export default {
	name: 'SessionDetails',
	props: {
		session: Object
	},
	computed: {
		formattedProfit() {
			return Vue.prototype.currency.format(this.session.profit);
		}
	},
	methods: {
		deleteSession: function() {
			this.$modal.show('dialog', {
				title: 'Are you sure?',
				text: 'Are you sure you want to delete this cash game?  This action cannot be undone.',
				buttons: [
					{
						title: 'Cancel'
					},
					{
						title: 'Yes, delete.',
						handler: () => { 
								this.$modal.hide('dialog');
								this.$emit('close');
								this.$snotify.warning('Successfully deleted.');
						},
						class: 'bg-red-500 text-white'
					},

				],
			})
		}
	}
}
</script>

<style>

</style>