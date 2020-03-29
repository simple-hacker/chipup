<template>
	<div class="flex justify-between mb-2 p-4 border border-muted-dark shadow bg-card hover:bg-muted-dark cursor-pointer text-white">
		<div class="flex flex-col">
			<div class="uppercase">{{ session.date }}</div>
			<div class="text-sm text-gray-600">{{ session.location }}</div>
		</div>
		<div
			class="text-2xl font-bold"
			:class="(session.profit < 0) ? 'text-red-500' : 'text-green-500'"
		>
			{{ formattedProfit }}
		</div>
	</div>
</template>

<script>
export default {
	name: 'SessionSummary',
	props: {
		session: Object
	},
	computed: {
		formattedProfit() {
			if (this.session.profit < 0) {
				return '-£'+((this.session.profit * -1).toFixed(2));
			} else {
				return '£'+this.session.profit.toFixed(2);
			}
		}
	}
}
</script>

<style>

</style>