<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div class="col-span-4 bg-background rounded border border-card p-1 text-white">
            <filters />
        </div>
        <div class="col-span-4 grid grid-cols-4 grid-2 md:gap-3 xxl:grid-4 bg-background rounded border border-card p-1 text-white">
			<router-link
                    :to="{ name: 'createsession' }"
                    class="col-span-4 mb-2 md:mb-0 flex flex-1"
			>
                <div class="flex p-4 border border-muted-dark shadow bg-card hover:bg-muted-dark cursor-pointer justify-center items-center w-full">
					<i class="fas fa-plus-circle fa-lgx mr-3"></i>
					<div class="text-white text-lg uppercase">Add New Session</div>
				</div>
			</router-link>
            <div v-for="session in cash_games.cash_games"
				:key="session.id"
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

export default {
    name: 'Sessions',
	components: { Filters, SessionSummary},
	computed: {
		...mapState(['cash_games'])
	},
}
</script>

<style scoped>

</style>