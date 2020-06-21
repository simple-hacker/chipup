<template>
    <div>
        <apexchart type="bar" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'VariantsProfitBarChart',
    computed: {
        ...mapGetters('filtered_sessions', ['variantSeries']),
        options() {
            return {
                chart: {
                    id: 'variantsProfitBarChart',
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
                labels: Object.keys(this.variantSeries?.profits) ?? [],
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
                }
            }
        },
        series() {
            return [
                {
                    name:'Profit',
                    data: Object.values(this.variantSeries?.profits) ?? []
                }
            ]
        }
    },
}
</script>

<style>

</style>