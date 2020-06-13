<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div
            v-if="sessions.length > 0"
            class="col-span-4"
        >
            <filter-bar />
        </div>
        <router-link
            :to="{ name: 'createsession' }"
            class="col-span-4 mb-2 md:mb-0 flex flex-1"
        >
            <div class="flex p-4 justify-center items-center w-full bg-gray-500 hover:bg-gray-450 rounded border-b-8 border-green-500 hover:border-green-400 shadow hover:shadow-2xl cursor-pointer text-white">
                <i class="fas fa-plus-circle fa-lgx mr-3"></i>
                <div class="text-white text-lg uppercase">Add New Session</div>
            </div>
        </router-link>
        <div
            v-if="filteredSessions.length > 0"
            class="col-span-4 grid grid-cols-4 grid-2 md:gap-3 xxl:grid-4 card"
        >
            <div v-for="session in filteredSessions"
                :key="`${session.game_type}_${session.id}`"
                class="col-span-4 md:col-span-2 xxl:col-span-1 mb-2 md:mb-0"
            >
                <session-summary :session="session"></session-summary>
            </div>
        </div>
        <div
            v-else
            class="col-span-4 flex card"
        >
            <div class="text-white uppercase" v-text="emptySessionsMessage"></div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'
import FilterBar from '@components/FilterBar'
import SessionSummary from '@components/Session/SessionSummary'

export default {
    name: 'Sessions',
	components: { FilterBar, SessionSummary},
	computed: {
		...mapGetters('sessions', ['sessions']),
        ...mapGetters('filtered_sessions', ['filteredSessions']),
        emptySessionsMessage() {
            // This is displayed if user does not have any sessions that match the filter
            return (this.sessions.length > 0) ? 'You do not have any sessions that match those filters.' : 'You have not created any sessions yet.'
        }
    },
}
</script>

<style scoped>

</style>