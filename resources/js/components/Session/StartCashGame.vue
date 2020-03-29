<template>
	<div>
		<h1 class="text-lg md:text-2xl">Start a new cash game session.</h1>
    	<hr class="mb-4 border border-muted-dark">
    	<form action="" @submit.prevent="startCashGame">
			<div class="flex flex-col">
				<div>
					<p class="text-base md:text-lg mb-2">What are you playing?</p>
					<div class="flex flex-wrap">
						<div class="w-1/2 lg:w-1/4 p-1">
							<select v-model="stake" class="w-full bg-muted-light rounded border border-muted-dark p-2 mb-2" :class="errors.stake_id ? 'border-red-700' : 'border-gray-400'">
							<option v-for="stake in stakes" :key="stake.id" :value="stake.id">{{ stake.stake }}</option>
							</select>
							<span v-if="errors.stake_id" class="text-xs text-red-700 mt-1 mb-2">{{ errors.stake_id[0] }}</span>
						</div>
						<div class="w-1/2 lg:w-1/4 p-1">
							<select v-model="limit" class="w-full bg-muted-light rounded border border-muted-dark p-2 mb-2" :class="errors.limit_id ? 'border-red-700' : 'border-gray-400'">
							<option v-for="limit in limits" :key="limit.id" :value="limit.id">{{ limit.limit }}</option>
							</select>
							<span v-if="errors.limit_id" class="text-xs text-red-700 mt-1 mb-2">{{ errors.limit_id[0] }}</span>
						</div>
						<div class="w-1/2 lg:w-1/4 p-1">
							<select v-model="variant" class="w-full bg-muted-light rounded border border-muted-dark p-2 mb-2" :class="errors.variant_id ? 'border-red-700' : 'border-gray-400'">
							<option v-for="variant in variants" :key="variant.id" :value="variant.id">{{ variant.variant }}</option>
							</select>
							<span v-if="errors.variant_id" class="text-xs text-red-700 mt-1 mb-2">{{ errors.variant_id[0] }}</span>
						</div>
						<div class="w-1/2 lg:w-1/4 p-1">
							<select v-model="table_size" class="w-full bg-muted-light rounded border border-muted-dark p-2 mb-2" :class="errors.table_size_id ? 'border-red-700' : 'border-gray-400'">
							<option v-for="table_size in table_sizes" :key="table_size.id" :value="table_size.id">{{ table_size.table_size }}</option>
							</select>
							<span v-if="errors.table_size_id" class="text-xs text-red-700 mt-1 mb-2">{{ errors.table_size_id[0] }}</span>
						</div>
					</div>
				</div>
				<div class="mt-3">
					<p class="text-base md:text-lg mb-2">Where are you playing?</p>
					<input v-model="location" type="text" placeholder="Enter location" class="w-full bg-muted-light border border-muted-dark rounded p-3" :class="errors.location ? 'border-red-700' : 'border-gray-400 mb-2'"/>
					<span v-if="errors.location" class="text-xs text-red-700">{{ errors.location[0] }}</span>
				</div>
				<div class="mt-3">
					<p class="text-base md:text-lg mb-2">What's your buyin?</p>
					<input v-model="buyin" type="number" step="0.01" min="0" class="w-full bg-muted-light border border-muted-dark rounded p-3" :class="errors.buyin ? 'border-red-700' : 'border-gray-400 mb-2'"/>
					<span v-if="errors.buyin" class="text-xs text-red-700">{{ errors.buyin[0] }}</span>        
				</div>
				<transition name="fade-slide" mode="out-in">
					<div v-if="live" key="live">
						<div class="mt-4">
							<button type="button" class="w-full bg-green-600 border border-green-700 hover:bg-green-700 p-4 uppercase text-white font-bold text-center">Start Live Cash Game</button>
						</div>
						<div class="mt-8 flex justify-center">
							<a @click.prevent="live = false" href="#" class="text-green-700 hover:text-green-800 text-sm font-bold">Switch to non live mode</a>
						</div>
					</div>
					<div v-else key="notLive">
						<div class="mt-4">
							<p class="text-base md:text-lg mb-2">When did this cash game start?</p>
							<datetime
								v-model="start_time"
								type="datetime"
								input-class="w-full p-3"
								:minute-step="5"
								auto
								title="Session start date and time"
								class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
								:class="errors.start_time ? 'border-red-700' : 'border-gray-400'"
							></datetime>
							<span v-if="errors.start_time" class="text-xs text-red-700 mt-1 mb-2">{{ errors.start_time[0] }}</span>        
						</div>
						<div class="mt-4">
							<p class="text-base md:text-lg mb-2">When did this cash game end?</p>
							<datetime
								v-model="end_time"
								type="datetime"
								input-class="w-full p-3"
								:minute-step="5"
								auto
								title="Session end date and time"
								class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
								:class="errors.end_time ? 'border-red-700' : 'border-gray-400'"
							></datetime>
							<span v-if="errors.end_time" class="text-xs text-red-700 mt-1 mb-2">{{ errors.end_time[0] }}</span>        
						</div>
						<div class="mt-4">
							<button type="button" class="w-full bg-green-500 border border-green-700 hover:bg-green-700 p-4 uppercase text-white font-bold text-center">Save cash game</button>
						</div>
						<div class="mt-8 flex justify-center">
							<a @click.prevent="live = true" href="#" class="text-green-500 hover:text-green-600 text-sm font-bold">Switch to live mode</a>
						</div>
					</div>
				</transition>
			</div>
		</form>
	</div>
</template>

<script>
export default {
	name: 'StartCashGame',
    data() {
		return {
			live: false,
			start_time: '',
			end_time: '',
			buyin: 0,
			errors: [],
			stake: 0,
			limit: 0,
			variant: 0,
			table_size: 0,
			location: '',
			stakes: [
				{id: 1, stake: '1/1'},
				{id: 2, stake: '1/2'},
				{id: 3, stake: '2/4'},
			],
			limits: [
				{id: 1, limit: 'No Limit'},
				{id: 2, limit: 'Pot Limit'},
				{id: 3, limit: 'Limit'},
			],
			variants: [
				{id: 1, variant: 'Holdem'},
				{id: 2, variant: 'Omaha'},
				{id: 3, variant: 'Stud8'},
			],
			table_sizes: [
				{id: 1, table_size: 'Full-Ring'},
				{id: 2, table_size: 'Mixed'},
				{id: 3, table_size: 'Heads Up'},
			],
		}
    },
    methods: {
		startCashGame: function () {
			alert('Starting new cash game');
		}
    }
}
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition-duration: 0.30s;
    transition-property: all;
    transition-timing-function: ease;
}

.fade-slide-enter,
.fade-slide-leave-active {
	opacity: 0;
	transform: translateY(20px);
}
</style>