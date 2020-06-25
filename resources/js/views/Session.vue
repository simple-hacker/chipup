<template>
	<div class="w-full grid grid-cols-4 gap-4 xxl:w-5/6 xxl:mx-auto">
		<!--
			STATS
		-->
		<div
			class="col-span-4 flex md:flex-row border-b-16 rounded-b flex-wrap"
			:class="profit < 0 ? 'border-red-500' : 'border-green-500'"
		>
			<!--
				PROFIT
			-->
			<div
				class="w-2/3 md:w-4/12 rounded-tl flex flex-col p-4 md:p-6"
				:class="profit < 0 ? 'card-red' : 'card-green'"
			>
				<h1
					class="uppercase font-extrabold tracking-wider"
					:class="(profit < 0) ? 'text-red-700' : 'text-green-700'"
				>
					Profit
				</h1>
				<div
					class="text-4xl sm:text-5xl xl:text-6xl font-extrabold -mt-3"
					:class="(profit < 0) ? 'text-red-800' : 'text-green-800'"
				>
					<number
						ref="profit"
						:from="0"
						:to="profit"
						:duration="2"
						:format="amount => $n(amount, { style: 'currency', currency: sessionCurrency })"
						easing="Power1.easeOut"
					/>
				</div>
			</div>
			<!--
				IMAGE
			-->
			<div class="w-1/3 md:w-2/12 rounded-tr md:rounded-none card-highlighted flex flex-col justify-center items-center p-3">
				<i
					class="fas fa-5x"
					:class="gameTypeImage"
				></i>
				<div
					v-if="session.game_type === 'tournament'"
					class="mt-3"
				>
					<div
						class="text-sm uppercase font-bold tracking-wide text-gray-300"
						v-html="tournamentPositionEntriesText">
					</div>
				</div>
			</div>
			<!--
				PROFIT/HOUR
			-->
			<div class="w-1/2 md:w-3/12 card-highlighted flex flex-col p-3">
				<h2 class="uppercase text-gray-200 font-extrabold tracking-wider">
					Profit/Hour
				</h2>
				<div class="flex items-baseline">
					<div
						class="text-3xl sm:text-4xl font-semibold"
						:class="(profitPerHour < 0) ? 'text-red-500' : 'text-green-500'"
					>
						<number
							ref="stats-profit-hour"
							:from="0"
							:to="profitPerHour"
							:duration="2"
							:format="amount => $n(amount, { style: 'currency', currency: sessionCurrency })"
							easing="Power1.easeOut"
						/>
					</div>
					<span class="ml-2 uppercase text-sm text-gray-200 font-extrabold tracking-wider">/ hr </span>
				</div>
				<div class="flex flex-col mb-2">
					<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Duration</span>
					<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="formatDuration(duration)"></span>
				</div>
			</div>
			<!--
				ROI
			-->
			<div class="w-1/2 md:w-3/12 md:rounded-tr card-highlighted flex flex-col p-3">
				<h2 class="uppercase text-gray-200 font-extrabold tracking-wider">
					ROI
				</h2>
				<div
					class="text-3xl sm:text-4xl font-semibold"
					:class="(roi < 0) ? 'text-red-500' : 'text-green-500'"
				>
					<number
						ref="roi"
						:from="0"
						:to="roi"
						:duration="2"
						:format="(number) => number.toFixed(2)+'%'"
						easing="Power1.easeOut"
					/>
				</div>
				<div class="flex flex-col mb-2">
					<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Total Buy Ins</span>
					<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="$n(buyInsTotal, { style: 'currency', currency: sessionCurrency })"></span>
				</div>
				<div class="flex flex-col">
					<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Total Cashes</span>
					<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="$n(cashOutTotal, { style: 'currency', currency: sessionCurrency })"></span>
				</div>
			</div>
		</div>
		<!--
			DETAILS
		-->
		<div class="col-span-4 md:col-span-2 md:row-span-3 flex flex-col rounded-lg card p-3 text-lg text-white">
			<div class="mb-2">
				<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Played</h2>
				<div class="bg-gray-500 rounded shadow p-1">
					<!--
						STAKE
					-->
					<div
						v-if="session.stake"
						class="flex items-center p-1 md:p-2"
					>
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-coins"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.stake.stake"></span>
							<div v-if="editing" class="flex flex-col">
								<select
									v-model="editSession.stake_id"
									class="p-1 md:p-2 bg-gray-450 text-lg"
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
					<div class="flex items-center p-1 md:p-2">
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-stream"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="`${session.limit.limit} ${session.variant.variant}`"></span>
							<div v-if="editing" class="flex w-full">
								<div class="flex flex-1 flex-col mr-1">
									<select
										v-model="editSession.limit_id"
										class="p-1 md:p-2 bg-gray-450 text-lg"
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
								<div class="flex flex-1 flex-col ml-1">
									<select
										v-model="editSession.variant_id"
										class="p-1 md:p-2 bg-gray-450 text-lg"
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
						class="flex items-center p-1 md:p-2"
					>
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-user-friends"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.table_size.table_size"></span>
							<div v-if="editing" class="flex flex-col">
								<select
									v-model="editSession.table_size_id"
									class="p-1 md:p-2 bg-gray-450 text-lg"
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
				</div>
			</div>
			<div class="mb-2">
				<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Played At</h2>
				<div class="bg-gray-500 rounded shadow p-1">
					<!--
						LOCATION
					-->
					<div class="flex items-center p-1 md:p-2">
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-map-marker-alt"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.location"></span>
							<div v-if="editing" class="flex flex-col">
								<input
									type="text"
									v-model="editSession.location"
									placeholder="Location"
									class="p-1 md:p-2 bg-gray-450 text-lg"
									:class="{'error-input' : errors.location}"
									@input="delete errors.location"
								/>
								<span v-if="errors.location" class="error-message">{{ errors.location[0] }}</span>
							</div>						
						</div>
					</div>
				</div>
			</div>
			<div
				v-if="session.game_type === 'tournament'"
				class="mb-2"
			>
				<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Tournament Info</h2>
				<div class="bg-gray-500 rounded shadow p-1">
					<!--
						TOURNAMENT NAME
					-->
					<div
						v-if="session.name || (session.game_type === 'tournament' && editing)"
						class="flex items-center p-1 md:p-2"
					>
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-star"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="session.name"></span>
							<div v-if="editing" class="flex flex-col">
								<input
									type="text"
									v-model="editSession.name"
									placeholder="Tournament name"
									class="p-1 md:p-2 bg-gray-450 text-lg"
									:class="{'error-input' : errors.name}"
									@input="delete errors.name"
								/>
								<span v-if="errors.name" class="error-message">{{ errors.name[0] }}</span>
							</div>						
						</div>
					</div>
					<!--
						PRIZE POOL
					-->
					<div
						v-if="session.game_type === 'tournament'"
						class="flex items-center p-1 md:p-2"
					>
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-money-bill-wave"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="`${$n(session.prize_pool, { style: 'currency', currency: sessionCurrency })} prize pool`"></span>
							<div v-if="editing" class="flex flex-col">
								<currency-input
									v-model="editSession.prize_pool"
									class="p-1 md:p-2 bg-gray-450 text-lg"
									:class="{'error-input' : errors.prize_pool}"
									:currency="editSession.currency"
									placeholder="Prize Pool"
									:locale="user.locale"
									:distraction-free="false"
									:allow-negative="false"
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
						class="flex items-center p-1 md:p-2"
					>
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-medal"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="$n(session.position)"></span>
							<div v-if="editing" class="flex flex-col">
								<currency-input
									v-model="editSession.position"
									class="p-1 md:p-2 bg-gray-450 text-lg"
									:class="{'error-input' : errors.position}"
									:currency="null"
									:precision="0"
									placeholder="Position"
									:locale="user.locale"
									:distraction-free="false"
									:allow-negative="false"
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
						class="flex items-center p-1 md:p-2"
					>
						<div class="w-1/6 flex justify-center">
							<i class="fas fa-users"></i>
						</div>
						<div class="w-full">
							<span v-if="!editing" v-text="`${$n(session.entries)} entries`"></span>
							<div v-if="editing" class="flex flex-col">
								<currency-input
									v-model="editSession.entries"
									class="p-1 md:p-2 bg-gray-450 text-lg"
									:class="{'error-input' : errors.entries}"
									:currency="null"
									:precision="0"
									placeholder="Entries"
									:locale="user.locale"
									:distraction-free="false"
									:allow-negative="false"
									@input="delete errors.entries"
								/>
								<span v-if="errors.entries" class="error-message">{{ errors.entries[0] }}</span>
							</div>						
						</div>
					</div>
				</div>
			</div>
			<div class="mb-2">
				<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Times</h2>
				<div class="bg-gray-500 rounded shadow p-1">
					<!--
						START TIME
					-->
					<div class="flex items-center p-1 md:p-2">
						<div class="w-1/6 flex justify-center">
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
									:max-datetime="maxStartDateTime"
									auto
									placeholder="Start Date and Time"
									title="Start Date and Time"
									class="theme-green"
									:input-class="{'error-input' : errors.start_time, 'p-1 bg-gray-450' : true}"
									@input="delete errors.start_time"
								></datetime>
								<span v-if="errors.start_time" class="error-message">{{ errors.start_time[0] }}</span>
							</div>
						</div>
					</div>
					<!--
						END TIME
					-->
					<div class="flex items-center p-1 md:p-2">
						<div class="w-1/6 flex justify-center">
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
									:min-datetime="editSession.start_time"
									:max-datetime="maxDateTime"
									auto
									placeholder="End Date and Time"
									title="End Date and Time"
									class="theme-green"
									:input-class="{'error-input' : errors.end_time, 'p-1 bg-gray-450' : true}"
									@input="delete errors.end_time"
								></datetime>
								<span v-if="errors.end_time" class="error-message">{{ errors.end_time[0] }}</span>
							</div>
						</div>
					</div>	
				</div>
			</div>
			<!--
				COMMENTS
			-->
			<div
				v-if="session.comments || editing"
				class="flex flex-col flex-1"
			>
				<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1">Comments</h2>
				<div class="bg-gray-500 rounded shadow p-2 flex-1 text-base">
					<div
						v-if="!editing"
						v-text="editSession.comments"
					></div>
					<textarea
						v-if="editing"
						v-model="editSession.comments"
						name="comments" cols="30" rows="5"
						class="bg-gray-450"
						:class="{'error-input' : errors.comments}"
						placeholder="Comments"
						@input="delete errors.comments"
					></textarea>
					<span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
				</div>
			</div>
			<div class="flex mt-3 pin-b">
				<button @click.prevent="deleteSession" type="button" class="btn btn-red mr-1 py-2 px-3 md:py-3 md:px-4"><i class="fas fa-trash"></i></button>
				<button v-if="!editing" @click.prevent="editing = true" type="button" class="btn btn-green mr-1 py-2 px-3 md:py-3 md:px-4"><i class="fas fa-edit mr-2"></i><span>Edit</span></button>
				<div v-if="editing" class="flex">
					<button @click.prevent="saveSession" type="button" class="btn btn-green mr-1 py-2 px-3 md:py-3 md:px-4"><i class="fas fa-edit mr-2"></i><span>Save Changes</span></button>
					<div @click.prevent="cancelChanges" class="btn btn-gray py-2 px-3 md:py-3 md:px-4"><i class="fas fa-undo mr-2"></i><span>Cancel</span></div>
				</div>
			</div>
		</div>
		<!--
			CASH BUY INS
		-->
		<div
			v-if="session.game_type === 'cash_game'"
			class="col-span-4 md:col-span-2 flex flex-col card"
		>
			<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1 p-1">Buy Ins</h2>
			<div
				v-for="buy_in in session.buy_ins"
				:key="buy_in.id"
				class="mb-2"
			>
				<transaction-summary :transaction="buy_in" :transaction-type="'buyin'" :game-id="session.id"></transaction-summary>
			</div>
			<div
				@click="addTransaction('buyin', { amount: 0, currency: sessionCurrency })"
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
			v-if="session.game_type === 'tournament'" 
			class="col-span-4 md:col-span-2 flex flex-col card"
		>
			<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1 p-1">Buy In</h2>
			<transaction-summary v-if="session.buy_in" :transaction="session.buy_in" :transaction-type="'buyin'" :game-id="session.id"></transaction-summary>
			<div
				v-if="!session.buy_in"
				@click="addTransaction('buyin', { amount: 0, currency: sessionCurrency })"
				class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
			>
				<i class="fas fa-plus-circle mr-2"></i>
				<span>Add Buy In</span>
			</div>
		</div>
		<!--
			CASH OUT
		-->
		<div
			class="col-span-4 md:col-span-2 flex flex-col card"
		>
			<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1 p-1">Cash Out</h2>
			<transaction-summary v-if="session.cash_out" :transaction="session.cash_out" :transaction-type="'cashout'" :game-id="session.id"></transaction-summary>
			<div
				v-if="!session.cash_out"
				@click="addTransaction('cashout', { amount: 0, currency: sessionCurrency })"
				class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-green-500 hover:border-green-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
			>
				<i class="fas fa-plus-circle mr-2"></i>
				<span>Add Cash Out</span>
			</div>
		</div>
		<!--
			EXPENSES
		-->
		<div
			class="col-span-4 md:col-span-2 flex flex-col card"
		>
			<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1 p-1">Expenses</h2>
			<div
				v-for="expense in session.expenses"
				:key="expense.id"
				class="mb-2"
			>
				<transaction-summary :transaction="expense" :transaction-type="'expense'" :game-id="session.id"></transaction-summary>
			</div>
			<div
				@click="addTransaction('expense', { amount: 0, currency: sessionCurrency, comments: '' })"
				class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
			>
				<i class="fas fa-plus-circle mr-2"></i>
				<span>Add Expense</span>
			</div>
		</div>
		<!--
			REBUYS
		-->
		<div
			v-if="session.game_type === 'tournament'"
			class="col-span-4 md:col-span-2 flex flex-col card"
		>
			<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1 p-1">Rebuys</h2>
			<div
				v-for="rebuy in session.rebuys"
				:key="rebuy.id"
				class="mb-2"
			>
				<transaction-summary :transaction="rebuy" :transaction-type="'rebuy'" :game-id="session.id"></transaction-summary>
			</div>
			<div
				@click="addTransaction('rebuy', { amount: 0, currency: sessionCurrency })"
				class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
			>
				<i class="fas fa-plus-circle mr-2"></i>
				<span>Add Rebuy</span>
			</div>
		</div>
		<!--
			ADD ONS
		-->
		<div
			v-if="session.game_type === 'tournament'"
			class="col-span-4 md:col-span-2 flex flex-col card"
		>
			<h2 class="uppercase text-gray-200 font-extrabold tracking-wider mb-1 p-1">Add Ons</h2>
			<div
				v-for="add_on in session.add_ons"
				:key="add_on.id"
				class="mb-2"
			>
				<transaction-summary :transaction="add_on" :transaction-type="'addon'" :game-id="session.id"></transaction-summary>
			</div>
			<div
				@click="addTransaction('addon', { amount: 0, currency: sessionCurrency })"
				class="w-full rounded bg-gray-500 hover:bg-gray-450 border-b-4 border-red-500 hover:border-red-400 shadow p-3 md:p-4 cursor-pointer text-white text-center"
			>
				<i class="fas fa-plus-circle mr-2"></i>
				<span>Add Add On</span>
			</div>
		</div>
		<!-- SPACER FOR MOBILE -->
		<div class="col-span-4 mb-2"></div>
	</div>
