<template>
	<div class="flex flex-col text-white">
		<div class="border border-muted-dark rounded-lg">
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
					:class="(averageROI < 0) ? 'text-red-500' : 'text-green-500'"
				>
					<number
						ref="stats-average-roi"
						:from="0"
						:to="averageROI"
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
		</div>
	</div>
</template>

<script>
import moment from 'moment'

import { mapGetters } from 'vuex'

export default {
	name: 'StatsTable',
	computed: {
		...mapGetters('filtered_sessions', [
			'numberOfSessions',
			'totalProfit',
			'totalDuration',
			'averageDuration',
			'profitPerHour',
			'profitPerSession',
			'totalBuyIns',
			'totalCashes',
			'averageROI',
		])
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