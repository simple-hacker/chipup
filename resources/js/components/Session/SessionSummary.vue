<template>
	<div 
		@click="showSessionDetails()"
		class="flex justify-between items-center p-3 md:p-4 bg-gray-500 hover:bg-gray-450 rounded border-r-4 shadow hover:shadow-2xl cursor-pointer text-white"
		:class="(session.profit < 0) ? 'border-red-500' : 'border-green-500'"
	>
			<i v-if="session.game_type === 'cash_game'" class="fas fa-money-bill fa-lg sm:fa-2x mr-4"></i>
			<i v-if="session.game_type === 'tournament'" class="fas fa-trophy fa-lg sm:fa-2x mr-4"></i>
			<div class="flex-col flex-1">
				<div class="uppercase">{{ date }}</div>
				<div class="text-sm tracking-wide text-gray-300 font-medium">{{ session.location }}</div>
			</div>
			<div
				class="text-lg sm:text-2xl font-bold items-center"
				:class="(session.profit < 0) ? 'text-red-500' : 'text-green-500'"
			>
				{{ formattedProfit }}
			</div>
	</div>
</template>

<script>
import moment from 'moment'
import { mapActions } from 'vuex'

export default {
	name: 'SessionSummary',
	props: {
		session: Object
	},
	computed: {
		profit() {
			return this.session.profit
		},
		formattedProfit() {
			return this.$currency.format(this.profit)
		},
		date() {
			return moment(this.session.start_time).format("dddd, Do MMMM YYYY")
		}
	},
	methods: {
		...mapActions('cash_games', ['viewCashGame']),
		...mapActions('tournaments', ['viewTournament']),
		showSessionDetails: function () {
			this.$router.push({
				name: 'session',
				params: {
					viewSession: {
						id: this.session.id,
						game_type: this.session.game_type
					}
				}
			})
		}
	}
}
</script>

<style>

</style>