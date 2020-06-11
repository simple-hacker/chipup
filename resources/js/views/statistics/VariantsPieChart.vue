<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'VariantsPieChart',
    data() {
        return {
            options: {
                chart: {
					id: 'variantsPieChart',
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
                        color: '#BBB248',
                        shadeTo: 'light',
                        shadeIntensity: 0.75
                    }
                }
            },
        }
    },
    computed: {
        ...mapGetters('filtered_sessions', ['variantSeries']),
        series() {
            return Object.values(this.variantSeries?.counts) ?? []
        }
    },
    created() {
        this.options.labels = Object.keys(this.variantSeries?.counts) ?? []
    }
}
</script>

<style>

</style>