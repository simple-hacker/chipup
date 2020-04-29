<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div class="col-span-4 bg-background rounded border border-card p-1 text-white">
            <filters />
        </div>
        <div class="col-span-4 grid grid-cols-4 grid-2 md:gap-3 xxl:grid-4 bg-background rounded border border-card p-1 text-white">
            <div v-for="session in cash_games.cash_games"
				:key="session.id"
				@click="showSessionDetails(session)"
				class="col-span-4 md:col-span-2 xxl:col-span-1 mb-2 md:mb-0"
			>
                <session-summary :session="session"></session-summary>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import Filters from '@components/Filters'
import SessionSummary from '@components/Session/SessionSummary'
import SessionDetails from '@components/Session/SessionDetails'

export default {
    name: 'Sessions',
	components: { Filters, SessionSummary, SessionDetails},
	computed: {
		...mapState(['cash_games'])
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

<style scoped>

</style>