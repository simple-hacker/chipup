<template>
	<div class="flex flex-col xxl:w-2/3 xxl:mx-auto w-full h-full text-white">
		<div class="flex-1">	
			<div class="text-center text-6xl font-bold"
				:class="(profit < 0) ? 'text-red-500' : 'text-green-500'"
			>
				{{ formattedProfit }}
			</div>
			<div class="text-center text-lg font-bold">
				in for {{ formatCurrency(buyInsTotal) }} and out for {{ cashOutTotal }}
			</div>
			<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
				<div class="col-span-6 md:col-span-3 md:row-span-3 flex-col bg-card border border-muted-dark rounded-lg p-3 text-lg">
					<!--
						DETAILS
					-->
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Details</div>
					<!--
						TOURNAMENT NAME
					-->
					<div
						v-if="session.name || (session.game_type === 'tournament' && editing)"
						class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
						<div class="w-1/6">
							<i class="fas fa-star"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.name"></span>
							<div v-if="editing" class="flex flex-col">
								<input
									type="text"
									v-model="editSession.name"
									placeholder="Tournament name"
									class="p-1 text-lg"
									:class="{'error-input' : errors.name}"
									@input="delete errors.name"
								/>
								<span v-if="errors.name" class="error-message">{{ errors.name[0] }}</span>
							</div>						
						</div>
					</div>
					<!--
						LOCATION
					-->
					<div class="mb-2 md:mb-0 flex items-start p-0 md:p-2">
						<div class="w-1/6">
							<i class="fas fa-map-marker-alt"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.location"></span>
							<div v-if="editing" class="flex flex-col">
								<input
									type="text"
									v-model="editSession.location"
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
					<div
						v-if="session.stake"
						class="mb-2 md:mb-0 flex items-start p-0 md:p-2"
					>
						<div class="w-1/6">
							<i class="fas fa-coins"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.stake.stake"></span>
							<div v-if="editing" class="flex flex-col">
								<select
									v-model="editSession.stake_id"
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
						<div class="w-full">
							<span v-if="!editing" v-text="`${session.limit.limit} ${session.variant.variant}`"></span>
							<div v-if="editing" class="flex w-full">
								<div class="flex flex-1 flex-col">
									<select
										v-model="editSession.limit_id"
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
										v-model="editSession.variant_id"
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
					<div
						v-if="session.table_size"
						class="mb-2 md:mb-0 flex items-start p-0 md:p-2"
					>
						<div class="w-1/6">
							<i class="fas fa-user-friends"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.table_size.table_size"></span>
							<div v-if="editing" class="flex flex-col">
								<select
									v-model="editSession.table_size_id"
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
						PRIZE POOL
					-->
					<div
						v-if="session.game_type === 'tournament'"
						class="mb-2 md:mb-0 flex items-start p-0 md:p-2"
					>
						<div class="w-1/6">
							<i class="fas fa-money-bill-wave"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="`£${session.prize_pool} prize pool`"></span>
							<div v-if="editing" class="flex flex-col">
								<input
									type="number"
									min=0
									step=1
									v-model="editSession.prize_pool"
									placeholder="Prize Pool"
									class="p-1 text-lg"
									:class="{'error-input' : errors.prize_pool}"
									@input="delete errors.prize_pool"
								/>
								<span v-if="errors.prize_pool" class="error-message">{{ errors.prize_pool[0] }}</span>
							</div>						
						</div>
					</div>
					<!--
						POSITION
					-->
					<div
						v-if="session.game_type === 'tournament'"
						class="mb-2 md:mb-0 flex items-start p-0 md:p-2"
					>
						<div class="w-1/6">
							<i class="fas fa-medal"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.position"></span>
							<div v-if="editing" class="flex flex-col">
								<input
									type="number"
									min=0
									step=1
									v-model="editSession.position"
									placeholder="Finishing Position"
									class="p-1 text-lg"
									:class="{'error-input' : errors.position}"
									@input="delete errors.position"
								/>
								<span v-if="errors.position" class="error-message">{{ errors.position[0] }}</span>
							</div>						
						</div>
					</div>
					<!--
						ENTRIES
					-->
					<div
						v-if="session.game_type === 'tournament'"
						class="mb-2 md:mb-0 flex items-start p-0 md:p-2"
					>
						<div class="w-1/6">
							<i class="fas fa-users"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="`${session.entries} entries`"></span>
							<div v-if="editing" class="flex flex-col">
								<input
									type="number"
									min=0
									step=1
									v-model="editSession.entries"
									placeholder="Number of Entries"
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
						<div class="w-full">
							<span v-if="!editing" v-text="formatDate(session.start_time)"></span>
							<div v-if="editing" class="flex flex-col">
								<datetime
									v-model="editSession.start_time"
									input-id="start_time"
									type="datetime"
									:minute-step="5"
									:max-datetime="maxDateTime"
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
							<span v-if="!editing" v-text="formatDate(session.end_time)"></span>
							<div v-if="editing" class="flex flex-col">
								<datetime
									v-model="editSession.end_time"
									input-id="end_time"
									type="datetime"
									:minute-step="5"
									:max-datetime="maxDateTime"
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
					CASH BUY INS
				-->
				<div
					v-if="session.game_type === 'cash_game' && ((session.buy_ins && session.buy_ins.length > 0) || editing)"
					class="col-span-6 md:col-span-3 flex flex-col order-3 md:order-2 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
				>
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Buy Ins</div>
					<div
						v-for="buy_in in session.buy_ins"
						:key="buy_in.id"
						class="mb-1"
					>
						<transaction-summary :transaction="buy_in" :transaction-type="'buyin'" :game-id="session.id"></transaction-summary>
					</div>
					<div
						v-if="editing"
						@click="addTransaction('buyin', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Buy In</span>
					</div>
				</div>
				<!-- 
					TOURNAMENT BUY IN
				-->
				<div
					v-if="session.game_type === 'tournament' && (session.buy_in || editing)" 
					class="col-span-6 md:col-span-3 flex flex-col order-3 md:order-2 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
				>
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Buy In</div>
					<transaction-summary v-if="session.buy_in" :transaction="session.buy_in" :transaction-type="'buyin'" :game-id="session.id"></transaction-summary>
					<div
						v-if="editing && !session.buy_in"
						@click="addTransaction('buyin', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Buy In</span>
					</div>
				</div>
				<!--
					CASH OUT
				-->
				<div
					v-if="session.cash_out || editing"
					class="col-span-6 md:col-span-3 flex flex-col order-4 md:order-3 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
				>
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Cash Out</div>
					<transaction-summary v-if="session.cash_out" :transaction="session.cash_out" :transaction-type="'cashout'" :game-id="session.id"></transaction-summary>
					<div
						v-if="editing && !session.cash_out"
						@click="addTransaction('cashout', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Cash Out</span>
					</div>
				</div>
				<!--
					EXPENSES
				-->
				<div
					v-if="(session.expenses && session.expenses.length > 0) || editing" 
					class="col-span-6 md:col-span-3 flex flex-col order-5 md:order-4 justify-start md:justify-start bg-card border border-muted-dark rounded-lg p-3"
				>
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Expenses</div>
					<div
						v-for="expense in session.expenses"
						:key="expense.id"
						class="mb-1"
					>
						<transaction-summary :transaction="expense" :transaction-type="'expense'" :game-id="session.id"></transaction-summary>
					</div>
					<div
						v-if="editing"
						@click="addTransaction('expense', { amount: 0, comments: '' })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Expense</span>
					</div>
				</div>
				<!--
					REBUYS
				-->
				<div
					v-if="session.game_type === 'tournament' && ((session.rebuys && session.rebuys.length > 0) || editing)" 
					class="col-span-6 md:col-span-3 flex flex-col order-6 md:order-5 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
				>
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Rebuys</div>
					<div
						v-for="rebuy in session.rebuys"
						:key="rebuy.id"
						class="mb-1"
					>
						<transaction-summary :transaction="rebuy" :transaction-type="'rebuy'" :game-id="session.id"></transaction-summary>
					</div>
					<div
						v-if="editing"
						@click="addTransaction('rebuy', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Rebuy</span>
					</div>
				</div>
				<!--
					ADD ONS
				-->
				<div
					v-if="session.game_type === 'tournament' && ((session.add_ons && session.add_ons.length > 0) || editing)" 
					class="col-span-6 md:col-span-3 flex flex-col order-7 md:order-6 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3"
				>
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-2">Add Ons</div>
					<div
						v-for="add_on in session.add_ons"
						:key="add_on.id"
						class="mb-1"
					>
						<transaction-summary :transaction="add_on" :transaction-type="'addon'" :game-id="session.id"></transaction-summary>
					</div>
					<div
						v-if="editing"
						@click="addTransaction('addon', { amount: 0 })"
						class="w-full rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2 md:p-3 cursor-pointer text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Add On</span>
					</div>
				</div>
			</div>
			<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
				<!--
					STATS
				-->
				<div class="col-span-6 md:col-span-3 md:row-span-1 order-2 md:order-7 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Stats</div>
					<div class="flex flex-col border border-muted-dark rounded-lg">
						<div class="flex justify-between border border-muted-dark p-3">
							<span>Duration</span>
							<span class="text-lg font-semibold">
								<number
									ref="stats-profit-session"
									:from="0"
									:to="duration"
									:duration="2"
									:format="(amount) => formatDuration(amount)"
									easing="Power1.easeOut"
								/>
							</span>
						</div>
						<div class="flex justify-between border border-muted-dark p-3">
							<span>Profit</span>
							<span
								class="text-lg font-semibold"
								:class="(profit < 0) ? 'text-red-500' : 'text-green-500'"
							>
								<number
									ref="stats-profit"
									:from="0"
									:to="profit"
									:duration="2"
									:format="(amount) => formatCurrency(amount)"
									easing="Power1.easeOut"
								/>
							</span>
						</div>
						<div class="flex justify-between border border-muted-dark p-3">
							<span>Profit / hour</span>
							<span
								class="text-lg font-semibold"
								:class="(profitPerHour < 0) ? 'text-red-500' : 'text-green-500'"
							>
								<number
									ref="stats-profit-hour"
									:from="0"
									:to="profitPerHour"
									:duration="2"
									:format="(amount) => formatCurrency(amount)"
									easing="Power1.easeOut"
								/>
							</span>
						</div>
						<div class="flex justify-between border border-muted-dark p-3">
							<span>ROI</span>
							<span
								class="text-lg font-semibold"
								:class="(roi < 0) ? 'text-red-500' : 'text-green-500'"
							>
								<number
									ref="stats-average-roi"
									:from="0"
									:to="roi"
									:duration="2"
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
					v-if="session.comments  || editing"
					class="col-span-6 md:col-span-3 row-span-1 flex flex-col order-8 justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
					<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Comments</div>
					<div
						v-if="!editing"
						v-text="editSession.comments"
					></div>
					<textarea
						v-if="editing"
						v-model="editSession.comments"
						name="comments" cols="30" rows="5"
						:class="{'error-input' : errors.comments}"
						@input="delete errors.comments"
					></textarea>
					<span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
				</div>
			</div>
		</div>
		<div class="flex my-3 pin-b">
			<button @click.prevent="deleteSession" type="button" class="bg-red-500 hover:bg-red-600 focus:bg-red-600 rounded text-white text-sm px-4 py-2 mr-3"><i class="fas fa-trash"></i></button>
			<button v-if="!editing" @click.prevent="editing = true" type="button" class="bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2"><i class="fas fa-edit mr-3"></i><span>Edit</span></button>
			<div v-if="editing" class="flex">
				<button @click.prevent="saveSession" type="button" class="bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2 mr-3"><i class="fas fa-edit mr-3"></i><span>Save Changes</span></button>
				<div @click.prevent="cancelChanges" class="border border-muted-light hover:border-muted-dark hover:text-muted-light rounded text-sm px-4 py-2 cursor-pointer">Cancel</div>
			</div>
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
		viewSession: Object,
	},
	data() {
		return {
			editSession: {},
			editing: false,
			errors: {},
			loadSession: {},
			maxDateTime: moment().format(),
			// session: {},
		}
	},
	created() {
		// If session was provided then use that id and game_type to load session and save to state.
		if (this.viewSession) {
			this.loadSession = this.viewSession
			this.saveLoadSession(this.loadSession)
		}
		// Else user refreshed the page so load last viewed session by getting the id and game_type from state.
		else {
			this.loadSession = this.loadSessionState
		}

		// We should have a fully loaded session at this point, whether user clicked to view one or loaded from state
		// after user refreshes page.
		// Create a copy of loaded session so we can edit without mutating state directly.
		// This editSession is what's send to api controllers when updating data.
		if (Object.keys(this.loadSession).length > 0) {
			this.editSession = this.getEditSession()
		}
		// Else change router and give error message.
		// The only cases where this should occur is if the user visits /session directly without ever having viewed even one session.
		// Or views /session directly after deleting a session as loadSessionState is cleared to {} after destroying.
		else {
			this.$router.push({ name: 'sessions' })
			this.$snotify.error('Please selected a session to view')
		}
	},
	computed: {
		...mapState(['stakes', 'limits', 'variants', 'table_sizes']),
		...mapState('sessions', ['loadSessionState']),
		...mapGetters('cash_games', ['getCashGameById']),
		...mapGetters('tournaments', ['getTournamentById']),
		session() {
			if (this.loadSession.game_type === 'cash_game') {
				return this.getCashGameById(this.loadSession.id)
			} else if (this.loadSession.game_type === 'tournament') {
				return this.getTournamentById(this.loadSession.id)
			}
		},
		profit() {
			return this.session?.profit ?? 0
		},
		formattedProfit() {
			return this.$currency.format(this.profit)
		},
		roi() {
			const buyInsTotal = (this.buyInsTotal < 1) ? 1 : this.buyInsTotal
			return this.profit / buyInsTotal
		},
		buyInsTotal() {
			if (this.session) {
				let buyInTotal = this.session?.buy_in?.amount ?? 0
				let buyInsTotal = (this.session.buy_ins) ? this.session.buy_ins.reduce((total, buy_in) => total + buy_in.amount, 0) : 0
				let addOnTotal = (this.session.add_ons) ? this.session.add_ons.reduce((total, add_ons) => total + add_ons.amount, 0) : 0
				let rebuyTotal = (this.session.rebuys) ? this.session.rebuys.reduce((total, rebuys) => total + rebuys.amount, 0) : 0
				let expenseTotal = (this.session.expenses) ? this.session.expenses.reduce((total, expenses) => total + expenses.amount, 0) : 0
				return buyInTotal + buyInsTotal + addOnTotal + rebuyTotal + expenseTotal
			} else {
				return 0
			}
		},
		cashOutTotal() {
			let amount = this.session?.cash_out?.amount ?? 0
			return this.formatCurrency(amount)
		},
		duration() {
			const end_time = moment.utc(this.session.end_time)
			const start_time = moment.utc(this.session.start_time)
			return end_time.diff(start_time, 'hours', true)
		},
		// duration() {
		// 	return moment.duration(this.duration, 'hours').format("h [hours] m [mins]")
		// },
		profitPerHour() {
			return (this.profit / this.duration).toFixed(2)
		},
	},
	methods: {
		...mapActions('sessions', ['saveLoadSession', 'updateSession', 'destroySession']),
		getEditSession() {
			let session = JSON.parse(JSON.stringify(this.session))
			return {
				...session,
				start_time: moment.utc(session.start_time).format(),
				end_time: moment.utc(session.end_time).format(),
			}
		},
		cancelChanges() {
			this.editing = false
			this.editSession = this.getEditSession()
		},
		saveSession() {
			this.updateSession(this.editSession)
			.then(response => {
				this.$snotify.success('Changes saved.')
				this.editing = false
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors
			})
		},
		deleteSession() {
			this.$modal.show('dialog', {
				title: 'Are you sure?',
				text: 'Are you sure you want to delete this session?  This action cannot be undone.',
				buttons: [
					{
						title: 'Cancel'
					},
					{
						title: 'Yes, delete.',
						handler: () => { 
                            this.destroySession(this.editSession)
                            .then(response => {
								this.$router.push({ name: 'sessions' })
								this.$modal.hide('dialog')
                                this.$snotify.warning('Successfully deleted session.')
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
			return this.$currency.format(amount)
		},
		formatDuration(time) {
			return moment.duration(time, 'hours').format("h [hours] m [mins]")
		},
		formatDate(date) {
			return moment.utc(date).local().format("dddd Do MMMM, HH:mm")
		},
		addTransaction(transactionType, transaction) {
			this.$modal.show(TransactionDetails, {
                // Modal props
                transaction,
				transactionType,
				gameId: this.session.id,
                gameType: this.session.game_type
            }, {
                // Modal Options
                classes: 'modal',
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