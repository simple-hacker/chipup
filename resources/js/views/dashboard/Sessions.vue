<template>
	<div class="flex flex-col">
		<div
			v-if="sessions.length > 0"
		>
			<div v-for="session in sessions.slice(0, 8)"
				:key="`${session.game_type}_${session.id}`"
				class="mb-2">
				<session-summary :session="session"></session-summary>
			</div>
		</div>
		<div
			v-else
			class="flex p-4 border border-muted-dark shadow bg-muted-dark justify-center items-center w-full"
		>
			<div class="text-white uppercase">You do not have any sessions yet.</div>
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