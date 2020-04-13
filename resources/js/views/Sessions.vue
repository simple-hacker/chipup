<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div class="col-span-4 bg-background rounded border border-card p-1 text-white">
            <filters />
        </div>
        <div class="col-span-4 grid grid-cols-4 grid-2 md:gap-3 xxl:grid-4 bg-background rounded border border-card p-1 text-white">
            <div v-for="session in sessions"
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
import Filters from '@components/Filters'
import SessionSummary from '@components/Session/SessionSummary'
import SessionDetails from '@components/Session/SessionDetails'

export default {
    name: 'Sessions',
    components: { Filters, SessionSummary, SessionDetails},
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
				{
					id: 6,
					date: 'Today',
					location: 'Aspers Casino MK',
					profit: -300000
				},
				{
					id: 7,
					date: 'Yesterday',
					location: 'Aspers Casino MK',
					profit: 75.85
				},
				{
					id: 8,
					date: 'Monday 25th March',
					location: 'Aspers Casino MK',
					profit: -60.00
				},
				{
					id: 9,
					date: 'Saturday 23rd March',
					location: 'Groveners Luton',
					profit: 212
				},
				{
					id: 10,
					date: 'Friday 22nd March',
					location: 'Groveners Luton',
					profit: 118
				},
				{
					id: 11,
					date: 'Today',
					location: 'Aspers Casino MK',
					profit: -300000
				},
				{
					id: 12,
					date: 'Yesterday',
					location: 'Aspers Casino MK',
					profit: 75.85
				},
				{
					id: 13,
					date: 'Monday 25th March',
					location: 'Aspers Casino MK',
					profit: -60.00
				},
				{
					id: 14,
					date: 'Saturday 23rd March',
					location: 'Groveners Luton',
					profit: 212
				},
				{
					id: 15,
					date: 'Friday 22nd March',
					location: 'Groveners Luton',
					profit: 118
				},
				{
					id: 16,
					date: 'Today',
					location: 'Aspers Casino MK',
					profit: -300000
				},
				{
					id: 17,
					date: 'Yesterday',
					location: 'Aspers Casino MK',
					profit: 75.85
				},
				{
					id: 18,
					date: 'Monday 25th March',
					location: 'Aspers Casino MK',
					profit: -60.00
				},
				{
					id: 19,
					date: 'Saturday 23rd March',
					location: 'Groveners Luton',
					profit: 212
				},
				{
					id: 20,
					date: 'Friday 22nd March',
					location: 'Groveners Luton',
					profit: 118
				},
			]
		}
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