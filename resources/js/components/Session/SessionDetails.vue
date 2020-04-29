<template>
	<div class="flex flex-col w-full relative">
		<div class="absolute top-5 right-5">
			<button @click="$emit('close')" class="hover:text-muted-light cursor-pointer">
				<i class="fas fa-times-circle fa-2x"></i>
			</button>
		</div>
		<div class="text-center text-6xl font-bold"
			:class="(cash_game.profit > 0) ? 'text-green-500' : 'text-red-500'"
		>
			{{ formattedProfit }}
		</div>
		<div class="flex flex-col md:flex-row md:justify-around items-center text-xl">
			<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
					<i class="fas fa-map-marker-alt mr-3"></i>
					<span v-if="!editing" v-text="cash_game.location"></span>
					<input v-if="editing" type="text" class="p-1 text-lg" v-model="cash_game.location">
			</div>
			<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
				<i class="fas fa-stream mr-3"></i>
				<span v-if="!editing" v-text="gameDetails"></span>
				<select
					v-if="editing" v-model="cash_game.stake_id"
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
				<select
					v-if="editing" v-model="cash_game.limit_id"
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
					v-if="editing" v-model="cash_game.variant_id"
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
			<div class="mb-2 md:mb-0 flex items-center p-0 md:p-2">
				<i class="fas fa-users mr-3"></i>
				<span v-if="!editing" v-text="cash_game.table_size.table_size"></span>
				<select
					v-if="editing" v-model="cash_game.table_size_id"
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
		</div>
		<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Session Start</div>
				<div v-if="!editing" class="self-center" v-text="formatDate(cash_game.start_time)"></div>
				<datetime
					v-if="editing"
					v-model="cash_game.start_time"
					input-id="start_time"
					type="datetime"
					:minute-step="5"
					:value="'2019-12-25T11:11:11Z'"
					input-class="self-center p-1"
					auto
					title="Bankroll Transaction Date"
					class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
					:class="errors.start_time ? 'border-red-700' : 'border-gray-400'"
				></datetime>
			</div>
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Session End</div>
				<div v-if="!editing" class="self-center" v-text="formatDate(cash_game.end_time)"></div>
				<datetime
					v-if="editing"
					v-model="cash_game.end_time"
					input-id="end_time"
					type="datetime"
					:minute-step="5"
					input-class="self-center p-1"
					auto
					title="Bankroll Transaction Date"
					class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
					:class="errors.end_time ? 'border-red-700' : 'border-gray-400'"
				></datetime>
			</div>
			<div class="col-span-6 md:col-span-2 flex md:flex-col justify-between bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Session Duration</div>
				<div class="self-center" v-text="runTime"></div>
			</div>
		</div>
		<div class="grid grid-cols-6 gap-2 md:gap-3 mt-2 md:mt-4">
			<!--
				BUY INS
			-->
			<div
				v-if="(cash_game.buy_ins && cash_game.buy_ins.length > 0) || editing" 
				class="col-span-6 md:col-span-2 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Buy Ins</div>
				<div class="flex flex-col self-center">
					<div v-if="!editing">
						<div
							v-for="buyIn in cash_game.buy_ins"
							:key="buyIn.id"
							v-text="formatCurrency(buyIn.amount / 100)"
						></div>
					</div>
					<div v-if="editing">
						<div
							v-for="buyIn in cash_game.buy_ins"
							:key="buyIn.id"
							class="flex"
						>
							<input v-model="buyIn.amount" type="text" class="p-1">
							<button @click="alert('delete')" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="far fa-trash-alt"></i></button>
						</div>
					</div>
				</div>
			</div>
			<!--
				CASH OUT
			-->
			<div
				v-if="cash_game.cash_out_model || editing"
				class="col-span-6 md:col-span-2 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Cash Out</div>
				<div class="flex flex-col self-center">
					<div v-if="!editing" v-text="formatCurrency(cash_game.cash_out_model.amount / 100)"></div>
					<div
						v-if="editing"
						class="flex"
					>
						<input v-model="cash_game.cash_out_model.amount" type="text" class="p-1">
						<button @click="alert('delete')" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="far fa-trash-alt"></i></button>
					</div>
				</div>
			</div>
			<!--
				EXPENSES
			-->
			<div
				v-if="(cash_game.expenses && cash_game.expenses.length > 0) || editing" 
				class="col-span-6 md:col-span-2 flex md:flex-col justify-start md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Expenses</div>
				<div class="flex flex-col self-center w-full">
					<div
						v-for="expense in cash_game.expenses"
						:key="expense.id"
						class="flex justify-end md:justify-around">
						<div class="order-last md:order-first" v-text="formatCurrency(expense.amount / 100)"></div>
						<div class="mr-3 md:mr-0" v-if="expense.comments" v-text="expense.comments">Comments</div>
					</div>
				</div>
			</div>
			<!--
				REBUYS
			-->
			<div
				v-if="(cash_game.rebuys && cash_game.rebuys.length > 0) || editing" 
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Rebuys</div>
				<div class="flex flex-col self-center">
					<div v-if="!editing">
						<div
							v-for="rebuy in cash_game.rebuys"
							:key="rebuy.id"
							v-text="formatCurrency(rebuy.amount / 100)"
						></div>
					</div>
					<div v-if="editing">
						<div
							v-for="rebuy in cash_game.rebuys"
							:key="rebuy.id"
							class="flex"
						>
							<input v-model="rebuy.amount" type="text" class="p-1">
							<button @click="alert('delete')" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="far fa-trash-alt"></i></button>
						</div>
					</div>
				</div>
			</div>
			<!--
				ADD ONS
			-->
			<div
				v-if="(cash_game.add_ons && cash_game.add_ons.length > 0) || editing" 
				class="col-span-6 md:col-span-3 flex md:flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 md:mb-2">Add Ons</div>
				<div class="flex flex-col self-center">
					<div v-if="!editing">
						<div
							v-for="addOn in cash_game.add_ons"
							:key="addOn.id"
							v-text="formatCurrency(addOn.amount / 100)"
						></div>
					</div>
					<div v-if="editing">
						<div
							v-for="addOn in cash_game.add_ons"
							:key="addOn.id"
							class="flex"
						>
							<input v-model="addOn.amount" type="text" class="p-1">
							<button @click="alert('delete')" class="ml-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm p-2"><i class="far fa-trash-alt"></i></button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="grid grid-cols-2 gap-2 md:gap-3 mt-2 md:mt-4">
			<div class="col-span-2 md:col-span-1 col-start-1 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Stats</div>
				<div class="flex flex-col border border-muted-dark rounded-lg">
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
			<div
				v-if="cash_game.comments  || editing"
				class="col-span-2 md:col-span-1 flex flex-col justify-between md:justify-start bg-card border border-muted-dark rounded-lg p-3">
				<div class="font-semibold md:border-b md:border-muted-dark md:p-1 mb-1 md:mb-2">Comments</div>
				<div
					v-if="!editing"
					v-text="cash_game.comments"
				></div>
				<textarea
					v-if="editing"
					v-model="cash_game.comments"
					name="comments" cols="30" rows="5"
				></textarea>
			</div>
		</div>
		<div class="flex justify-between mt-4 mb-2">
			<button @click.prevent="deleteSession" type="button" class="bg-red-500 hover:bg-red-600 focus:bg-red-600 rounded text-white text-sm px-4 py-2"><i class="fas fa-trash mr-3"></i><span>Delete</span></button>
			<button v-if="!editing" @click.prevent="editing = true" type="button" class="bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2"><i class="fas fa-edit mr-3"></i><span>Edit</span></button>
			<div v-if="editing">
				<button @click.prevent="cancelChanges" type="button" class="mr-2 rounded text-white border border-muted-dark hover:border-muted-light text-sm px-4 py-2"><span>Cancel</span></button>
				<button @click.prevent="saveSession" type="button" class="bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2"><i class="fas fa-edit mr-3"></i><span>Save Changes</span></button>
			</div>
		</div>
	</div>
