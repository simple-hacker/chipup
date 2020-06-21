<template>
    <div>
        <apexchart type="bar" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'GameTypesProfitBarChart',
    data() {
        return {
            options: {
                chart: {
					id: 'gameTypesProfitBarChart',
                    foreColor: '#FFFFFF',
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#38a169'],
                yaxis: {
                    labels: {
                        formatter: (val, opts) => {
                            return this.$n(val, 'currencyNoFraction')
                        },
                    },
                    title: {
                        text: 'Profit'
                    }
                },
                labels: [],
                grid: {
					borderColor: '#38393D'
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val, opts) => {
                            return this.$n(val, 'currency')
                        },
                },
                tooltip: {
					theme: false,
				},
                plotOptions: {
                    bar: {
                        colors: {
                            ranges: [{
                                from: -9999999,
                                to: 0,
                                color: '#e53e3e'
                            }, {
                                from: 0,
                                to: 9999999,
                                color: '#38a169'
                            }]
                        },
                    }
                },
            },
        }
    },
    computed: {
        ...mapGetters('filtered_sessions', ['gameTypeSeries']),
        series() {
            return [
                {
                    name:'Profit',
                    data: this.gameTypeSeries?.profits ?? []
                }
            ]
        }
    },
    created() {
        this.options.labels = this.gameTypeSeries?.game_types ?? []
    }
}
</script>

<style>

</style>