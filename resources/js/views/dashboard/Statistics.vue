<template>
	<div class="flex flex-col text-white">
		<div class="border border-muted-dark rounded-lg">
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Sessions</span>
				<span class="text-lg" v-text="numberOfSessions"></span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Profit</span>
				<span class="text-lg font-semibold text-green-500">
					<number
						ref="stats-profit"
						:from="0"
						:to="totalProfit"
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
						ref="stats-profit-hour"
						:from="0"
						:to="profitPerHour"
						:duration="1"
						:format="(number) => '£'+number.toLocaleString()"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Total Buy Ins</span>
				<span class="text-lg font-semibold">
					<number
						ref="stats-profit-hour"
						:from="0"
						:to="totalBuyIns"
						:duration="1"
						:format="(number) => '£'+number.toLocaleString()"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Total Cashes</span>
				<span class="text-lg font-semibold">
					<number
						ref="stats-profit-hour"
						:from="0"
						:to="totalCashes"
						:duration="1"
						:format="(number) => '£'+number.toLocaleString()"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Profit / session</span>
				<span class="text-lg font-semibold text-green-500">
					<number
						ref="stats-profit-session"
						:from="0"
						:to="profitPerSession"
						:duration="1"
						:format="(number) => '£'+number.toLocaleString()"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Average ROI</span>
				<span class="text-lg font-semibold text-green-500">
					<number
						ref="stats-average-roi"
						:from="0"
						:to="averageROI"
						:duration="1"
						:format="(number) => number.toFixed(2)+'%'"
						easing="Power1.easeOut"
					/>
				</span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Average duration</span>
				<span class="text-lg" v-text="averageDuration"></span>
			</div>
			<div class="flex justify-between border border-muted-dark p-3">
				<span>Total duration</span>
				<span class="text-lg" v-text="totalDuration"></span>
			</div>
		</div>
		<div class="mt-4 flex justify-end">
			<router-link
				:to="{ name: 'statistics' }"
				class="btn btn-green"
			>
			View more statistics
			</router-link>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
	name: 'Statistics',
	computed: {
		...mapGetters('sessions', [
			'numberOfSessions',
			'totalProfit',
			'totalDurationHours',
			'averageDuration',
			'totalDuration',
			'profitPerHour',
			'profitPerSession',
			'totalBuyIns',
			'totalCashes',
			'averageROI',
		])
	}
}
</script>

<style>

</style>