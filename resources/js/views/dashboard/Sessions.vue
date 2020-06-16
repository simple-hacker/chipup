<template>
	<div
		v-if="sessions.length > 0"
		class="flex flex-col"
	>
		<div v-for="session in sessions.slice(0,8)"
			:key="`${session.game_type}_${session.id}`"
			class="mb-2">
			<session-summary :session="session"></session-summary>
		</div>
		<router-link
			:to="{ name: 'sessions' }"
			class="p-3 md:p-4 flex justify-center items-center w-full bg-gray-500 hover:bg-gray-450 rounded border-b-8 border-green-500 hover:border-green-400 shadow hover:shadow-2xl cursor-pointer text-white"
		>
			<div class="text-white text-lg uppercase">View All Sessions</div>
		</router-link>
	</div>
	<empty-sessions v-else></empty-sessions>
</template>

<script>
import { mapGetters } from 'vuex'
import SessionSummary from '@components/Session/SessionSummary'
import EmptySessions from '@components/Session/EmptySessions'

export default {
	name: 'Sessions',
	components: { SessionSummary, EmptySessions },
	computed: {
        ...mapGetters('sessions', ['sessions']),
	},
}
</script>

<style>

</style>