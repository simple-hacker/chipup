<template>
	<div class="flex flex-col w-full text-white">
		<div class="text-center text-4xl lg:text-6xl font-bold text-green-500">
			Create {{ game_type_label }}
		</div>
		<div @click="switchGameType" class="text-center font-bold text-green-500 p-3 cursor-pointer">
			<span class="mr-3">Switch to {{ game_type_label_inverse }} Mode</span>
		</div>
		<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
			<div class="col-span-6 md:col-span-3 md:row-span-3 flex-col bg-card border border-muted-dark rounded-lg p-3 text-lg">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Details</div>
				<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-map-marker-alt"></i>
					</div>
					<input type="text" class="p-1 text-lg" v-model="session.location" placeholder="Location" />
				</div>
				<div v-show-slide="game_type === 'cash_game'">
					<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
						<div class="w-1/6">
							<i class="fas fa-coins"></i>
						</div>
						<select
							v-model="cash_game.stake_id"
							class="p-1 text-lg mr-1"
						>
							<option
								v-for="stake in stakes"
								:key="stake.id"
								:value="stake.id"
								v-text="stake.stake"
							>
							</option>
						</select>
					</div>
				</div>
				<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-stream"></i>
					</div>
					<div class="flex w-full">
						<select
							v-model="session.limit_id"
							class="p-1 text-lg mr-1"
						>
							<option
								v-for="limit in limits"
								:key="limit.id"
								:value="limit.id"
								v-text="limit.limit"
							>
							</option>
						</select>
						<select
							v-model="session.variant_id"
							class="p-1 text-lg"
						>
							<option
								v-for="variant in variants"
								:key="variant.id"
								:value="variant.id"
								v-text="variant.variant"
							>
							</option>
						</select>
					</div>
				</div>
				<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-user-friends"></i>
					</div>
					<select
						v-model="session.table_size_id"
						class="p-1 text-lg"
					>
						<option
							v-for="table_size in table_sizes"
							:key="table_size.id"
							:value="table_size.id"
							v-text="table_size.table_size"
						>
						</option>
					</select>
				</div>
				<div v-show-slide="game_type === 'tournament'">
					<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
						<div class="w-1/6">
							<i class="fas fa-users"></i>
						</div>
						<input type="number" min="0" class="p-1 text-lg" v-model="tournament.entries" placeholder="Number of entries" />
					</div>
				</div>
				<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
					<div class="w-1/6">
						<i class="far fa-clock"></i>
					</div>
					<datetime
						v-model="session.start_time"
						input-id="start_time"
						type="datetime"
						:minute-step="5"
						:value="'2019-12-25T11:11:11Z'"
						input-class="self-center p-1"
						auto
						placeholder="Start Date and Time"
						title="Start Date and Time"
						class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
						:class="errors.start_time ? 'border-red-700' : 'border-gray-400'"
					></datetime>
				</div>
				<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-clock"></i>
					</div>
					<datetime
						v-model="session.end_time"
						input-id="end_time"
						type="datetime"
						:minute-step="5"
						input-class="self-center p-1"
						auto
						placeholder="End Date and Time"
						title="End Date and Time"
						class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
						:class="errors.end_time ? 'border-red-700' : 'border-gray-400'"
					></datetime>
				</div>
			</div>	
			<!--
				BUY INS
			-->
			<div 
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0" v-text="(game_type === 'cash_game') ? 'Buy Ins' : 'Buy In'"></div>
				<div class="flex flex-col justify-center flex-1">
					<div v-show-slide="game_type === 'tournament'">
						<input v-model="tournament.buy_in.amount" type="text" class="p-1">
					</div>
					<div v-show-slide="game_type === 'cash_game'">
						<div
							v-for="(buy_in, index) in cash_game.buy_ins"
							:key="index"
							class="flex mb-2"
						>
							<input v-model="buy_in.amount" type="text" class="p-1">
							<button v-if="index != 0" @click="cash_game.buy_ins.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="cash_game.buy_ins.push({ amount: 0})"
								class="rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 cursor-pointer">
								<i class="fas fa-plus-circle mr-2"></i>
								<span>Add Buy In</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--
				CASH OUT
			-->
			<div
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0">Cash Out</div>
				<div class="flex flex-col justify-center flex-1">
					<div class="flex mb-2">
						<input v-model="session.cash_out_model.amount" type="text" class="p-1">
					</div>
				</div>
			</div>
			<!--
				EXPENSES
			-->
			<div
				class="col-span-6 md:col-span-3 flex md:flex-col justify-start md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0">Expenses</div>
				<div class="flex flex-col justify-center flex-1">
					<div
						v-for="(expense, index) in session.expenses"
						:key="index"
						class="flex mb-2"
					>
						<input v-model="expense.amount" type="text" class="p-1">
						<button @click="session.expenses.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
					</div>
					<div class="flex justify-center items-center">
						<div
							@click="session.expenses.push({ amount: 0})"
							class="rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 cursor-pointer">
							<i class="fas fa-plus-circle mr-2"></i>
							<span>Add Expense</span>
						</div>
					</div>
				</div>
			</div>
			<!--
				REBUYS
			-->
			<div
				v-show-slide="game_type === 'tournament'"
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg"
			>
				<div class="p-3">
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0">Rebuys</div>
					<div class="flex flex-col justify-center flex-1">
						<div
							v-for="(rebuy, index) in tournament.rebuys"
							:key="index"
							class="flex mb-2"
						>
							<input v-model="rebuy.amount" type="text" class="p-1">
							<button @click="tournament.rebuys.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="tournament.rebuys.push({ amount: 0})"
								class="rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 cursor-pointer">
								<i class="fas fa-plus-circle mr-2"></i>
								<span>Add Rebuy</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--
				ADD ONS
			-->
			<div
				v-show-slide="game_type === 'tournament'"
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg"
			>
				<div class="p-3">
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0">Add Ons</div>
					<div class="flex flex-col justify-center flex-1">
						<div
							v-for="(add_on, index) in tournament.add_ons"
							:key="index"
							class="flex mb-2"
						>
							<input v-model="add_on.amount" type="text" class="p-1">
							<button @click="tournament.add_ons.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="tournament.add_ons.push({ amount: 0})"
								class="rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 cursor-pointer">
								<i class="fas fa-plus-circle mr-2"></i>
								<span>Add Add On</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div
				class="col-span-6 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Comments</div>
				<textarea
					v-model="session.comments"
					name="comments" cols="30" rows="5"
				></textarea>
			</div>
		</div>
		<div class="flex my-3">
			<button @click.prevent="saveSession" type="button" class="w-full p-4 bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white uppercase"><i class="fas fa-edit mr-3"></i><span>Save {{ game_type_label }} </span></button>
		</div>
	</div>
