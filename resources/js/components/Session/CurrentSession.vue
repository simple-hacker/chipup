<template>
	<div class="flex flex-col w-full xxl:w-3/5 xxl:mx-auto card text-white border-b-8 border-green-500 p-0">
		<h1 class="bg-gray-700 rounded-t text-center py-3 uppercase text-2xl md:text-4xl tracking-wider font-semibold text-white border-b-2 border-green-500">Current Session</h1>
		<form-wizard ref="currentSession" @on-complete="endSessionAndCashOut" @on-change="scrollToTop" :startIndex="1" finishButtonText="Cash Out!" title="" subtitle="" color="#00AD71" errorColor="#F45757" class="text-white">
			<!--
				DETAILS
			-->
            <tab-content :beforeChange="detailsValidation" title="Session Details" icon="fas fa-info">
				<!--
					LOCATION
				-->
				<div class="mb-4">
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Location</h2>
					<div class="flex flex-col">
						<input
							type="text"
							v-model="editLiveSession.location"
							placeholder="Location"
							class="text-lg"
							:class="{'error-input' : errors.location}"
							@input="delete errors.location"
						/>
						<span v-if="errors.location" class="error-message">{{ errors.location[0] }}</span>
					</div>
				</div>
				<!--
					GAME VARIABLES
				-->
				<div class="mb-4">
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Game</h2>
					<div class="flex grid grid-cols-4 gap-2">
						<div
							v-if="liveSession.stake"
							class="col-span-4 sm:col-span-2"
							:class="{ 'md:col-span-1' : (liveSession.game_type === 'cash_game') }"
						>
							<select
								v-model="editLiveSession.stake_id"
								class="text-lg"
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
						<div
							class="col-span-4 sm:col-span-2"
							:class="{ 'md:col-span-1' : (liveSession.game_type === 'cash_game') }"
						>
							<select
								v-model="editLiveSession.limit_id"
								class="text-lg"
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
						<div
							class="col-span-4 sm:col-span-2"
							:class="{ 'md:col-span-1' : (liveSession.game_type === 'cash_game') }"
						>
							<select
								v-model="editLiveSession.variant_id"
								class="text-lg"
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
						<div
							v-if="liveSession.table_size"
							class="col-span-4 sm:col-span-2"
							:class="{ 'md:col-span-1' : (liveSession.game_type === 'cash_game') }"
						>
							<select
								v-model="editLiveSession.table_size_id"
								class="text-lg"
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
					START TIME
				-->
				<div>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Start Time</h2>
					<div class="flex flex-col">
						<datetime
							v-model="editLiveSession.start_time"
							input-id="start_time"
							type="datetime"
							:minute-step="5"
							:flow="['time']"
							:max-datetime="maxDateTime"
							auto
							placeholder="Start Date and Time"
							title="Start Date and Time"
							class="w-full theme-green"
							:input-class="{'error-input' : errors.start_time, 'text-lg' : true}"
							@input="delete errors.start_time"
						></datetime>
						<span v-if="errors.start_time" class="error-message">{{ errors.start_time[0] }}</span>
					</div>
				</div>
            </tab-content>
			<!--
				IN PROGRESS
			-->
            <tab-content :beforeChange="inProgressValidation" title="In Progress" icon="fas fa-clock">
				<!--
					STATS
				-->
				<div class="w-full flex flex-wrap mb-4">
					<div class="flex flex-col w-1/2 rounded-none rounded-l card card-highlighted md:p-3">
						<h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Session Duration</h2>
						<span class="text-3xl sm:text-5xl xl:text-6xl font-extrabold -mt-2 text-green-500" v-text="runTime"></span>
					</div>
					<div class="flex flex-col w-1/2 rounded:none rounded-r card card-highlighted md:p-3">
						<h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Total Buy Ins</h2>
						<div class="text-3xl sm:text-5xl xl:text-6xl font-extrabold -mt-2 text-red-500">
							<number
								ref="profit"
								:from="0"
								:to="(buyInsTotal * -1)"
								:duration="2"
								:format="function(amount) { return $n(amount, { style: 'currency', currency: editLiveSession.currency }) }"
								easing="Power1.easeOut"
							/>
						</div>
					</div>
				</div>
				<!--
					CASH BUY INS
				-->
				<div
					v-if="liveSession.game_type === 'cash_game'"
					class="mb-4"
				>
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Buy Ins</h2>
					<div
						v-for="buy_in in liveSession.buy_ins"
						:key="buy_in.id"
						class="mb-1"
					>
						<transaction-summary :transaction="buy_in" :transaction-type="'buyin'" :game-id="liveSession.id"></transaction-summary>
					</div>
					<div
						@click="addTransaction('buyin', { amount: 0, currency: liveSession.currency })"
						class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Buy In</span>
					</div>
				</div>
				<!--
					TOURNAMENT BUY IN
				-->
				<div
					v-if="liveSession.game_type === 'tournament'"
					class="mb-4"
				>
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Buy In</h2>
					<transaction-summary v-if="liveSession.buy_in" :transaction="liveSession.buy_in" :transaction-type="'buyin'" :game-id="liveSession.id"></transaction-summary>
					<div
						v-if="!liveSession.buy_in"
						@click="addTransaction('buyin', { amount: 0, currency: liveSession.currency })"
						class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Buy In</span>
					</div>
				</div>
				<!--
					ADD ONS
				-->
				<div
					v-if="liveSession.add_ons"
					class="mb-4"
				>
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Add Ons</h2>
					<div
						v-for="add_on in liveSession.add_ons"
						:key="add_on.id"
						class="mb-1"
					>
						<transaction-summary :transaction="add_on" :transaction-type="'addon'" :game-id="liveSession.id"></transaction-summary>
					</div>
					<div
						@click="addTransaction('addon', { amount: 0, currency: liveSession.currency })"
						class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Add On</span>
					</div>
				</div>
				<!--
					REBUYS
				-->
				<div
					v-if="liveSession.rebuys"
					class="mb-4"
				>
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Rebuys</h2>
					<div
						v-for="rebuy in liveSession.rebuys"
						:key="rebuy.id"
						class="mb-1"
					>
						<transaction-summary :transaction="rebuy" :transaction-type="'rebuy'" :game-id="liveSession.id"></transaction-summary>
					</div>
					<div
						@click="addTransaction('rebuy', { amount: 0, currency: liveSession.currency })"
						class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Rebuy</span>
					</div>
				</div>
				<!--
					EXPENSES
				-->
				<div class="mb-4">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Expenses</h2>
					<div
						v-for="expense in liveSession.expenses"
						:key="expense.id"
						class="mb-1"
					>
						<transaction-summary :transaction="expense" :transaction-type="'expense'" :game-id="liveSession.id"></transaction-summary>
					</div>
					<div
						@click="addTransaction('expense', { amount: 0, currency: liveSession.currency, comments: '' })"
						class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
					>
						<i class="fas fa-plus-circle mr-2"></i>
						<span>Add Expense</span>
					</div>
				</div>
				<!--
					COMMENTS
				-->
				<div class="mb-4">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Comments</h2>
					<div class="flex flex-col">
						<textarea
							v-model="editLiveSession.comments"
							name="comments" cols="30" rows="5"
							class="p-1 text-base bg-gray-500"
							:class="{'error-input' : errors.comments}"
							@input="updateComments"
						></textarea>
						<span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
					</div>
				</div>
				<!--
					PRIZE POOL
				-->
				<div
					v-if="liveSession.game_type === 'tournament'"
					class="mb-4"
				>
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">What is the total prize pool?</h2>
					<input
						v-model="editLiveSession.prize_pool"
						type="number"
						min=0
						:class="{'error-input' : errors.prize_pool}"
						@input="updatePrizePool"
					>
					<span v-if="errors.prize_pool" class="error-message">{{ errors.prize_pool[0] }}</span>
				</div>
            </tab-content>
			<!--
				CASH OUT
			-->
            <tab-content :beforeChange="cashOutValidation" title="Cash Out" icon="fas fa-money-bill">
				<!--
					CASH OUT
				-->
				<div class="mb-4">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">How much did you cash out for?</h2>
					<transaction-amount
						:currency="cashOut.currency"
						:amount="cashOut.amount"
						:border="'border-green-500'"
						:error="errors.amount"
						v-on:clear-error="delete errors.amount"
						v-on:update-currency="cashOut.currency = arguments[0]"
						v-on:update-amount="cashOut.amount = arguments[0]"
					/>
					<span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>
				</div>
				<!--
					POSITION
				-->
				<div
					v-if="liveSession.game_type === 'tournament'"
					class="mb-4"
				>
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">What was your finishing position?</h2>
					<input
						v-model="tournamentCashOut.position"
						type="number"
						min=0
						:class="{'error-input' : errors.position}"
						@input="delete errors.position"
					>
					<span v-if="errors.position" class="error-message">{{ errors.position[0] }}</span>
				</div>
				<!--
					ENTRIES
				-->
				<div
					v-if="liveSession.game_type === 'tournament'"
					class="mb-4"
				>
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">How many entries were there?</h2>
					<input
						v-model="tournamentCashOut.entries"
						type="number"
						min=0
						:class="{'error-input' : errors.entries}"
						@input="delete errors.entries"
					>
					<span v-if="errors.entries" class="error-message">{{ errors.entries[0] }}</span>
				</div>
				<!--
					END TIME
				-->
				<div class="mb-4">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">When did you finish?</h2>
					<datetime
						v-model="cashOut.end_time"
						input-id="end_time"
						type="datetime"
						:minute-step="5"
						:flow="['time']"
						:max-datetime="maxDateTime"
						placeholder="End At"
						title="End Live Session At"
						auto
						class="w-full theme-green"
						:input-class="{'error-input' : errors.end_time, 'text-lg' : true}"
						@input="delete errors.end_time"	
					>
					</datetime>
					<span v-if="errors.end_time" class="error-message">{{ errors.end_time[0] }}</span>
				</div>
            </tab-content>
        </form-wizard>
	</div>
