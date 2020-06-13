<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div
            v-if="sessions.length > 0"
            class="col-span-4"
        >
            <filter-bar />
        </div>
        <div class="col-span-4">
            <stats-table />
        </div>
        <div class="col-span-4 card">
            <profit-line-chart />
        </div>
        <div class="col-span-4 xl:col-span-2 card flex flex-col p-4">
            <h1 class="p-1 uppercase tracking-wider text-2xl font-bold text-gray-100 border-b border-gray-400">By Game Type</h1>
            <div class="mt-3 flex flex-col md:flex-row w-full">
                <div class="flex-1">
                    <game-types-profit-bar-chart />
                </div>
                <div class="flex-1">
                    <game-types-pie-chart />
                </div>
            </div>
        </div>
        <div
            v-if="filteredCashGames.length > 0"
            class="col-span-4 xl:col-span-2 card flex flex-col p-4">
            <h1 class="p-1 uppercase tracking-wider text-2xl font-bold text-gray-100 border-b border-gray-400">By Stake</h1>
            <div class="mt-3 flex flex-col md:flex-row w-full">
                <div class="flex-1">
                    <stakes-profit-bar-chart />
                </div>
                <div class="flex-1">
                    <stakes-pie-chart />
                </div>
            </div>
        </div>
        <div class="col-span-4 xl:col-span-2 card flex flex-col p-4">
            <h1 class="p-1 uppercase tracking-wider text-2xl font-bold text-gray-100 border-b border-gray-400">By Variant</h1>
            <div class="mt-3 flex flex-col md:flex-row w-full">
                <div class="flex-1">
                    <variants-profit-bar-chart />
                </div>
                <div class="flex-1">
                    <variants-pie-chart />
                </div>
            </div>
        </div>
        <div class="col-span-4 xl:col-span-2 card flex flex-col p-4">
            <h1 class="p-1 uppercase tracking-wider text-2xl font-bold text-gray-100 border-b border-gray-400">By Location</h1>
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
import GameTypesPieChart from '@views/statistics/GameTypesPieChart'
import GameTypesProfitBarChart from '@views/statistics/GameTypesProfitBarChart'
import StakesPieChart from '@views/statistics/StakesPieChart'
import StakesProfitBarChart from '@views/statistics/StakesProfitBarChart'
import VariantsPieChart from '@views/statistics/VariantsPieChart'
import VariantsProfitBarChart from '@views/statistics/VariantsProfitBarChart'
import LocationsPieChart from '@views/statistics/LocationsPieChart'
import LocationsProfitBarChart from '@views/statistics/LocationsProfitBarChart'

export default {
    name: 'Statistics',
    components: {
        FilterBar,
        StatsTable,
        ProfitLineChart,
        GameTypesPieChart,
        GameTypesProfitBarChart,
        StakesPieChart,
        StakesProfitBarChart,
        VariantsPieChart,
        VariantsProfitBarChart,
        LocationsPieChart,
        LocationsProfitBarChart,
    },
    computed: {
        ...mapGetters('sessions', ['sessions']),
        ...mapGetters('filtered_sessions', ['filteredCashGames']),
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