</template>

<script>
import moment from 'moment'
import 'moment-duration-format'
import { mapState, mapActions } from 'vuex'

export default {
	name: 'CreateSession',
	data() {
		return {
			game_type: 'cash_game',
			session: {
				location: '',
				start_time: '',
				end_time: '',
				variant_id: 1,
				limit_id: 1,
				table_size_id: 1,
				expenses: [],
				cash_out_model: { amount: 0 },
				comments: ''
			},
			cash_game: {
				stake_id: 1,
				buy_ins: [
					{ amount: 0 },
				],
			},
			tournament: {
				entries: '',
				buy_in: { amount: 0},
				rebuys: [],
				add_ons: [],
			},
			errors: {}
		}
	},
	created() {

	},
	computed: {
		...mapState(['stakes', 'limits', 'variants', 'table_sizes']),
		game_type_label_inverse() {
			if (this.game_type === 'cash_game') {
				return 'Tournament'
			} else {
				return 'Cash Game'
			}
		},
		game_type_label() {
			if (this.game_type === 'cash_game') {
				return 'Cash Game'
			} else {
				return 'Tournament'
			}
		},
	},
	methods: {
		...mapActions('cash_games', ['addCashGame']),
		switchGameType() {
			if (this.game_type === 'cash_game') {
				this.game_type = 'tournament'
			} else {
				this.game_type = 'cash_game'
			}
		},
		saveSession() {
			if (this.game_type === 'cash_game') {
				this.addCashGame({
					...this.session,
					...this.cash_game
				})
				.then(response => {
					this.$snotify.success('Successfully created cash game');
					this.$router.push({ name: 'sessions' })
				})
				.catch(error => {
					this.$snotify.error('Error: '+error.response.data.message);
				})
			} else if (this.game_type === 'tournament') {
				console.log('TOURNAMENT')
				console.log({
					...this.session,
					...this.tournament
				})
			}
			// this.saveCashGame({
			// 	...this.session,
			// 	...this.cash_game
			// })
			// .then(response => {
			// 	this.$snotify.success('Successfully created new session');
			// })
			// .catch(error => {
			// 	this.$snotify.error('Error: '+error.response.data.message);
			// })
		},
	}
}
</script>

<style>

</style>