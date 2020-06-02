<template>
	<div 
		@click="showSessionDetails()"
		class="flex justify-between p-4 border border-muted-dark shadow bg-card hover:bg-muted-dark cursor-pointer text-white">
		<div class="flex items-center">
			<i v-if="session.game_type === 'cash_game'" class="fas fa-money-bill fa-2x mr-3"></i>
			<i v-if="session.game_type === 'tournament'" class="fas fa-trophy fa-2x mr-3"></i>
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
		showSessionDetails: function () {
			// Will need to update to viewSession to include Tournaments
			// Save CashGame in to state and then go to sessions route.
			// This is because I don't want /session/:id as the ids could be in the thousands and non consecutive because
			// they're consecutive for all users and don't want the user to type random numbers in the URL
			// even though viewing other user's is protected server side.
			this.viewCashGame(this.session.id)
			this.$router.push({
				name: 'session',
				params: {
					id: this.session.id
				}
			})
		}
	}
}
</script>

<style>

</style>