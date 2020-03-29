<template>
	<div class="flex flex-col">
		<session-summary v-for="session in sessions" :key="session.id" :session="session"></session-summary>
		<div class="mt-4 flex justify-end">
			<router-link
				:to="{ name: 'sessions' }"
				class="bg-green-600 border border-green-700 hover:bg-green-700 py-2 px-4 uppercase text-white text-sm text-center"
			>
			View all sessions
			</router-link>
		</div>
	</div>
</template>

<script>
import SessionSummary from '../SessionSummary';

export default {
	name: 'Sessions',
	components: { SessionSummary },
	data() {
		return {
			sessions: [
				{
					id: 1,
					date: 'Today',
					location: 'Aspers Casino MK',
					profit: -300
				},
				{
					id: 2,
					date: 'Yesterday',
					location: 'Aspers Casino MK',
					profit: 75.85
				},
				{
					id: 3,
					date: 'Monday 25th March',
					location: 'Aspers Casino MK',
					profit: -60.00
				},
				{
					id: 4,
					date: 'Saturday 23rd March',
					location: 'Groveners Luton',
					profit: 212
				},
				{
					id: 5,
					date: 'Friday 22nd March',
					location: 'Groveners Luton',
					profit: 118
				},
			]
		}
	}
}
</script>

<style>

</style>