</template>

<script>
import moment from 'moment'
import 'moment-duration-format'
import { mapState, mapGetters, mapActions } from 'vuex'

import { FormWizard, TabContent } from 'vue-form-wizard'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'

import TransactionSummary from '@components/Transaction/TransactionSummary'
import TransactionDetails from '@components/Transaction/TransactionDetails'
import TransactionAmount from '@components/TransactionAmount'

export default {
	name: 'CurrentSession',
	components: { FormWizard, TabContent, TransactionSummary, TransactionDetails, TransactionAmount },
	data() {
		return {
			editLiveSession: {},
			editing: false,
			errors: {},
			maxDateTime: moment().format(),
			cashOut: {
				end_time: moment().format(),
				currency: 'GBP',
                amount: 0
			},
			tournamentCashOut: {
				position: '',
				entries: ''
			},
		}
	},
	created() {
		this.editLiveSession = this.getEditSession()
		this.cashOut.currency = this.liveSession.currency ?? this.$store.state.user?.currency ?? 'GBP'
	},
	mounted() {
		this.$refs.currentSession.activateAll()
	},
	computed: {
		...mapState(['stakes', 'limits', 'variants', 'table_sizes']),
		...mapState('live', ['liveSession']),
		...mapGetters('live', ['runTime', 'runTimeHours']),
		buyInsTotal() {
			let buyInTotal = this.liveSession?.buy_in?.amount ?? 0
			let buyInsTotal = (this.liveSession.buy_ins) ? this.liveSession.buy_ins.reduce((total, buy_in) => total + buy_in.amount, 0) : 0
			let addOnTotal = (this.liveSession.add_ons) ? this.liveSession.add_ons.reduce((total, add_ons) => total + add_ons.amount, 0) : 0
			let rebuyTotal = (this.liveSession.rebuys) ? this.liveSession.rebuys.reduce((total, rebuys) => total + rebuys.amount, 0) : 0
			let expenseTotal = (this.liveSession.expenses) ? this.liveSession.expenses.reduce((total, expenses) => total + expenses.amount, 0) : 0
			return buyInTotal + buyInsTotal + addOnTotal + rebuyTotal + expenseTotal
		},
		profitPerHour() {
			return (this.buyInsTotal * -1) / this.runTimeHours
		},
	},
	methods: {
		...mapActions('live', ['updateLiveSession', 'endLiveSession']),
		scrollToTop() {
			// Scroll to top of main content div, needed for mobiles so each step of the form scrolls to top.
			this.$parent.$parent.$refs.scroll.scrollTop = 0
		},
		detailsValidation() {
			// Front end validation
			let validationErrors = {}
			if (this.editLiveSession.location === '') {
				validationErrors.location = ['Location cannot be empty.']
			}
			if (this.editLiveSession.start_time === '') {
				validationErrors.start_time = ['Start date and time cannot be empty.']
			}
			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

			// If front end validation is successful, perform update on session.
			// If updating fails return false so user cannot progress on formwizard.
			if (Object.keys(validationErrors).length === 0) {
				// Backend validation and update.
				this.updateLiveSession(this.editLiveSession)
				.then(response => {
					// Return true because session was successfully updated and can move to next step on form.
					this.$refs.currentSession.changeTab(this.$refs.currentSession.activeTabIndex, 1)
				})
				.catch(error => {
					this.errors = error.response.data.errors
				})
			}

			return Object.keys(validationErrors).length === 0
		},
		inProgressValidation() {
			let validationErrors = {}

			// Tournament BuyIn can be zero.
			if (this.editLiveSession.prize_pool < 0) {
				validationErrors.prize_pool = ['Prize pool amount must be zero or greater.']
			}

			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

            // If no keys are in validationErrors return true to go to next slide, else stop.
			return (Object.keys(validationErrors).length === 0)
		},
		cashOutValidation() {
			let validationErrors = {}

			// Tournament BuyIn can be zero.
			if (this.cashOut.amount < 0) {
				validationErrors.cashOut.amount = ['Buy in amount must be zero or greater.']
			}
			if (this.game_type === 'tournament' && this.cashOut.position < 0) {
				validationErrors.cashOut.position = ['Position must be greater than zero.']
			}
			if (this.game_type === 'tournament' && this.cashOut.entries < 0) {
				validationErrors.cashOut.entries = ['Number of entires must be greater than zero.']
			}
			if (this.cashOut.end_time === '') {
				validationErrors.end_time = ['End date and time cannot be empty.']
			}

			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

            // If no keys are in validationErrors return true to go to next slide, else stop.
			return (Object.keys(validationErrors).length === 0)
		},
		getEditSession() {
			let liveSession = JSON.parse(JSON.stringify(this.liveSession))
			return {
				...liveSession,
				start_time: moment.utc(liveSession.start_time).format(),
				end_time: moment.utc(liveSession.end_time).format(),
			}
		},
		updateComments: _.debounce(function() {
			delete this.errors.comments

			this.updateLiveSession({
				game_type: this.liveSession.game_type,
				comments: this.editLiveSession.comments
			})
			.catch(error => {
				this.errors = error.response.data.errors
			})
		}, 1000),
		updatePrizePool: _.debounce(function() {
			delete this.errors.prize_pool

			if (this.editLiveSession.prize_pool >= 0) {
				this.updateLiveSession({
					game_type: this.liveSession.game_type,
					prize_pool: this.editLiveSession.prize_pool
				})
				.catch(error => {
					this.errors = error.response.data.errors
				})
			}
		}, 1000),
		saveSession() {
			this.updateLiveSession(this.editLiveSession)
			.then(response => {
				this.editing = false
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors
			})
		},
		formatDate(date) {
			return moment.utc(date).local().format("dddd Do MMMM, HH:mm")
		},
		endSessionAndCashOut() {
			let cashOutData = (this.liveSession.game_type === 'tournament') ? {...this.cashOut, ...this.tournamentCashOut} : this.cashOut
			this.endLiveSession(cashOutData)
			.then(response => {
                this.$emit('close')
                this.$router.push('sessions')               

                if ((this.cashOut.amount - this.buyInTotal) > 0) {
                    this.$snotify.success(`Nice win!`)
                } else {
                    this.$snotify.warning(`Better luck next time.`)
                }
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors
			})
		},
		addTransaction(transactionType, transaction) {
			const modalClass = (transactionType === 'cashout') ? 'modal-green' : 'modal-red'
			this.$modal.show(TransactionDetails, {
                // Modal props
                transaction,
				transactionType,
				gameId: this.liveSession.id,
                gameType: this.liveSession.game_type
            }, {
                // Modal Options
                classes: ['modal', modalClass],
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