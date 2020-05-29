<template>
	<div class="bg-card rounded border border-muted-dark p-4 text-white">
		<h1 class="text-lg md:text-2xl">Start a new cash game session.</h1>
    	<hr class="mb-4 border border-muted-dark">
		<div class="flex flex-col">
			<div>
				<p class="text-base md:text-lg mb-2">What are you playing?</p>
				<div class="flex flex-wrap">
					<div class="flex flex-col w-1/2 lg:w-1/4 p-1">
						<select
							v-model="session.stake_id"
							:class="{ 'error-input' : errors.stake_id }"
							@input="delete errors.stake_id"
						>
							<option v-for="stake in stakes" :key="stake.id" :value="stake.id">{{ stake.stake }}</option>
						</select>
						<span v-if="errors.stake_id" class="error-message">{{ errors.stake_id[0] }}</span>
					</div>
					<div class="flex flex-col w-1/2 lg:w-1/4 p-1">
						<select
							v-model="session.limit_id"
							:class="{ 'error-input' : errors.limit_id }"
							@input="delete errors.limit_id"
						>
							<option v-for="limit in limits" :key="limit.id" :value="limit.id">{{ limit.limit }}</option>
						</select>
						<span v-if="errors.limit_id" class="error-message">{{ errors.limit_id[0] }}</span>
					</div>
					<div class="flex flex-col w-1/2 lg:w-1/4 p-1">
						<select
							v-model="session.variant_id"
							:class="{ 'error-input' : errors.variant_id }"
							@input="delete errors.variant_id"
						>
							<option v-for="variant in variants" :key="variant.id" :value="variant.id">{{ variant.variant }}</option>
						</select>
						<span v-if="errors.variant_id" class="error-message">{{ errors.variant_id[0] }}</span>
					</div>
					<div class="flex flex-col w-1/2 lg:w-1/4 p-1">
						<select
							v-model="session.table_size_id"
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
			<div class="flex flex-col mt-4">
				<div class="flex">
					<datetime
						v-model="start_time"
						input-id="start_time"
						type="datetime"
						:minute-step="5"
						:flow="['time']"
						format="HH:mm"
						class="w-full theme-green mr-1"
						placeholder="Start At"
						title="Start Live Session At"
						:input-class="{'error-input' : errors.end_time, 'bg-green-700 border-none text-white font-bold p-4 uppercase text-center cursor-pointer' : true}"
						@input="delete errors.end_time"	
					>
						<template slot="button-confirm">
							<div @click.prevent="startSessionAt">
								Start Session
							</div>
						</template>
					</datetime>
					<button
						@click.prevent="startSessionNow"
						type="button"
						class="w-full bg-green-600 border border-green-700 hover:bg-green-700 rounded p-4 uppercase text-white font-bold text-center ml-1"
					>
						Start Now
					</button>
				</div>
				<span v-if="errors.start_time" class="error-message">{{ errors.start_time[0] }}</span>
			</div>
		</div>
	</div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import moment from 'moment'

export default {
	name: 'StartCashGame',
    data() {
		return {
			errors: {},
			session: {
				stake_id: 1,
				limit_id: 1,
				variant_id: 1,
				table_size_id: 1,
				location: '',
				amount: 0,
			},
			start_time: '',
		}
	},
	computed: {
		...mapState(['stakes', 'limits', 'variants', 'table_sizes']),
	},
    methods: {
		...mapActions('live', ['startLiveSession']),
		startSessionNow() {
			this.startLiveSession(this.session)
			.then(response => {
				this.$snotify.success('Started live cash game.')
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors
			})
		},
		startSessionAt() {
			// NOTE: setTimeout is horrible, but vue-datetime does not provide a callback feature
			// Had to replace the Ok button with my own in the template which triggers this function on click
			// But this function is fired before vue-datetime has updated the v-model
			// Adding a slight delay so that start_time is correct, but this is dodgy
			// TODO:  Look in to creating a callback feature and submitting a pull request to the open source project.
			// Looks okay enough to do, need to edit Datetime.vue confirm and cancel methods to trigger a callback provided
			// Don't know how to do the tests though
			setTimeout(() => {
				this.startLiveSession({
					...this.session,
					start_time: moment(this.start_time).format("YYYY-MM-DD HH:mm:ss"),
				})
				.then(response => {
					this.$snotify.success('Started live cash game.')
				})
				.catch(error => {
					this.$snotify.error('Error: '+error.response.data.message)
					this.errors = error.response.data.errors
				})
			}, 100)
		}
    }
}
</script>

<style scoped>

</style>