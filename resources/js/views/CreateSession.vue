<template>
	<div class="flex flex-col w-full xxl:w-2/3 xxl:mx-auto text-white">
		<div class="text-center text-4xl lg:text-6xl font-bold text-green-500">
			Create {{ game_type_label }}
		</div>
		<div @click="switchGameType" class="text-center font-bold text-green-500 p-3 cursor-pointer">
			<span class="mr-3">Switch to {{ game_type_label_inverse }} Mode</span>
		</div>
		<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
			<div class="col-span-6 md:col-span-3 md:row-span-3 flex-col bg-card border border-muted-dark rounded-lg p-3 text-lg">
				<!--
					DETAILS
				-->
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Details</div>
				<!--
					LOCATION
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-map-marker-alt"></i>
					</div>
					<div class="flex flex-col w-full">
						<input
							type="text"
							v-model="session.location"
							placeholder="Location"
							class="p-1 text-lg"
							:class="{'error-input' : errors.location}"
							@input="delete errors.location"
						/>
						<span v-if="errors.location" class="error-message">{{ errors.location[0] }}</span>
					</div>
				</div>
				<!--
					STAKE
				-->
				<div v-show-slide="game_type === 'cash_game'">
					<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
						<div class="w-1/6">
							<i class="fas fa-coins"></i>
						</div>
						<div class="flex flex-col w-full">
							<select
								v-model="cash_game.stake_id"
								class="p-1 text-lg mr-1"
								:class="{'error-input' : errors.stake_id}"
								@input="delete errors.stake_id"
							>
								<option
									v-for="stake in stakes"
									:key="stake.id"
									:value="stake.id"
									v-text="stake.stake"
								>
								</option>
							</select>
							<span v-if="errors.stake_id" class="error-message">{{ errors.stake_id[0] }}</span>
						</div>
					</div>
				</div>
				<!--
					LIMIT AND VARIANT
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-stream"></i>
					</div>
					<div class="flex w-full">
						<div class="flex flex-col w-full">
							<select
								v-model="session.limit_id"
								class="p-1 text-lg mr-1"
								:class="{'error-input' : errors.limit_id}"
								@input="delete errors.limit_id"
							>
								<option
									v-for="limit in limits"
									:key="limit.id"
									:value="limit.id"
									v-text="limit.limit"
								>
								</option>
							</select>
							<span v-if="errors.limit_id" class="error-message">{{ errors.limit_id[0] }}</span>
						</div>
						<div class="flex flex-col w-full">
							<select
								v-model="session.variant_id"
								class="p-1 text-lg"
								:class="{'error-input' : errors.variant_id}"
								@input="delete errors.variant_id"
							>
								<option
									v-for="variant in variants"
									:key="variant.id"
									:value="variant.id"
									v-text="variant.variant"
								>
								</option>
							</select>
							<span v-if="errors.variant_id" class="error-message">{{ errors.variant_id[0] }}</span>
						</div>
					</div>
				</div>
				<!--
					TABLE SIZE
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-user-friends"></i>
					</div>
					<div class="flex flex-col w-full">
						<select
							v-model="session.table_size_id"
							class="p-1 text-lg"
							:class="{'error-input' : errors.table_size_id}"
							@input="delete errors.table_size_id"
						>
							<option
								v-for="table_size in table_sizes"
								:key="table_size.id"
								:value="table_size.id"
								v-text="table_size.table_size"
							>
							</option>
						</select>
						<span v-if="errors.table_size_id" class="error-message">{{ errors.table_size_id[0] }}</span>
					</div>
				</div>
				<!--
					ENTRIES
				-->
				<div v-show-slide="game_type === 'tournament'">
					<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
						<div class="w-1/6">
							<i class="fas fa-users"></i>
						</div>
						<div class="flex flex-col w-full">
							<input
								type="number"
								min="0"
								placeholder="Number of entries"
								v-model="tournament.entries"
								class="p-1 text-lg"
								:class="{'error-input' : errors.entries}"
								@input="delete errors.entries"
							/>
							<span v-if="errors.entries" class="error-message">{{ errors.entries[0] }}</span>
						</div>
					</div>
				</div>
				<!--
					START TIME
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="far fa-clock"></i>
					</div>
					<div class="flex flex-col w-full">
						<datetime
							v-model="session.start_time"
							input-id="start_time"
							type="datetime"
							:minute-step="5"
							auto
							placeholder="Start Date and Time"
							title="Start Date and Time"
							class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
							:input-class="{'error-input' : errors.start_time, 'p-1' : true}"
							@input="delete errors.start_time"
						></datetime>
						<span v-if="errors.start_time" class="error-message">{{ errors.start_time[0] }}</span>
					</div>
				</div>
				<!--
					END TIME
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-clock"></i>
					</div>
					<div class="flex flex-col w-full">
						<datetime
							v-model="session.end_time"
							input-id="end_time"
							type="datetime"
							:minute-step="5"
							auto
							placeholder="End Date and Time"
							title="End Date and Time"
							class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
							:input-class="{'error-input' : errors.end_time, 'p-1' : true}"
							@input="delete errors.end_time"
						></datetime>
						<span v-if="errors.end_time" class="error-message">{{ errors.end_time[0] }}</span>
					</div>
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
						<input	
							v-model="tournament.buy_in.amount"
							type="number"
							min="0"
							class="p-1"
							:class="{'error-input' : errors[`buy_in.amount`]}"
							@input="delete errors[`buy_in.amount`]"
						>
						<span v-if="errors[`buy_in.amount`]" class="error-message">{{ errors[`buy_in.amount`][0] }}</span>
					</div>
					<div v-show-slide="game_type === 'cash_game'">
						<div
							v-for="(buy_in, index) in cash_game.buy_ins"
							:key="index"
							class="flex mb-2"
						>
							<div class="flex flex-col w-full">
								<div class="flex">
									<input
										v-model="buy_in.amount"
										type="number"
										min="0"
										class="p-1"
										:class="{'error-input' : errors[`buy_ins.${index}.amount`]}"
										@input="delete errors[`buy_ins.${index}.amount`]"
									>
									<button v-if="index != 0" @click="cash_game.buy_ins.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
								</div>
								<span v-if="errors[`buy_ins.${index}.amount`]" class="error-message">{{ errors[`buy_ins.${index}.amount`][0] }}</span>
							</div>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="cash_game.buy_ins.push({ amount: 0})"
								class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
							>
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
					<div class="flex flex-col mb-2">
						<input
							v-model="session.cash_out_model.amount"
							type="number"
							min="0"
							class="p-1"
							:class="{'error-input' : errors['cash_out_model.amount']}"
							@input="delete errors['cash_out_model.amount']"
						>
						<span v-if="errors['cash_out_model.amount']" class="error-message">{{ errors['cash_out_model.amount'][0] }}</span>
					</div>
				</div>
			</div>
			<!--
				EXPENSES
			-->
			<div
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0">Expenses</div>
				<div class="flex flex-col justify-center flex-1">
					<div
						v-for="(expense, index) in session.expenses"
						:key="index"
						class="flex mb-2"
					>
						<div class="flex flex-col w-full">
							<div class="flex">
								<input
									v-model="expense.amount"
									type="number"
									min="0"
									class="p-1"
									:class="{'error-input' : errors[`expenses.${index}.amount`]}"
									@input="delete errors[`expenses.${index}.amount`]"
								>
								<input
									v-model="expense.comments"
									type="text"
									class="p-1 ml-1"
									placeholder="Comments"
									:class="{'error-input' : errors[`expenses.${index}.comments`]}"
									@input="delete errors[`expenses.${index}.comments`]"
								>
								<button @click="session.expenses.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
							</div>
							<span v-if="errors[`expenses.${index}.amount`]" class="error-message">{{ errors[`expenses.${index}.amount`][0] }}</span>
							<span v-if="errors[`expenses.${index}.comments`]" class="error-message">{{ errors[`expenses.${index}.comments`][0] }}</span>
						</div>
					</div>
					<div class="flex justify-center items-center">
						<div
							@click="session.expenses.push({ amount: 0, comments: ''})"
							class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
						>
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
				v-if="game_type === 'tournament'"
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
			>
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0">Rebuys</div>
				<div class="flex flex-col justify-center flex-1">
					<div
						v-for="(rebuy, index) in tournament.rebuys"
						:key="index"
						class="flex mb-2"
					>
						<div class="flex flex-col w-full">
							<div class="flex">
								<input
									v-model="rebuy.amount"
									type="number"
									min="0"
									class="p-1"
									:class="{'error-input' : errors[`rebuys.${index}.amount`]}"
									@input="delete errors[`rebuys.${index}.amount`]"
								>
								<button @click="tournament.rebuys.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
							</div>
							<span v-if="errors[`rebuys.${index}.amount`]" class="error-message">{{ errors[`rebuys.${index}.amount`][0] }}</span>
						</div>
					</div>
					<div class="flex justify-center items-center">
						<div
							@click="tournament.rebuys.push({ amount: 0})"
							class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
						>
							<i class="fas fa-plus-circle mr-2"></i>
							<span>Add Rebuy</span>
						</div>
					</div>
				</div>
			</div>
			<!--
				ADD ONS
			-->
			<div
				v-if="game_type === 'tournament'"
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
			>
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2 w-1/4 md:w-auto mr-2 md:mr-0">Add Ons</div>
				<div class="flex flex-col justify-center flex-1">
					<div
						v-for="(add_on, index) in tournament.add_ons"
						:key="index"
						class="flex mb-2"
					>
						<div class="flex flex-col w-full">
							<div class="flex">
								<input
									v-model="add_on.amount"
									type="number"
									min="0"
									class="p-1"
									:class="{'error-input' : errors[`add_ons.${index}.amount`]}"
									@input="delete errors[`add_ons.${index}.amount`]"
								>
								<button @click="tournament.add_ons.splice(index, 1)" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="fas fa-times"></i></button>
							</div>
							<span v-if="errors[`add_ons.${index}.amount`]" class="error-message">{{ errors[`add_ons.${index}.amount`][0] }}</span>
						</div>
					</div>
					<div class="flex justify-center items-center">
						<div
							@click="tournament.add_ons.push({ amount: 0})"
							class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
						>
							<i class="fas fa-plus-circle mr-2"></i>
							<span>Add Add On</span>
						</div>
					</div>
				</div>
			</div>
			<!---
				COMMENTS
			-->
			<div
				class="col-span-6 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Comments</div>
				<textarea
					v-model="session.comments"
					name="comments" cols="30" rows="5"
					:class="{'error-input' : errors.comments}"
					@input="delete errors.comments"
				></textarea>
				<span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
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
				variant_id: 0,
				limit_id: 0,
				table_size_id: 0,
				expenses: [],
				cash_out_model: { amount: 0 },
				comments: ''
			},
			cash_game: {
				stake_id: 0,
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
					this.$snotify.success('Successfully created cash game')
					// this.$router.push({ name: 'sessions' })
				})
				.catch(error => {
					this.$snotify.error('Error: '+error.response.data.message)
					this.errors = error.response.data.errors
				})
			} else if (this.game_type === 'tournament') {
				console.log('TOURNAMENT')
				console.log({
					...this.session,
					...this.tournament
				})
			}
		},
	}
}
</script>

<style>

</style>