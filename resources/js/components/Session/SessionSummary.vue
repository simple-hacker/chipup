<template>
	<div 
		@click="showSessionDetails()"
		class="flex-1 flex justify-between items-center group p-3 md:p-4 bg-gray-500 hover:bg-gray-450 rounded border-r-4 shadow hover:shadow-2xl cursor-pointer text-white"
		:class="(session.profit < 0) ? 'border-red-500 hover:border-red-400' : 'border-green-500 hover:border-green-400'"
	>
		<i v-if="session.game_type === 'cash_game'" class="fas fa-money-bill fa-lg sm:fa-2x mr-4"></i>
		<i v-if="session.game_type === 'tournament'" class="fas fa-trophy fa-lg sm:fa-2x mr-4"></i>
		<div class="flex-col flex-1">
			<div v-text="date" class="uppercase"></div>
			<div v-text="session.location" class="text-sm tracking-wide text-gray-300 font-medium"></div>
		</div>
		<div
			class="text-lg sm:text-2xl font-bold items-center"
			:class="(session.profit < 0) ? 'text-red-500 group-hover:text-red-400' : 'text-green-500 group-hover:text-green-400'"
			v-text="$n(session.profit, { style: 'currency', currency: session.currency })"
		>
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
	},
	created() {
		console.log(this.session)
	}
}
</script>

<style>

</style>