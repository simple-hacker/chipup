<template>
	<div class="flex flex-col w-full xxl:w-3/5 xxl:mx-auto card text-white border-b-8 border-green-500 p-0">
		<h1 class="bg-gray-700 rounded-t text-center py-3 uppercase text-2xl md:text-4xl tracking-wider font-semibold text-white border-b-2 border-green-500">Start Session</h1>
		<form-wizard ref="createSession" @on-complete="startSession" @on-change="scrollToTop" finishButtonText="Start!" title="" subtitle="" color="#00AD71" errorColor="#F45757" class="text-white">
            <tab-content title="Session" icon="fas fa-star">
				<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What are you playing?</h2>
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
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">Where are you playing?</h2>
					<div class="flex flex-col">
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
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What is the tournament name?</h2>
					<div class="flex flex-col">
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
					LIMIT AND VARIANT
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What game variant are you playing?</h2>
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
					TABLE SIZE
				-->
				<div
					v-if="game_type === 'cash_game'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What is the ring size?</h2>
					<div class="flex flex-col">
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
            </tab-content>
			<!--
				START
			-->
            <tab-content :beforeChange="startValidation" title="Start" icon="fas fa-play">
				<!--
					BUY IN
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What is your buy in?</h2>
					<div class="flex flex-col">
						<transaction-amount
							:currency="session.currency"
							:amount="session.amount"
							:border="'border-red-500'"
							:error="errors.amount"
							v-on:update-currency="session.currency = arguments[0]"
							v-on:update-amount="session.amount = arguments[0]"
							v-on:clear-error="delete errors.amount"
						/>
						<span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>
					</div>
				</div>
				<!--
					STAKES
				-->
				<div
					v-if="game_type === 'cash_game'"
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">What stakes are you playing?</h2>
					<div class="flex flex-col">
						<stake-select @stake-changed="changeStake" :stake_id="cash_game.stake_id" :currency="sessionCurrency" :locale="$store.state.user.locale"/>
						<span v-if="errors.stake_id" class="error-message">{{ errors.stake_id[0] }}</span>
					</div>
				</div>
				<!--
					START TIME
				-->
				<div
					class="mb-3"
				>
					<h2 class="uppercase text-gray-100 text-base md:text-xl font-extrabold tracking-wider mb-1">When are you starting the session?</h2>
					<div class="flex flex-col">
						<datetime
							v-model="session.start_time"
							input-id="start_time"
							type="datetime"
							:minute-step="5"
							:max-datetime="maxStartTime"
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
        </form-wizard>
	</div>
</template>

<script>

import moment from 'moment'
import { mapState, mapActions } from 'vuex'

import { FormWizard, TabContent } from 'vue-form-wizard'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'

import StakeSelect from '@components/StakeSelect'
import TransactionAmount from '@components/TransactionAmount'

export default {
	name: 'StartSession',
	components: { FormWizard, TabContent, StakeSelect, TransactionAmount },
    data() {
		return {
			errors: {},
			game_type: 'cash_game',
			maxStartTime: moment().format(),
			session: {
				location: '',
				limit_id: 1,
				variant_id: 1,
				currency: 'GBP',
				amount: 0,
				start_time: moment().format(),
			},
			cash_game: {
				stake_id: 1,
				table_size_id: 1,
			},
			tournament: {
				name: '',
			},
			errors: {}
		}
	},
	computed: {
		...mapState(['user', 'stakes', 'limits', 'variants', 'table_sizes']),
		liveSession() {
			if (this.game_type === 'cash_game') {
				return {...this.session, ...this.cash_game}
			} else if (this.game_type === 'tournament') {
				return {...this.session, ...this.tournament}
			} else {
				return {}
			}
		},
		defaultCurrency() {
			return this.$store.state.user?.currency ?? 'GBP'
		},
		sessionCurrency() {
			return this.session?.currency ?? this.defaultCurrency
		}
	},
    methods: {
		...mapActions('live', ['startLiveSession']),
		switchGameType(game_type) {
			this.game_type = game_type
		},
		changeStake(stake) {
            this.cash_game.stake_id = stake.id
        },
		scrollToTop() {
			// Scroll to top of main content div, needed for mobiles so each step of the form scrolls to top.
			this.$parent.$parent.$refs.scroll.scrollTop = 0
		},
		detailsValidation() {
			let validationErrors = {}

			if (this.session.location === '') {
				validationErrors.location = ['Location cannot be empty']
			}

			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

            // If no keys are in validationErrors return true to go to next slide, else stop.
			return (Object.keys(validationErrors).length === 0)
		},
		startValidation() {
			let validationErrors = {}

			// Cash Game BuyIn cannot be zero
			if (this.game_type === 'cash_game' && this.session.amount <= 0) {
				validationErrors.amount = ['Buy in amount must be greater than zero']
			}
			// Tournament BuyIn can be zero.
			if (this.game_type === 'tournament' && this.session.amount < 0) {
				validationErrors.amount = ['Buy in amount must be zero or greater']
			}
			if (this.session.start_time === '') {
				validationErrors.start_time = ['Start date and time cannot be empty']
			}

			// Need Object.assign for reactivity to display error message and highlighting
			this.errors = Object.assign({}, validationErrors)

            // If no keys are in validationErrors return true to go to next slide, else stop.
			return (Object.keys(validationErrors).length === 0)
		},
		startSession() {
			this.startLiveSession({
				game_type: this.game_type,
				session: this.liveSession
			})
			.then(response => {
				this.$snotify.success('Good luck!')
				this.errors = {}
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors

				if (this.errors.location || this.errors.stake_id || this.errors.limit_id || this.errors.variant_id || this.errors.table_size_id || this.errors.name) {
					this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 1)
				} else if (this.errors.amount || this.errors.start_time) {
					this.$refs.createSession.changeTab(this.$refs.createSession.activeTabIndex, 2)
				}
			})
		},
	},
	created() {
		this.session.location = this.user.default_location ?? ''
		this.session.limit_id = this.user.default_limit_id ?? 1
		this.session.variant_id = this.user.default_variant_id ?? 1
		this.cash_game.stake_id = this.user.default_stake_id ?? 1
		this.cash_game.table_size_id = this.user.default_table_size_id ?? 1

		this.session.currency = this.$store.state.user.currency ?? 'GBP'
	},
}
</script>

<style scoped>

</style>