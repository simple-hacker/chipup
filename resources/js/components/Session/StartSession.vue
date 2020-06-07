<template>
	<div class="bg-card rounded border border-muted-dark p-4 text-white">
		<h1 class="text-lg md:text-2xl">Start a new {{ game_type_label }}</h1>
    	<hr class="mb-4 border border-muted-dark">
		<div @click="switchGameType" class="text-center font-bold text-green-500 p-3 cursor-pointer">
			<span class="mr-3">Switch to {{ game_type_label_inverse }} Mode</span>
		</div>
		<div class="flex flex-col">
			<div
				v-show-slide="game_type === 'tournament'"
				class="mb-2">
				<p class="text-base md:text-lg mb-2">What's the name of the tournament?</p>
				<div class="flex flex-col">
					<input
						v-model="tournament.name"
						type="text"
						placeholder="Tournament name"
						:class="{ 'error-input' : errors.name }"
						@input="delete errors.name"
					/>
					<span v-if="errors.name" class="error-message">{{ errors.name[0] }}</span>
				</div>
			</div>
			<div>
				<p class="text-base md:text-lg mb-2">What are you playing?</p>
				<div class="flex flex-wrap">
					<div
						v-if="game_type === 'cash_game'"
						class="flex flex-col w-1/2 lg:w-1/4 p-1">
						<select
							v-model="cash_game.stake_id"
							:class="{ 'error-input' : errors.stake_id }"
							@input="delete errors.stake_id"
						>
							<option v-for="stake in stakes" :key="stake.id" :value="stake.id">{{ stake.stake }}</option>
						</select>
						<span v-if="errors.stake_id" class="error-message">{{ errors.stake_id[0] }}</span>
					</div>
					<div
						class="flex flex-col w-1/2 p-1"
						:class="game_type === 'cash_game' ? 'lg:w-1/4' : 'lg:w-1/2'"
					>
						<select
							v-model="session.limit_id"
							:class="{ 'error-input' : errors.limit_id }"
							@input="delete errors.limit_id"
						>
							<option v-for="limit in limits" :key="limit.id" :value="limit.id">{{ limit.limit }}</option>
						</select>
						<span v-if="errors.limit_id" class="error-message">{{ errors.limit_id[0] }}</span>
					</div>
					<div
						class="flex flex-col w-1/2 p-1"
						:class="game_type === 'cash_game' ? 'lg:w-1/4' : 'lg:w-1/2'"
					>
						<select
							v-model="session.variant_id"
							:class="{ 'error-input' : errors.variant_id }"
							@input="delete errors.variant_id"
						>
							<option v-for="variant in variants" :key="variant.id" :value="variant.id">{{ variant.variant }}</option>
						</select>
						<span v-if="errors.variant_id" class="error-message">{{ errors.variant_id[0] }}</span>
					</div>
					<div
						v-if="game_type === 'cash_game'"
						class="flex flex-col w-1/2 lg:w-1/4 p-1">
						<select
							v-model="cash_game.table_size_id"
							:class="{ 'error-input' : errors.table_size_id }"
							@input="delete errors.table_size_id"
						>
							<option v-for="table_size in table_sizes" :key="table_size.id" :value="table_size.id">{{ table_size.table_size }}</option>
						</select>
						<span v-if="errors.table_size_id" class="error-message">{{ errors.table_size_id[0] }}</span>
					</div>
				</div>
			</div>
			<div class="mt-3">
				<p class="text-base md:text-lg mb-2">Where are you playing?</p>
				<div class="flex flex-col">
					<input
						v-model="session.location"
						type="text"
						placeholder="Enter location"
						:class="{ 'error-input' : errors.location }"
						@input="delete errors.location"
					/>
					<span v-if="errors.location" class="error-message">{{ errors.location[0] }}</span>
				</div>
			</div>
			<div class="mt-3">
				<p class="text-base md:text-lg mb-2">What's your buyin?</p>
				<div class="flex flex-col">
					<input
						v-model="session.amount"
						type="number"
						step="0.01"
						min="0"
						:class="{ 'error-input' : errors.amount }"
						@input="delete errors.amount"
					/>
					<span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>        
				</div>
			</div>
			<div class="mt-3">
				<p class="text-base md:text-lg mb-2">When are you starting?</p>
				<div class="flex flex-col">
					<datetime
						v-model="session.start_time"
						input-id="start_time"
						type="datetime"
						:minute-step="5"
						:flow="['time']"
						placeholder="Start Time"
						title="Start Live Session At"
						auto
						class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
						:input-class="{'error-input' : errors.start_time, 'p-3' : true}"
						@input="delete errors.start_time"	
					>
					</datetime>
					<span v-if="errors.start_time" class="error-message">{{ errors.start_time[0] }}</span>    
				</div>
			</div>
			<div class="flex mt-4">
				<button
					@click.prevent="startSession"
					type="button"
					class="w-full bg-green-600 border border-green-700 hover:bg-green-700 rounded p-4 uppercase text-white font-bold text-center ml-1"
					v-text="`Start ${game_type_label}`"
				>
				</button>				
			</div>
		</div>
	</div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import moment from 'moment'

export default {
	name: 'StartSession',
    data() {
		return {
			errors: {},
			game_type: 'cash_game',
			session: {
				location: '',
				limit_id: 1,
				variant_id: 1,
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
		...mapState(['stakes', 'limits', 'variants', 'table_sizes']),
		liveSession() {
			if (this.game_type === 'cash_game') {
				return {...this.session, ...this.cash_game}
			} else if (this.game_type === 'tournament') {
				return {...this.session, ...this.tournament}
			} else {
				return {}
			}
		},
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
		...mapActions('live', ['startLiveSession']),
		switchGameType() {
			if (this.game_type === 'cash_game') {
				this.game_type = 'tournament'
			} else {
				this.game_type = 'cash_game'
			}
		},
		startSession() {
			this.startLiveSession({
				game_type: this.game_type,
				session: this.liveSession
			})
			.then(response => {
				this.$snotify.success('Good luck!')
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors
			})
		},
    }
}
</script>

<style scoped>

</style>