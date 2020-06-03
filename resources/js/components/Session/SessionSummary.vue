<template>
	<div 
		@click="showSessionDetails()"
		class="flex justify-between p-4 border border-muted-dark shadow bg-card hover:bg-muted-dark cursor-pointer text-white">
		<div class="flex items-center">
			<i v-if="session.game_type === 'cash_game'" class="fas fa-money-bill fa-2x mr-4"></i>
			<i v-if="session.game_type === 'tournament'" class="fas fa-trophy fa-2x mr-4"></i>
			<div class="flex-col">
				<div class="uppercase">{{ date }}</div>
				<div class="text-sm text-gray-600">{{ session.location }}</div>
			</div>
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
			return Vue.prototype.currency.format(this.profit)
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