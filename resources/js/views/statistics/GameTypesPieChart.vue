<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'GameTypesPieChart',
    data() {
        return {
            options: {
                chart: {
					id: 'gameTypesPieChart',
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
                        color: '#38a169',
                        shadeTo: 'light',
                        shadeIntensity: 0.75
                    }
                }
            },
        }
    },
    computed: {
        ...mapGetters('filtered_sessions', ['gameTypeSeries']),
        series() {
            return this.gameTypeSeries?.counts ?? []
        }
    },
    created() {
        this.options.labels = this.gameTypeSeries?.game_types ?? []
    }
}
</script>

<style>

</style>