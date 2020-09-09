<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
    name: 'StakesPieChart',
    computed: {
        ...mapState(['user']),
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
                labels: this.labels,
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