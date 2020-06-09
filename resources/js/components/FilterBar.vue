<template>
	<div>
		<div @click.prevent="showFilters" class="flex justify-between items-center p-3 rounded cursor-pointer text-xl font-medium font-bold bg-muted-dark hover:bg-muted-light">
			<div class="flex items-center">
				<h1 class="mr-4">Filters</h1>
				<i class="fas fa-filter"></i>
			</div>
		</div>
	</div>
</template>

<script>
import Filters from '@components/Filters'

export default {
	name: 'FilterBar',
	components: { Filters },
	methods: {
		showFilters() {
			this.$modal.show(Filters, {}, {
                // Modal Options
                classes: 'modal',
                height: 'auto',
				width: '95%',
				scrollable: true,
			})
		}
	}
}
</script>

<style>

</style>