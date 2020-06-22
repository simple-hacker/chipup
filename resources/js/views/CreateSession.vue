<template>
	<div class="flex flex-col w-full xxl:w-3/5 xxl:mx-auto card text-white border-b-8 border-green-500 p-0">
		<h1 class="bg-gray-700 rounded-t text-center py-3 uppercase text-2xl md:text-4xl tracking-wider font-semibold text-white border-b-2 border-green-500">Create Session</h1>
		<form-wizard ref="createSession" @on-complete="addSession" @on-change="scrollToTop" finishButtonText="Create Session" title="" subtitle="" color="#00AD71" errorColor="#F45757" class="text-white">
            <tab-content title="Session" icon="fas fa-star">
				<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What did you play?</h2>
                <div class="flex flex-col md:flex-row justify-around py-4">
					<button
						@click="switchGameType('cash_game')"
						class="w-full md:w-auto mb-4 md:mb-0 btn text-2xl"
						:class="(game_type === 'cash_game') ? 'btn-green' : 'btn-gray'"
						v-text="'Cash Game'"
					>
					</button>
					<button
						@click="switchGameType('tournament')"
						class="w-full md:w-auto btn text-2xl"
						:class="(game_type === 'tournament') ? 'btn-green' : 'btn-gray'"
						v-text="'Tournament'"
					>
					</button>
				</div>
            </tab-content>
            <tab-content :beforeChange="detailsValidation" title="Details" icon="fas fa-info">
				<!--
					LOCATION
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Where did you play?</h2>
					<div class="flex flex-col py-2">
						<input
							type="text"
							v-model="session.location"
							placeholder="Location"
							class="text-lg"
							:class="{'error-input' : errors.location}"
							@input="delete errors.location"
						/>
						<span v-if="errors.location" class="error-message">{{ errors.location[0] }}</span>
					</div>
				</div>
				<!--
					TOURNAMENT NAME
				-->
				<div
					v-if="game_type === 'tournament'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What was the tournament name?</h2>
					<div class="flex flex-col py-2">
						<input
							type="text"
							v-model="tournament.name"
							placeholder="Tournament Name"
							class="text-lg"
							:class="{'error-input' : errors.name}"
							@input="delete errors.name"
						/>
						<span v-if="errors.name" class="error-message">{{ errors.name[0] }}</span>
					</div>
				</div>
				<!--
					STAKES
				-->
				<div
					v-if="game_type === 'cash_game'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What stakes were you playing?</h2>
					<div class="flex flex-col py-2">
						<select
							v-model="cash_game.stake_id"
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
				</div>
				<!--
					LIMIT AND VARIANT
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What game variant were you playing?</h2>
					<div class="flex py-2">
						<div class="flex flex-col w-1/2 mr-1">
							<select
								v-model="session.limit_id"
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
						<div class="flex flex-col w-1/2 ml-1">
							<select
								v-model="session.variant_id"
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
					</div>
				</div>
				<!--
					PRIZE POOL
				-->
				<div
					v-if="game_type === 'tournament'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What was the total prize pool?</h2>
					<div class="flex flex-col py-2">
						<input
							type="number"
							min="0"
							placeholder="Prize Pool"
							v-model="tournament.prize_pool"
							class="text-xl"
							:class="{'error-input' : errors.prize_pool}"
							@input="delete errors.prize_pool"
						/>
						<span v-if="errors.prize_pool" class="error-message">{{ errors.prize_pool[0] }}</span>
					</div>
				</div>
				<!--
					ENTRIES
				-->
				<div
					v-if="game_type === 'tournament'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">How many entries were there?</h2>
					<div class="flex flex-col py-2">
						<input
							type="number"
							min="0"
							placeholder="Number of Entries"
							v-model="tournament.entries"
							class="text-xl"
							:class="{'error-input' : errors.entries}"
							@input="delete errors.entries"
						/>
						<span v-if="errors.entries" class="error-message">{{ errors.entries[0] }}</span>
					</div>
				</div>
				<!--
					TABLE SIZE
				-->
				<div
					v-if="game_type === 'cash_game'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What was the ring size?</h2>
					<div class="flex flex-col py-2">
						<select
							v-model="cash_game.table_size_id"
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
				<!--
					START TIME
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">When did the session start?</h2>
					<div class="flex flex-col py-2">
						<datetime
							v-model="session.start_time"
							input-id="start_time"
							type="datetime"
							:minute-step="5"
							:max-datetime="maxStartDateTime"
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
            <tab-content :beforeChange="buyInsValidation" title="Buy Ins" icon="fas fa-wallet">
				<!--
					CASH BUY INS
				-->
				<div
					v-if="game_type === 'cash_game'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">How much did you buy in for?</h2>
					<div class="flex flex-col justify-around py-4">
						<div
							v-for="(buy_in, index) in cash_game.buy_ins"
							:key="index"
							class="flex mb-2"
						>
							<div class="flex flex-col w-full">
								<div class="flex">
									<transaction-amount
										:currency="buy_in.currency"
										:amount="buy_in.amount"
										:border="'border-red-500'"
										:error="errors[`buy_ins.${index}.amount`]"
										v-on:clear-error="delete errors[`buy_ins.${index}.amount`]"
										v-on:update-currency="buy_in.currency = arguments[0]"
										v-on:update-amount="buy_in.amount = arguments[0]"
									/>
									<button v-if="index != 0" @click="cash_game.buy_ins.splice(index, 1)" class="ml-2 rounded py-2 px-3 bg-gray-450 hover:bg-gray-400"><i class="fas fa-times"></i></button>
								</div>
								<span v-if="errors[`buy_ins.${index}.amount`]" class="error-message">{{ errors[`buy_ins.${index}.amount`][0] }}</span>
							</div>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="cash_game.buy_ins.push({ amount: 0, currency: defaultCurrency })"
								class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 cursor-pointer text-white text-center"
							>
								<i class="fas fa-plus-circle mr-2"></i>
								<span>Add Buy In</span>
							</div>
						</div>
					</div>
				</div>
				<!--
					TOURNAMENT BUY IN
				-->
				<div
					v-if="game_type === 'tournament'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What was the buy in?</h2>
					<div class="flex flex-col py-2">
						<transaction-amount
							:currency="tournament.buy_in.currency"
							:amount="tournament.buy_in.amount"
							:border="'border-red-500'"
							:error="errors[`buy_in.amount`]"
							v-on:clear-error="delete errors[`buy_in.amount`]"
							v-on:update-currency="tournament.buy_in.currency = arguments[0]"
							v-on:update-amount="tournament.buy_in.amount = arguments[0]"
						/>
						<span v-if="errors[`buy_in.amount`]" class="error-message">{{ errors[`buy_in.amount`][0] }}</span>
					</div>
				</div>
				<!--
					REBUYS
				-->
				<div
					v-if="game_type === 'tournament'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Any rebuys?</h2>
					<div class="flex flex-col py-2">
						<div
							v-for="(rebuy, index) in tournament.rebuys"
							:key="index"
							class="flex mb-2"
						>
							<div class="flex flex-col w-full">
								<div class="flex">
									<transaction-amount
										:currency="rebuy.currency"
										:amount="rebuy.amount"
										:border="'border-red-500'"
										:error="errors[`rebuys.${index}.amount`]"
										v-on:clear-error="delete errors[`rebuys.${index}.amount`]"
										v-on:update-currency="rebuy.currency = arguments[0]"
										v-on:update-amount="rebuy.amount = arguments[0]"
									/>
									<button @click="tournament.rebuys.splice(index, 1)" class="ml-2 rounded py-2 px-3 bg-gray-450 hover:bg-gray-400"><i class="fas fa-times"></i></button>
								</div>
								<span v-if="errors[`rebuys.${index}.amount`]" class="error-message">{{ errors[`rebuys.${index}.amount`][0] }}</span>
							</div>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="tournament.rebuys.push({ amount: 0, currency: defaultCurrency})"
								class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 cursor-pointer text-white text-center"
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
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Any add ons?</h2>
					<div class="flex flex-col py-2">
						<div
							v-for="(add_on, index) in tournament.add_ons"
							:key="index"
							class="flex mb-2"
						>
							<div class="flex flex-col w-full">
								<div class="flex">
									<transaction-amount
										:currency="add_on.currency"
										:amount="add_on.amount"
										:border="'border-red-500'"
										:error="errors[`add_ons.${index}.amount`]"
										v-on:clear-error="delete errors[`add_ons.${index}.amount`]"
										v-on:update-currency="add_on.currency = arguments[0]"
										v-on:update-amount="add_on.amount = arguments[0]"
									/>
									<button @click="tournament.add_ons.splice(index, 1)" class="ml-2 rounded py-2 px-3 bg-gray-450 hover:bg-gray-400"><i class="fas fa-times"></i></button>
								</div>
								<span v-if="errors[`add_ons.${index}.amount`]" class="error-message">{{ errors[`add_ons.${index}.amount`][0] }}</span>
							</div>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="tournament.add_ons.push({ amount: 0, currency: defaultCurrency})"
								class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 cursor-pointer text-white text-center"
							>
								<i class="fas fa-plus-circle mr-2"></i>
								<span>Add Add On</span>
							</div>
						</div>
					</div>
				</div>
				<!--
					EXPENSES
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Any expenses?</h2>
					<div class="flex flex-col py-2">
						<div
							v-for="(expense, index) in session.expenses"
							:key="index"
							class="flex mb-2"
						>
							<div class="flex flex-col w-full">
								<div class="flex">
									<transaction-amount
										:currency="expense.currency"
										:amount="expense.amount"
										:border="'border-red-500'"
										:error="errors[`expenses.${index}.amount`]"
										v-on:clear-error="delete errors[`expenses.${index}.amount`]"
										v-on:update-currency="expense.currency = arguments[0]"
										v-on:update-amount="expense.amount = arguments[0]"
									/>
									<input
										v-model="expense.comments"
										type="text"
										class="text-base border-r-4 border-red-500 ml-1"
										placeholder="Comments"
										:class="{'error-input' : errors[`expenses.${index}.comments`]}"
										@input="delete errors[`expenses.${index}.comments`]"
									>
									<button @click="session.expenses.splice(index, 1)" class="ml-2 rounded py-2 px-3 bg-gray-450 hover:bg-gray-400"><i class="fas fa-times"></i></button>
								</div>
								<span v-if="errors[`expenses.${index}.amount`]" class="error-message">{{ errors[`expenses.${index}.amount`][0] }}</span>
								<span v-if="errors[`expenses.${index}.comments`]" class="error-message">{{ errors[`expenses.${index}.comments`][0] }}</span>
							</div>
						</div>
						<div class="flex justify-center items-center">
							<div
								@click="session.expenses.push({ amount: 0, currency: defaultCurrency, comments: ''})"
								class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 cursor-pointer text-white text-center"
							>
								<i class="fas fa-plus-circle mr-2"></i>
								<span>Add Expense</span>
							</div>
						</div>
					</div>
				</div>
            </tab-content>
            <tab-content :beforeChange="cashOutValidation" title="Cash Out" icon="fas fa-money-bill">
				<!--
					CASH OUT
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">How much did you cash out for?</h2>
					<div class="flex flex-col py-2">
						<transaction-amount
							:currency="session.cash_out.currency"
							:amount="session.cash_out.amount"
							:border="'border-green-500'"
							:error="errors[`cash_out.amount`]"
							v-on:clear-error="delete errors[`cash_out.amount`]"
							v-on:update-currency="session.cash_out.currency = arguments[0]"
							v-on:update-amount="session.cash_out.amount = arguments[0]"
						/>
						<span v-if="errors[`cash_out.amount`]" class="error-message">{{ errors[`cash_out.amount`][0] }}</span>
					</div>
				</div>
				<!--
					POSITION
				-->
				<div
					v-if="game_type === 'tournament'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What position did you finish?</h2>
					<div class="flex flex-col py-2">
						<input
							type="number"
							min="0"
							placeholder="Finishing Position"
							v-model="tournament.position"
							class="text-xl"
							:class="{'error-input' : errors.position}"
							@input="delete errors.position"
						/>
						<span v-if="errors.position" class="error-message">{{ errors.position[0] }}</span>
					</div>
				</div>
				<!--
					END TIME
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">When did you finish?</h2>
					<div class="flex flex-col py-2">
						<datetime
							v-model="session.end_time"
							input-id="end_time"
							type="datetime"
							:minute-step="5"
							:min-datetime="session.start_time"
							:max-datetime="maxDateTime"
							auto
							placeholder="End Date and Time"
							title="End Date and Time"
							class="w-full theme-green"
							:input-class="{'error-input' : errors.end_time, 'text-lg' : true}"
							@input="delete errors.end_time"
						></datetime>
						<span v-if="errors.end_time" class="error-message">{{ errors.end_time[0] }}</span>
					</div>
				</div>
				<!--
					COMMENTS
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Any comments?</h2>
					<div class="flex flex-col py-2">
						<textarea
							v-model="session.comments"
							name="comments" cols="30" rows="5"
							class="p-1 text-base bg-gray-500"
							:class="{'error-input' : errors.comments}"
							@input="delete errors.comments"
						></textarea>
						<span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
					</div>
				</div>
            </tab-content>
        </form-wizard>
	</div>
