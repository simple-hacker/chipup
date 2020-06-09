<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div
            v-if="sessions.length > 0"
            class="col-span-4 bg-background rounded border border-card p-1 text-white"
        >
            <filter-bar />
        </div>
        <router-link
            :to="{ name: 'createsession' }"
            class="col-span-4 mb-2 md:mb-0 flex flex-1"
        >
            <div class="flex p-4 border border-muted-dark shadow bg-card hover:bg-muted-dark cursor-pointer justify-center items-center w-full text-white">
                <i class="fas fa-plus-circle fa-lgx mr-3"></i>
                <div class="text-white text-lg uppercase">Add New Session</div>
            </div>
        </router-link>
        <div
            v-if="filteredSessions.length > 0"
            class="col-span-4 grid grid-cols-4 grid-2 md:gap-3 xxl:grid-4 bg-background rounded border border-card p-1 text-white"
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
            class="col-span-4 flex bg-muted-dark border border-muted-dark shadow justify-center items-center my-2 p-4"
        >
            <div class="text-white uppercase">You do not have any sessions yet.</div>
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
		...mapGetters('filtered_sessions', ['filteredSessions'])
    },
}
</script>

<style scoped>

</style>