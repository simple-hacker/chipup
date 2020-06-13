<template>
	<div>
		<div
			@click.self="showFilters"
			class="flex justify-between items-center p-3 rounded cursor-pointer text-white"
			:class="filtersApplied ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-500 hover:bg-gray-450'"
		>
			<div class="flex items-center">
				<h1 class="mr-4 uppercase text-xl font-medium tracking-widest">Filters</h1>
				<i class="fas fa-filter"></i>
			</div>
			<div
				v-if="filtersApplied"
                @click.prevent="removeFilters"
                class="flex items-center cursor-pointer rounded hover:bg-green-500 p-2"
            >
                <i class="fas fa-times mr-3"></i>
                <span class="text-sm uppercase tracking-wide font-medium">Remove filters</span>
            </div>
		</div>
	</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

import Filters from '@components/Filters'

export default {
	name: 'FilterBar',
	components: { Filters },
	computed: {
		...mapState('filtered_sessions', ['currentFilters']),
		...mapGetters('filters', ['unfilteredFilters']),
		filtersApplied() {
			return ! _.isEqual(this.currentFilters, this.unfilteredFilters)
		}
	},
	methods: {
		showFilters() {
			this.$modal.show(Filters, {}, {
                // Modal Options
                classes: 'modal',
                height: 'auto',
				width: '95%',
				scrollable: true,
			})
		},
		removeFilters() {
			this.$store.dispatch('filtered_sessions/resetFilters', this.unfilteredFilters)
		}
	},
}
</script>

<style>

</style>