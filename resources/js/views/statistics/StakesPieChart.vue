<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'StakesPieChart',
    data() {
        return {
            options: {
                chart: {
					id: 'stakesPieChart',
                    foreColor: '#FFFFFF',
                    toolbar: {
                        show: false
                    }
                },
                labels: [],
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
            },
        }
    },
    computed: {
        ...mapGetters('filtered_sessions', ['stakeSeries']),
        series() {
            return Object.values(this.stakeSeries.counts) ?? []
        }
    },
    created() {
        this.options.labels = Object.keys(this.stakeSeries.counts) ?? []
    }
}
</script>

<style>

</style>