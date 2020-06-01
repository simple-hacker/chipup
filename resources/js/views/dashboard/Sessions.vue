<template>
	<div class="flex flex-col">
		<div v-for="session in cash_games.cash_games.slice(0, 5)"
			:key="session.id"
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
import { mapState } from 'vuex'
import SessionSummary from '@components/Session/SessionSummary'

export default {
	name: 'Sessions',
	components: { SessionSummary },
	computed: {
        ...mapState(['cash_games']),
	}
}
</script>

<style>

</style>