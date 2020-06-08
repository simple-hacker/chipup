<template>
    <div>
        <apexchart type="bar" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
export default {
    name: 'GameTypesProfitBarChart',
    data() {
        return {
            series: [{name:'Profit', data: [250,-218]}],
            options: {
                chart: {
					id: 'locationBarChart',
					foreColor: '#FFFFFF',
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
                labels: ["1/2 NL Holdem", "1/1 Pot Limit Omaha"],
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
            },
        }
    }
}
</script>

<style>

</style>