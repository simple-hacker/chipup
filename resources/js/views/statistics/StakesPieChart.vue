<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'StakesPieChart',
    computed: {
        ...mapGetters('filtered_sessions', ['stakeSeries']),
        options() {
            return {
                chart: {
                    id: 'stakesPieChart',
                    foreColor: '#FFFFFF',
                    toolbar: {
                        show: false
                    }
                },
                labels: Object.keys(this.stakeSeries.counts) ?? [],
                legend: {
                    show: true,
                    position: 'bottom',
                    labels: {
                        colors: ['#FFFFFF'],
                    }
                },
                theme: {
                    monochrome: {
                        enabled: true,
                        color: '#4851BB',
                        shadeTo: 'light',
                        shadeIntensity: 0.75
                    }
                }
            }
        },
        series() {
            return Object.values(this.stakeSeries.counts) ?? []
        }
    },
}
</script>

<style>

</style>