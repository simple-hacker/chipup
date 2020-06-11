<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'LocationsPieChart',
    computed: {
        ...mapGetters('filtered_sessions', ['locationSeries']),
        options() {
        return {
                chart: {
                    id: 'locationsPieChart',
                    foreColor: '#FFFFFF',
                    toolbar: {
                        show: false
                    }
                },
                labels: Object.keys(this.locationSeries?.counts) ?? [],
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
                        color: '#BB488B',
                        shadeTo: 'light',
                        shadeIntensity: 0.75
                    }
                }
            }
        },
        series() {
            return Object.values(this.locationSeries?.counts) ?? []
        }
    },
}
</script>

<style>

</style>