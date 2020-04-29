<template>
	<div class="flex flex-col">
		<div v-for="session in cash_games.cash_games"
			:key="session.id"
			@click="showSessionDetails(session)"
			class="mb-2">
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
import { mapState } from 'vuex'
import SessionSummary from '@components/Session/SessionSummary'
import SessionDetails from '@components/Session/SessionDetails'

export default {
	name: 'Sessions',
	components: { SessionSummary, SessionDetails },
	computed: {
        ...mapState(['cash_games']),
	},
	methods: {
		showSessionDetails: function (session) {
			this.$modal.show(SessionDetails, {
				// Modal props
				session: session,
			}, {
				// Modal Options
				classes: 'bg-background text-white p-1 md:p-3 rounded-lg border border-muted-dark',
				minHeight: 150,
				height: 'auto',
				width: '95%',
				maxWidth: 900,
				maxHeight: 500,
				adaptive: true,
				scrollable: true,
			})
		}
	}
}
</script>

<style>

</style>