<template>
	<div class="flex flex-col">
		<div v-for="session in sessions.slice(0, 5)"
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
</template>

<script>
import { mapGetters } from 'vuex'
import SessionSummary from '@components/Session/SessionSummary'

export default {
	name: 'Sessions',
	components: { SessionSummary },
	computed: {
        ...mapGetters('sessions', ['sessions']),
	},
}
</script>

<style>

</style>