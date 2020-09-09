<template>
    <div>
        <apexchart type="bar" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
    name: 'StakesProfitBarChart',
    computed: {
        ...mapState(['user']),
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
                        formatter: (val, opts) => {
                            return this.$n(val, 'currencyNoDecimal')
                        },
                    },
                    title: {
                        text: 'Profit'
                    }
                },
                labels: this.labels,
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
            }
        },
        series() {
            return [
                {
                    name:'Profit',
                    data: Object.values(this.stakeSeries.profits) ?? []
                }
            ]
        },
        labels() {
            let formatter = new Intl.NumberFormat(this.user.locale, { style: 'currency', currency: this.user.currency, minimumFractionDigits: 0, maximumFractionDigits: 2 });

            return Object.keys(this.stakeSeries.profits).map(stake => {
                // For each stake 1/1, split against /
                // For each blinds and join together with /
                return stake.split('/').map(blind => {
                    return formatter.format(blind)
                }).join('/')
            }) ?? []
        }
    },
}
</script>

<style>

</style>