</template>

<script>
import moment from 'moment'
import 'moment-duration-format'

import { mapState, mapActions } from 'vuex'

import { FormWizard, TabContent } from 'vue-form-wizard'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'

import TransactionAmount from '@components/TransactionAmount'

export default {
	name: 'CreateSession',
	components: { FormWizard, TabContent, TransactionAmount },
	data() {
		return {
			game_type: 'cash_game',
			session: {
				location: '',
				start_time: '',
				end_time: '',
				limit_id: 0,
				variant_id: 0,
				expenses: [],
				cash_out: { amount: 0, currency: 'GBP' },
				comments: ''
			},
			cash_game: {
				stake_id: 0,
				table_size_id: 0,
				buy_ins: [
					{ amount: 0, currency: 'GBP' },
				],
			},
			tournament: {
				name: '',
				prize_pool: 0,
				position: 0,
				entries: 0,
				buy_in: { amount: 0, currency: 'GBP'},
				rebuys: [],
				add_ons: [],
			},
			errors: {},
			maxDateTime: moment().format(),
		}
	},
	computed: {
		...mapState(['user', 'stakes', 'limits', 'variants', 'table_sizes']),
		defaultCurrency() {
			return this.$store.state.user?.currency ?? 'GBP'
		},
		maxStartDateTime() {
			return moment(this.session.end_time).format() < moment().format() ? moment(this.session.end_time).format() : moment().format()
		}
	},
	methods: {
		...mapActions('cash_games', ['addCashGame']),
		...mapActions('tournaments', ['addTournament']),
		switchGameType(game_type) {
			this.game_type = game_type
		},
		scrollToTop() {
			// Scroll to top of main content div, needed for mobiles so each step of the form scrolls to top.
			this.$parent.$refs.scroll.scrollTop = 0
		},
		detailsValidation() {
			let validationErrors = {}

			if (this.session.location === '') {
				validationErrors.location = ['Location cannot be empty.']
			}
			if (this.session.start_time === '') {
				validationErrors.start_time = ['Start date and time cannot be empty.']
			}
			if (this.game_type === 'tournament' && this.tournament.prize_pool < 0) {
				validationErrors.prize_pool = ['Prize pool must be greater than zero.']
			}
			if (this.game_type === 'tournament' && this.tournament.entries < 0) {
				validationErrors.entries = ['Number of entries must be greater than zero.']
			}

			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

            // If no keys are in validationErrors return true to go to next slide, else stop.
			return (Object.keys(validationErrors).length === 0)
		},
		buyInsValidation() {
			let validationErrors = {}

			// Cash Game Buy In amount
			if (this.game_type === 'cash_game') {
				this.cash_game.buy_ins.forEach((buy_in, index) => {
					if (buy_in.amount <= 0) {
						validationErrors[`buy_ins.${index}.amount`] = ['Buy in amount must be greater than zero.']
					}
				})
			}

			// Tournament Buy In and extra transactions
			if (this.game_type === 'tournament') {
				if (this.tournament.buy_in.amount < 0) {
					validationErrors[`buy_in.amount`] = ['Buy in amount must be zero or greater.']
				}
				this.tournament.rebuys.forEach((rebuy, index) => {
					if (rebuy.amount <= 0) {
						validationErrors[`rebuys.${index}.amount`] = ['Rebuy amount must be greater than zero.']
					}
				})
				this.tournament.add_ons.forEach((add_on, index) => {
					if (add_on.amount <= 0) {
						validationErrors[`add_ons.${index}.amount`] = ['Add on amount must be greater than zero.']
					}
				})
			}

			// Expenses
			this.session.expenses.forEach((expense, index) => {
				if (expense.amount <= 0) {
					validationErrors[`expenses.${index}.amount`] = ['Expense amount must be greater than zero.']
				}
			})

			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

            // If no keys are in validationErrors return true to go to next slide, else stop.
			return (Object.keys(validationErrors).length === 0)
		},
		cashOutValidation() {
			let validationErrors = {}

			// Cash out
			if (this.session.cash_out.amount < 0) {
				validationErrors[`cash_out.amount`] = ['Cash out amount must be zero or greater.']
			}

			if (this.session.end_time === '') {
				validationErrors.end_time = ['End date and time cannot be empty.']
			}

			// Tournament Buy In and extra transactions
			if (this.game_type === 'tournament' && this.tournament.position < 0) {
				validationErrors.position = ['Position number must be greater than zero.']
			}

			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

            // If no keys are in validationErrors return true to go to next slide, else stop.
			return (Object.keys(validationErrors).length === 0)
		},
		addSession() {
			if (this.game_type === 'cash_game') {
				this.addCashGame({
					...this.session,
					...this.cash_game
				})
				.then(response => {
					this.$snotify.success(`Successfully created cash game.`)
					this.$router.push({ name: 'sessions' })
				})
				.catch(error => {
					this.$snotify.error('Error: '+error.response.data.message)
					this.errors = error.response.data.errors
					if (this.errors.location || this.errors.stake_id || this.errors.limit_id || this.errors.variant_id || this.errors.table_size_id || this.errors.start_time) {
                        this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 1)
                    } else if (this.errors.buy_ins || this.errors.expenses) {
                        this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 2)
                    } else if (this.errors.cash_out || this.errors.end_time || this.errors.comments) {
						this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 3)
					}
				})
			} else if (this.game_type === 'tournament') {
				this.addTournament({
					...this.session,
					...this.tournament
				})
				.then(response => {
					this.$snotify.success(`Successfully created tournament.`)
					this.$router.push({ name: 'sessions' })
				})
				.catch(error => {
					this.$snotify.error('Error: '+error.response.data.message)
					this.errors = error.response.data.errors
					if (this.errors.location || this.errors.name || this.errors.limit_id || this.errors.variant_id || this.errors.prize_pool || this.errors.entries || this.errors.start_time ) {
                        this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 1)
                    } else if (this.errors.buy_in || this.errors.expenses || this.errors.add_ons || this.errors.rebuys) {
                        this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 2)
                    } else if (this.errors.cash_out || this.errors.position || this.errors.end_time || this.errors.comments) {
						this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 3)
					}
				})
			} else {
				this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 0)
			}
		},
	},
	created() {
		this.session.location = this.user.default_location ?? ''
		this.session.limit_id = this.user.default_limit_id ?? 1
		this.session.variant_id = this.user.default_variant_id ?? 1
		this.cash_game.stake_id = this.user.default_stake_id ?? 1
		this.cash_game.table_size_id = this.user.default_table_size_id ?? 1

		this.session.cash_out.currency = this.$store.state.user.currency ?? 'GBP'
		this.cash_game.buy_ins[0].currency = this.$store.state.user.currency ?? 'GBP'
		this.tournament.buy_in.currency = this.$store.state.user.currency ?? 'GBP'
	},
}
</script>

<style lang="scss">
    .vue-form-wizard .wizard-icon-circle {
        background-color: #38393D;
    }

    .vue-form-wizard .wizard-nav-pills > li > a {
        color: white;

        &:hover {
            color: lighten(#505155, 20%);
        }
	}

	div.wizard-navigation > div.wizard-tab-content {
		@apply p-2 m-2 mt-3;
		@screen md {
			@apply py-4 px-4 m-3 mt-5;
		}
	}
</style>