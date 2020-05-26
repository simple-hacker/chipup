<template>
	<div class="flex flex-col xxl:w-2/3 xxl:mx-auto w-full text-white">
		<div class="text-center text-6xl font-bold"
			:class="(stateCashGame.profit > 0) ? 'text-green-500' : 'text-red-500'"
		>
			{{ formattedProfit }}
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
					<div class="w-full">
						<span v-if="!editing" v-text="stateCashGame.location"></span>
						<div v-if="editing" class="flex flex-col">
							<input
								type="text"
								v-model="cash_game.location"
								placeholder="Location"
								class="p-1 text-lg"
								:class="{'error-input' : errors.location}"
								@input="delete errors.location"
							/>
							<span v-if="errors.location" class="error-message">{{ errors.location[0] }}</span>
						</div>						
					</div>
				</div>
				<!--
					STAKE
				-->
				<!-- <div v-show-slide="game_type === 'cash_game'"> -->
					<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
						<div class="w-1/6">
							<i class="fas fa-coins"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="stateCashGame.stake.stake"></span>
							<div v-if="editing" class="flex flex-col">
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
				<!-- </div> -->
				<!--
					LIMIT AND VARIANT
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-stream"></i>
					</div>
					<div class="w-full">
						<span v-if="!editing" v-text="`${stateCashGame.limit.limit} ${stateCashGame.variant.variant}`"></span>
						<div v-if="editing" class="flex w-full">
							<div class="flex flex-1 flex-col">
								<select
									v-model="cash_game.limit_id"
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
							<div class="flex flex-1 flex-col">
								<select
									v-model="cash_game.variant_id"
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
				</div>
				<!--
					TABLE SIZE
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-user-friends"></i>
					</div>
					<div class="w-full">
						<span v-if="!editing" v-text="stateCashGame.table_size.table_size"></span>
						<div v-if="editing" class="flex flex-col">
							<select
								v-model="cash_game.table_size_id"
								class="p-1 text-lg mr-1"
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
				</div>
				<!--
					ENTRIES
				-->
				<!--
					START TIME
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="far fa-clock"></i>
					</div>
					<div class="w-full">
						<span v-if="!editing" v-text="formatDate(stateCashGame.start_time)"></span>
						<div v-if="editing" class="flex flex-col">
							<datetime
								v-model="cash_game.start_time"
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
				</div>
				<!--
					END TIME
				-->
				<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
					<div class="w-1/6">
						<i class="fas fa-clock"></i>
					</div>
					<div class="w-full">
						<span v-if="!editing" v-text="formatDate(stateCashGame.end_time)"></span>
						<div v-if="editing" class="flex flex-col">
							<datetime
								v-model="cash_game.end_time"
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
			</div>	
			<!--
				BUY INS
			-->
			<div
				v-if="(stateCashGame.buy_ins && stateCashGame.buy_ins.length > 0) || editing" 
				class="col-span-6 md:col-span-3 flex flex-col order-3 md:order-2 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
			>
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Buy Ins</div>
				<div
					v-for="buy_in in stateCashGame.buy_ins"
					:key="buy_in.id"
					class="mb-1"
				>
					<transaction-summary :transaction="buy_in" :transaction-type="'buyin'" :game-id="stateCashGame.id"></transaction-summary>
				</div>
				<div
					v-if="editing"
				>
					<div
						@click="addTransaction('buyin', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Buy In</span>
					</div>
				</div>
			</div>
			<!--
				CASH OUT
			-->
			<div
				v-if="stateCashGame.cash_out_model || editing"
				class="col-span-6 md:col-span-3 flex flex-col order-4 md:order-3 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
			>
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Cash Out</div>
				<transaction-summary v-if="stateCashGame.cash_out_model" :transaction="stateCashGame.cash_out_model" :transaction-type="'cashout'" :game-id="stateCashGame.id"></transaction-summary>
				<div
					v-if="editing && !stateCashGame.cash_out_model"
				>
					<div
						@click="addTransaction('cashout', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Cash Out</span>
					</div>
				</div>
			</div>
			<!--
				EXPENSES
			-->
			<div
				v-if="(stateCashGame.expenses && stateCashGame.expenses.length > 0) || editing" 
				class="col-span-6 md:col-span-3 flex flex-col order-5 md:order-4 justify-start md:justify-start bg-card border border-muted-dark rounded-lg p-3"
			>
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Expenses</div>
				<div
					v-for="expense in stateCashGame.expenses"
					:key="expense.id"
					class="mb-1"
				>
					<transaction-summary :transaction="expense" :transaction-type="'expense'" :game-id="stateCashGame.id"></transaction-summary>
				</div>
				<div
					v-if="editing"
				>
					<div
						@click="addTransaction('expense', { amount: 0, comments: '' })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Expense</span>
					</div>
				</div>
			</div>
			<!--
				REBUYS
			-->
			<div
				v-if="(stateCashGame.rebuys && stateCashGame.rebuys.length > 0) || editing" 
				class="col-span-6 md:col-span-3 flex flex-col order-6 md:order-5 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
			>
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Rebuys</div>
				<div
					v-for="rebuy in stateCashGame.rebuys"
					:key="rebuy.id"
					class="mb-1"
				>
					<transaction-summary :transaction="rebuy" :transaction-type="'rebuy'" :game-id="stateCashGame.id"></transaction-summary>
				</div>
				<div
					v-if="editing"
				>
					<div
						@click="addTransaction('rebuy', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Rebuy</span>
					</div>
				</div>
			</div>
			<!--
				ADD ONS
			-->
			<div
				v-if="(stateCashGame.add_ons && stateCashGame.add_ons.length > 0) || editing" 
				class="col-span-6 md:col-span-3 flex flex-col order-7 md:order-6 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
			>
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Add Ons</div>
				<div
					v-for="add_on in stateCashGame.add_ons"
					:key="add_on.id"
					class="mb-1"
				>
					<transaction-summary :transaction="add_on" :transaction-type="'addon'" :game-id="stateCashGame.id"></transaction-summary>
				</div>
				<div
					v-if="editing"
				>
					<div
						@click="addTransaction('addon', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Add On</span>
					</div>
				</div>
			</div>
			<!--
				STATS
			-->
			<div class="col-span-6 md:col-span-3 md:row-span-1 order-2 md:order-7 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Stats</div>
				<div class="flex flex-col border border-muted-dark rounded-lg">
					<div class="flex justify-between border border-muted-dark p-3">
						<span>Duration</span>
						<span class="text-lg font-semibold text-green-500" v-text="runTime">
						</span>
					</div>
					<div class="flex justify-between border border-muted-dark p-3">
						<span>Profit</span>
						<span class="text-lg font-semibold text-green-500">
							<number
								ref="stats-profit-session"
								:from="0"
								:to="profit"
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
								:to="profitPerHour"
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
								:to="roi"
								:duration="1"
								:format="(number) => number.toFixed(2)+'%'"
								easing="Power1.easeOut"
							/>
						</span>
					</div>
				</div>
			</div>
			<!--
				COMMENTS
			-->
			<div
				v-if="cash_game.comments  || editing"
				class="col-span-6 md:col-span-3 row-span-1 flex flex-col order-8 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Comments</div>
				<div
					v-if="!editing"
					v-text="cash_game.comments"
				></div>
				<textarea
					v-if="editing"
					v-model="cash_game.comments"
					name="comments" cols="30" rows="5"
					:class="{'error-input' : errors.comments}"
					@input="delete errors.comments"
				></textarea>
				<span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
			</div>
		</div>
		<div class="flex my-3">
			<button v-if="!editing" @click.prevent="editing = true" type="button" class="bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2 mr-3"><i class="fas fa-edit mr-3"></i><span>Edit</span></button>
			<div v-if="editing">
				<button @click.prevent="saveSession" type="button" class="bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2 mr-3"><i class="fas fa-edit mr-3"></i><span>Save Changes</span></button>
			</div>
			<button @click.prevent="deleteSession" type="button" class="bg-red-500 hover:bg-red-600 focus:bg-red-600 rounded text-white text-sm px-4 py-2"><i class="fas fa-trash"></i></button>
		</div>
	</div>