</template>

<script>
import moment from 'moment'
import 'moment-duration-format'
import { mapState, mapActions, mapGetters } from 'vuex'

import { CurrencyInput } from 'vue-currency-input'

import TransactionSummary from '@components/Transaction/TransactionSummary'
import TransactionDetails from '@components/Transaction/TransactionDetails'

export default {
	name: 'Session',
	components: { TransactionSummary, TransactionDetails, CurrencyInput },
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
		...mapState(['stakes', 'limits', 'variants', 'table_sizes', 'user']),
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
		sessionCurrency() {
			// Need to refer to this for session currency because when we delete a session, it tries to rerender
			// the Session.vue DOM even though we redirect to Sessions.vue
			// This results in trying to display all currency values using session.currency which is undefined and so
			// get lots of errors in Chrome Dev Tools.
			return this.session?.currency ?? this.$store.state?.user?.currency ?? 'GBP'
		},
		maxStartDateTime() {
			return moment(this.editSession.end_time).format() < moment().format() ? moment(this.editSession.end_time).format() : moment().format()
		},
		profit() {
			return this.session?.profit ?? 0
		},
		roi() {
			const buyInsTotal = (this.buyInsTotal < 1) ? 1 : this.buyInsTotal
			return (this.profit / buyInsTotal) * 100
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
			return this.session?.cash_out?.amount ?? 0
		},
		duration() {
			const end_time = moment.utc(this.session.end_time)
			const start_time = moment.utc(this.session.start_time)
			return end_time.diff(start_time, 'hours', true)
		},
		profitPerHour() {
			return (this.profit / this.duration).toFixed(2)
		},
		gameTypeImage() {
			switch(this.session.game_type) {
				case 'cash_game':
					return 'fa-money-bill'
					break;
				case 'tournament':
					return 'fa-trophy'
					break;
				default:
					return 'fa-star'
			}
		},
		tournamentPositionEntriesText() {
			if (this.session.position > 0 && this.session.entries > 0) {
				return `Finished <span class="text-gray-100 text-base">${this.session.position}</span> out of <span class="text-gray-100 text-base">${this.session.entries}</span> entries`
			}

			if (this.session.position > 0) {
				return `Finished <span class="text-gray-100 text-base">${this.session.position}</span>`
			}

			if (this.session.entries > 0) {
				return `<span class="text-gray-100 text-base">${this.session.entries}</span> entries`
			}
		}
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
		formatDuration(time) {
			return moment.duration(time, 'hours').format("h [hours] m [mins]")
		},
		formatDate(date) {
			return moment.utc(date).local().format("dddd Do MMMM, HH:mm")
		},
		addTransaction(transactionType, transaction) {
			const modalClass = (transactionType === 'cashout') ? 'modal-green' : 'modal-red'
			this.$modal.show(TransactionDetails, {
                // Modal props
                transaction,
				transactionType,
				gameId: this.session.id,
                gameType: this.session.game_type
            }, {
                // Modal Options
                classes: ['modal', modalClass],
                height: 'auto',
                width: '95%',
                maxWidth: 600,
            })
		}
	},
}
</script>

<style>

</style>