</template>

<script>
import moment from 'moment'
import 'moment-duration-format'
import { mapState, mapActions } from 'vuex'

export default {
	name: 'SessionDetails',
	props: {
		session: Object
	},
	data() {
		return {
			editing: false,
			cash_game: {
				...this.session,
				start_time: moment(this.session.start_time).format(),
				end_time: moment(this.session.end_time).format()
			},
			errors: {}
		}
	},
	created() {
		this.cached_cash_game = { ...this.cash_game }
		console.log(this.cash_game.buy_ins)
	},
	computed: {
		...mapState(['stakes', 'limits', 'variants', 'table_sizes']),
		profit() {
			return this.cash_game.profit / 100
		},
		roi() {
			const buyInTotal = (this.buyInsTotal < 1) ? 1 : this.buyInsTotal
			return (this.profit / buyInTotal) * 100
		},
		buyInsTotal() {
			return this.cash_game.buy_ins.reduce((total, buy_in) => total + buy_in.amount, 0) / 100
		},
		formattedProfit() {
			return Vue.prototype.currency.format(this.profit);
		},
		gameDetails() {
			return "£"+this.cash_game.stake.small_blind+"/£"+this.cash_game.stake.big_blind+" "+this.cash_game.limit.limit+" "+this.cash_game.variant.variant
		},
		runTimeHours() {
			const end_time = moment(this.cash_game.end_time)
			const start_time = moment(this.cash_game.start_time)
			return end_time.diff(start_time, 'hours', true)
		},
		runTime() {
			const end_time = moment(this.cash_game.end_time)
			const start_time = moment(this.cash_game.start_time)
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
			this.cash_game = { ...this.cached_cash_game }
		},
		saveSession() {
			this.editing = false
			this.cached_cash_game = { ...this.cash_game }
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
                                this.$modal.hide('dialog');
                                this.$emit('close');
                                this.$snotify.warning('Successfully deleted cash game.');
                            })
                            .catch(error => {
                                this.$snotify.error('Error: '+error.response.data.message);
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
		}
	}
}
</script>

<style>

</style>