</template>

<script>
import moment from 'moment'
import 'moment-duration-format'
import { mapState, mapActions, mapGetters } from 'vuex'

import TransactionSummary from '@components/Transaction/TransactionSummary'
import TransactionDetails from '@components/Transaction/TransactionDetails'

export default {
	name: 'Session',
	components: { TransactionSummary, TransactionDetails },
	props: {
		id: Number
	},
	data() {
		return {
			cash_game: {},
			editing: false,
			errors: {}
		}
	},
	created() {
		// If no id provided or user visits /session then redirect to sessions
		if (! this.id) {
			this.$router.push({ name: 'sessions' })
		} else {
			this.cash_game = {
					id: this.stateCashGame.id,
					location: this.stateCashGame.location,
					stake_id: this.stateCashGame.stake_id,
					limit_id: this.stateCashGame.limit_id,
					variant_id: this.stateCashGame.variant_id,
					table_size_id: this.stateCashGame.table_size_id,
					start_time: moment(this.stateCashGame.start_time).format(),
					end_time: moment(this.stateCashGame.end_time).format(),
					comments: this.stateCashGame.comments,
			}
		}
	},
	computed: {
		...mapState(['stakes', 'limits', 'variants', 'table_sizes']),
		...mapGetters('cash_games', ['getCashGameById']),
		stateCashGame() {
			return this.getCashGameById(this.id)
		},
		profit() {
			return this.stateCashGame.profit
		},
		formattedProfit() {
			return Vue.prototype.currency.format(this.profit);
		},
		roi() {
			const buy_inTotal = (this.buy_insTotal < 1) ? 1 : this.buy_insTotal
			return this.profit / buy_inTotal
		},
		buy_insTotal() {
			return this.stateCashGame.buy_ins.reduce((total, buy_in) => total + buy_in.amount, 0)
		},
		runTimeHours() {
			const end_time = moment(this.stateCashGame.end_time)
			const start_time = moment(this.stateCashGame.start_time)
			return end_time.diff(start_time, 'hours', true)
		},
		runTime() {
			const end_time = moment(this.stateCashGame.end_time)
			const start_time = moment(this.stateCashGame.start_time)
			return moment.duration(this.runTimeHours, 'hours').format("h [hours] m [mins]")
		},
		profitPerHour() {
			return (this.profit / this.runTimeHours).toFixed(2)
		}
	},
	methods: {
		...mapActions('cash_games', ['updateCashGame', 'deleteCashGame']),
		cancelChanges() {
			this.editing = false
			this.cash_game = this.stringifyCashGame(this.stateCashGame)
		},
		saveSession() {
			this.updateCashGame(this.cash_game)
			.then(response => {
				this.$snotify.success('Changes saved.')
				this.editing = false
				this.cash_game = this.stringifyCashGame(this.stateCashGame)
				// TODO: ^^ Edit line above
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors
			})
		},
		deleteSession() {
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
                            this.deleteCashGame(this.cash_game)
                            .then(response => {
								this.$modal.hide('dialog')
								this.$router.push({ name: 'sessions' })
                                this.$snotify.warning('Successfully deleted cash game.')
                            })
                            .catch(error => {
                                this.$snotify.error('Error: '+error.response.data.message)
                            })
						},
						class: 'bg-red-500 text-white'
					},

				],
			})
        },
		formatCurrency(amount) {
			return Vue.prototype.currency.format(amount)
		},
		formatDate(date) {
			return moment(date).format("dddd Do MMMM, HH:mm")
		},
		stringifyCashGame(cash_game) {
			return JSON.parse(JSON.stringify({
				...this.cash_game,
				start_time: moment(this.cash_game.start_time).format(),
				end_time: moment(this.cash_game.end_time).format()
			}))
		},
		addTransaction(transactionType, transaction) {
			this.$modal.show(TransactionDetails, {
                // Modal props
                transaction,
				transactionType,
				gameId: this.stateCashGame.id,
                gameType: 'cash_game'
            }, {
                // Modal Options
                classes: 'bg-background text-white p-1 md:p-3 rounded-lg border border-muted-dark',
                height: 'auto',
                width: '95%',
                maxWidth: 600,
            })
		}
	}
}
</script>

<style>

</style>