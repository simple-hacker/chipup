<template>
    <div class="flex-col">
        <div class="flex justify-between items-center p-3 rounded cursor-pointer text-lg :mdtext-xl font-medium font-bold bg-green-500">
			<div class="flex items-center">
				<h1 class="mr-4">Filters</h1>
				<i class="fas fa-filter"></i>
			</div>
		</div>
        <div class="grid cols-4 gap-2">
            <div class="col-span-4 lg:col-span-3 xxl:col-span-2 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Dates</h2>
                <div class="flex md:justify-around">
                    <div class="w-1/2 md:w-auto flex items-center">
                        <span class="hidden md:block"><i class="fas fa-calendar-alt fa-lg"></i></span>
                        <span class="mx-3 text-sm md:text-lg">From</span>
                        <datetime
                            input-id="filterFromDate"
                            v-model="filterFromDate"
                            type="date"
                            input-class="w-full p-2"
                            auto
                            title="Filter from"
                            class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
                        ></datetime>
                    </div>
                    <div class="w-1/2 md:w-auto flex items-center">
                        <span class="hidden md:block"><i class="fas fa-calendar-alt fa-lg"></i></span>
                        <span class="mx-3 text-sm md:text-lg">To</span>
                        <datetime
                            input-id="filterToDate"
                            v-model="filterToDate"
                            type="date"
                            input-class="w-full p-2"
                            auto
                            title="Filter to"
                            class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
                        ></datetime>
                    </div>
                </div>
            </div>
            <div class="col-span-4 lg:col-span-1 xxl:col-span-2 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-3">Game Type</h2>
                <div class="flex flex-wrap justify-around">
                    <div class="mb-1">
                        <label class="mr-3 text-sm md:text-lg">Cash Games</label>
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                    <div class="mb-1">
                        <label class="mr-3 text-sm md:text-lg">Tournaments</label>
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                </div>
            </div>
            <div class="col-span-4 md:col-span-1 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Stakes</h2>
                <div class="flex flex-wrap">
                    <div v-for="(stake, index) in stakeFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between py-1 px-3">
                        <label class="mr-3 text-sm md:text-lg">{{ stake }}</label>
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                </div>
            </div>
            <div class="col-span-4 md:col-span-1 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Limits</h2>
                <div class="flex flex-wrap">
                    <div v-for="(limit, index) in limitFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between py-1 px-3">
                        <label class="mr-3 text-sm md:text-lg">{{ limit }}</label>
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                </div>
            </div>
            <div class="col-span-4 md:col-span-1 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Variant</h2>
                <div class="flex flex-wrap">
                    <div v-for="(variant, index) in variantFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between py-1 px-3">
                        <label class="mr-3 text-sm md:text-lg">{{ variant }}</label>
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                </div>
            </div>
            <div class="col-span-4 md:col-span-1 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Table Size</h2>
                <div class="flex flex-wrap">
                    <div v-for="(table_size, index) in tableSizeFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between py-1 px-3">
                        <label class="mr-3 text-sm md:text-lg">{{ table_size }}</label>
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                </div>
            </div>
            <div class="col-span-4 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-3">Locations</h2>
                <div class="flex flex-wrap justify-around">
                    <div v-for="location in locationFilters" :key="location" class="mb-1">
                        <label class="mr-3 text-sm md:text-lg">{{ location }}</label>
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-4 border border-muted-dark p-1 md:p-3 flex justify-end">
            <button @click.prevent="applyFilters" type="button" class="btn btn-green">Apply Filters</button>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'Filters',
    data() {
        return {
            filterFromDate: '',
            filterToDate: '',
        }
    },
    computed: {
        ...mapGetters('filters', ['filteredSessions', 'stakeFilters', 'limitFilters', 'variantFilters', 'tableSizeFilters', 'locationFilters']),
    },
    methods: {
        applyFilters() {
            this.$emit('close')
        }
    },
    created() {
        console.log(this.filteredSessions)
        console.log(this.locationFilters)
    }
}
</script>

<style>

</style>