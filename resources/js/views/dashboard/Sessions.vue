<template>
	<div class="flex flex-col">
		<div
			v-if="sessions.length > 0"
		>
			<div v-for="session in sessions.slice(0,8)"
				:key="`${session.game_type}_${session.id}`"
				class="mb-2">
				<session-summary :session="session"></session-summary>
			</div>
			<div class="mt-4 flex justify-end">
				<router-link
					:to="{ name: 'sessions' }"
					class="btn btn-green"
				>
				View all sessions
				</router-link>
			</div>
		</div>
		<empty-sessions v-else></empty-sessions>
	</div>
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