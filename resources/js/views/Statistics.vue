<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div
            v-if="sessions.length > 0"
            class="col-span-4 bg-background rounded border border-card p-1 text-white"
        >
            <filter-bar />
        </div>
        <div class="col-span-4 xl:col-span-2 xxl:col-span-1 bg-card rounded border border-muted-dark mb-3 p-4 text-white">
            <stats-table />
        </div>
        <div class="col-span-4 xl:col-span-2 xxl:col-span-3 bg-card rounded border border-muted-dark mb-3 p-4 text-white">
            <profit-line-chart />
        </div>
        <div class="col-span-4 xl:col-span-2 bg-card rounded border border-muted-dark mb-3 p-4 text-white">
            <h1 class="text-3xl font-bold">By Game Type</h1>
            <div class="mt-3 flex flex-col md:flex-row w-full">
                <div class="flex-1">
                    <game-types-profit-bar-chart />
                </div>
                <div class="flex-1">
                    <game-types-pie-chart />
                </div>
            </div>
        </div>
        <div class="col-span-4 xl:col-span-2 bg-card rounded border border-muted-dark mb-3 p-4 text-white">
            <h1 class="text-3xl font-bold">By Locations</h1>
            <div class="mt-3 flex flex-col md:flex-row w-full">
                <div class="flex-1">
                    <locations-profit-bar-chart />
                </div>
                <div class="flex-1">
                    <locations-pie-chart />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

import FilterBar from '@components/FilterBar'
import StatsTable from '@views/statistics/StatsTable'
import ProfitLineChart from '@views/statistics/ProfitLineChart'
import LocationsPieChart from '@views/statistics/LocationsPieChart'
import LocationsProfitBarChart from '@views/statistics/LocationsProfitBarChart'
import GameTypesPieChart from '@views/statistics/GameTypesPieChart'
import GameTypesProfitBarChart from '@views/statistics/GameTypesProfitBarChart'

export default {
    name: 'Statistics',
    components: {FilterBar, StatsTable, ProfitLineChart, LocationsPieChart, LocationsProfitBarChart, GameTypesPieChart, GameTypesProfitBarChart},
    computed: {
		...mapGetters('sessions', ['sessions'])
    },
}
</script>

<style scoped>
    .slide-enter-active,
    .slide-leave-active {
        transition-duration: 0.30s;
        transition-property: all;
        transition-timing-function: ease;
    }

    .slide-enter,
    .slide-leave-active {
        opacity: 0;
        transform: translateY(-100px);
        margin-bottom: -100px;
    }
</style>