<template>
	<div class="flex flex-col text-white border border-muted-dark rounded-lg">
		<div class="flex justify-between border border-muted-dark p-3">
			<span>Sessions</span>
			<span class="text-lg">4</span>
		</div>
		<div class="flex justify-between border border-muted-dark p-3">
			<span>Profit</span>
			<span class="text-lg font-semibold text-green-500">
				<number
					ref="stats-profit"
					:from="0"
					:to="1056.84"
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
					:to="24.46"
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
					:to="254.21"
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
					:to="23.54"
					:duration="1"
					:format="(number) => number.toFixed(2)+'%'"
					easing="Power1.easeOut"
				/>
			</span>
		</div>
		<div class="flex justify-between border border-muted-dark p-3">
			<span>Average duration</span>
			<span class="text-lg">8 hours and 43 mins</span>
		</div>
		<div class="flex justify-between border border-muted-dark p-3">
			<span>Total duration</span>
			<span class="text-lg">43 hours and 12 mins</span>
		</div>
	</div>
</template>

<script>
export default {
    name: 'StatsTable'
}
</script>

<style>

</style>