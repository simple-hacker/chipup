<template>
    <div class="w-full flex flex-col justify-center items-center text-white p-8">
        <img src="/images/funnel.svg" width="150px" alt="Remove Filters" class="mb-5"/>
        <span class="text-xl md:text-2xl text-gray-100 text-center mb-5">You have do not have any sessions that match those filters.</span>
        <div
            @click.prevent="removeFilters"
            class="flex p-4 justify-center items-center w-5/6 md:w-3/4 bg-gray-500 hover:bg-gray-450 rounded border-b-8 border-green-500 hover:border-green-400 shadow hover:shadow-2xl cursor-pointer text-white"
        >
            <i class="fas fa-times mr-3"></i>
            <span class="text-sm uppercase tracking-wide font-medium">Remove filters</span>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'EmptyFilteredSessions',
    computed: {
		...mapGetters('filters', ['unfilteredFilters']),
	},
    methods: {
		removeFilters() {
			this.$store.dispatch('filtered_sessions/resetFilters')
		}
	},
}
</script>

<style>

</style>