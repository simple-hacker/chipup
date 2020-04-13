<template>
	<div class="flex flex-col">
		<div v-for="session in sessions" :key="session.id" class="mb-2">
			<session-summary :session="session"></session-summary>
		</div>
		<div class="mt-4 flex justify-end">
			<router-link
				:to="{ name: 'sessions' }"
				class="btn-green"
			>
			View all sessions
			</router-link>
		</div>
	</div>
</template>

<script>
import SessionSummary from '@components/Session/SessionSummary';

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
					profit: -300000
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