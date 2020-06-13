<template>
	<div class="flex flex-col md:flex-row">
		<!--
			CURRENT BANKROLL
		-->
		<div
			class="w-full md:w-1/3 xl:w-1/5 rounded-t md:rounded-none md:rounded-l flex flex-col p-4 md:p-6"
			:class="(bankroll < 0) ? 'card-red' : 'card-green'"
		>
			<h1
				class="uppercase font-extrabold tracking-wider"
				:class="(bankroll < 0) ? 'text-red-700' : 'text-green-700'"
			>
				Current Bankroll
			</h1>
			<div
				class="text-6xl md:text-5xl xxl:text-6xl font-extrabold -mt-3"
				:class="(bankroll < 0) ? 'text-red-800' : 'text-green-800'"
			>
				<number
					ref="dashboard-bankroll"
					:from="0"
					:to="bankroll"
					:duration="2"
					:format="amount => formatCurrency(amount)"
					easing="Power1.easeOut"
				/>
			</div>
		</div>
		<!--
			STATS
		-->
		<div class="w-full md:w-2/3 xl:w-4/5 flex flex-wrap text-white">
			<!--
				PROFIT
			-->
			<div class="w-full md:w-1/2 xl:w-1/4 card-highlighted flex md:flex-col p-3">
				<div class="flex flex-col flex-1">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider">
						Total Profit
					</h2>
					<div
						class="text-3xl sm:text-4xl font-semibold"
						:class="(totalProfit < 0) ? 'text-red-500' : 'text-green-500'"
					>
						<number
							ref="stats-profit"
							:from="0"
							:to="totalProfit"
							:duration="2"
							:format="(amount) => formatCurrency(amount)"
							easing="Power1.easeOut"
						/>
					</div>
				</div>
				<div class="flex flex-col flex-1">
					<div class="flex flex-col mb-2">
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Cash Games Profit</span>
						<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="formatCurrency(cashGameProfit)"></span>
					</div>
					<div class="flex flex-col">
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Tournament Profit</span>
						<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="formatCurrency(tournamentProfit)"></span>
					</div>
				</div>
			</div>
			<!--
				PROFIT/HOUR
			-->
			<div class="w-full md:w-1/2 xl:w-1/4 card-highlighted md:rounded-tr xl:rounded-none flex md:flex-col p-3">
				<div class="flex flex-col flex-1">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider">
						Profit/Hour
					</h2>
					<div
						class="text-3xl sm:text-4xl font-semibold"
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
					</div>
				</div>
				<div class="flex flex-col flex-1">
					<div class="flex flex-col mb-2">
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Average Duration</span>
						<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="formatDuration(averageDuration)"></span>
					</div>
					<div class="flex flex-col">
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Total Duration</span>
						<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="formatDuration(totalDuration)"></span>
					</div>
				</div>
			</div>
			<!--
				PROFIT/SESSION
			-->
			<div class="w-full md:w-1/2 xl:w-1/4 card-highlighted rounded-bl md:rounded-none flex md:flex-col p-3">
				<div class="flex flex-col flex-1">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider">
						Profit/Session
					</h2>
					<div
						class="text-3xl sm:text-4xl font-semibold"
						:class="(profitPerSession < 0) ? 'text-red-500' : 'text-green-500'"
					>
						<number
							ref="stats-profit-session"
							:from="0"
							:to="profitPerSession"
							:duration="2"
							:format="(amount) => formatCurrency(amount)"
							easing="Power1.easeOut"
						/>
					</div>
				</div>
				<div class="flex flex-col flex-1">
					<div class="flex items-baseline mb-1">
						<span class="text-base uppercase font-bold tracking-wide text-gray-100 mr-1" v-text="sessions.length"></span>
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Sessions</span>
					</div>
					<div class="flex items-baseline mb-1">
						<span class="text-base uppercase font-bold tracking-wide text-gray-100 mr-1" v-text="cash_games.length"></span>
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Cash Games</span>
					</div>
					<div class="flex items-baseline">
						<span class="text-base uppercase font-bold tracking-wide text-gray-100 mr-1" v-text="tournaments.length"></span>
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Tournaments</span>
					</div>
				</div>
			</div>
			<!--
				AVERAGE ROI
			-->
			<div class="w-full md:w-1/2 xl:w-1/4 card-highlighted rounded-br xl:rounded-r flex md:flex-col p-3">
				<div class="flex flex-col flex-1">
					<h2 class="uppercase text-gray-200 font-extrabold tracking-wider">
						ROI
					</h2>
					<div
						class="text-3xl sm:text-4xl font-semibold"
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
					</div>
				</div>
				<div class="flex flex-col flex-1">
					<div class="flex flex-col mb-2">
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Total Buy Ins</span>
						<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="formatCurrency(totalBuyIns)"></span>
					</div>
					<div class="flex flex-col">
						<span class="text-sm uppercase font-bold tracking-wide text-gray-300">Total Cashes</span>
						<span class="text-base uppercase font-bold tracking-wide text-gray-100" v-text="formatCurrency(totalCashes)"></span>
					</div>
				</div>
			</div>
		</div>
		<!-- <div class="border border-muted-dark rounded-lg">
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Sessions</span>
				<span class="text-lg">
					<number
						ref="stats-sessions"
						:from="0"
						:to="numberOfSessions"
						:duration="2"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Profit</span>
				<span
					class="text-lg font-semibold"
					:class="(totalProfit < 0) ? 'text-red-500' : 'text-green-500'"
				>
					<number
						ref="stats-profit"
						:from="0"
						:to="totalProfit"
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
				<span>Total Buy Ins</span>
				<span class="text-lg font-semibold">
					<number
						ref="stats-total-buyins"
						:from="0"
						:to="totalBuyIns"
						:duration="2"
						:format="(amount) => formatCurrency(amount)"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Total Cashes</span>
				<span class="text-lg font-semibold">
					<number
						ref="stats-total-cashes"
						:from="0"
						:to="totalCashes"
						:duration="2"
						:format="(amount) => formatCurrency(amount)"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Profit / session</span>
				<span
					class="text-lg font-semibold"
					:class="(profitPerSession < 0) ? 'text-red-500' : 'text-green-500'"
				>
					<number
						ref="stats-profit-session"
						:from="0"
						:to="profitPerSession"
						:duration="2"
						:format="(amount) => formatCurrency(amount)"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Average ROI</span>
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
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Average duration</span>
				<span class="text-lg">
					<number
						ref="stats-profit-session"
						:from="0"
						:to="averageDuration"
						:duration="2"
						:format="(time) => formatDuration(time)"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Total duration</span>
				<span class="text-lg">
					<number
						ref="stats-profit-session"
						:from="0"
						:to="totalDuration"
						:duration="2"
						:format="(time) => formatDuration(time)"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
		</div> -->
	</div>
</template>

<script>
import moment from 'moment'

import { mapState, mapGetters } from 'vuex'

export default {
	name: 'Statistics',
	computed: {
		...mapState('cash_games', ['cash_games']),
		...mapState('tournaments', ['tournaments']),
		...mapGetters('sessions', [
			'sessions',
			'numberOfSessions',
			'totalProfit',
			'totalDuration',
			'averageDuration',
			'profitPerHour',
			'profitPerSession',
			'totalBuyIns',
			'totalCashes',
			'roi',
		]),
		...mapGetters('bankroll', ['bankroll']),
		...mapGetters('cash_games', ['cashGameProfit']),
		...mapGetters('tournaments', ['tournamentProfit']),
	},
	methods: {
		formatCurrency(amount) {
			return this.$currency.format(amount)
		},
		formatDuration(time) {
			return moment.duration(time, 'hours').format("d [days] h [hours] m [mins]")
		},
	},
}
</script>

<style>

</style>