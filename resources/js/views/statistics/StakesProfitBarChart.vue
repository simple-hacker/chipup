<template>
    <div>
        <apexchart type="bar" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
    name: 'StakesProfitBarChart',
    data() {
        return {
            
        }
    },
    computed: {
        ...mapGetters('filtered_sessions', ['stakeSeries']),
        options() {
            return {
                chart: {
					id: 'stakesProfitBarChart',
                    foreColor: '#FFFFFF',
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#38a169'],
                yaxis: {
                    labels: {
                        formatter: function (val, opts) {
                            return Vue.prototype.$currency.format(val);
                        },
                    },
                    title: {
                        text: 'Profit'
                    }
                },
                labels: Object.keys(this.stakeSeries.profits) ?? [],
                grid: {
					borderColor: '#38393D'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return Vue.prototype.$currency.format(val);
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
            }
        },
        series() {
            return [
                {
                    name:'Profit',
                    data: Object.values(this.stakeSeries.profits) ?? []
                }
            ]
        }
    },
}
</script>

<style>

</style>