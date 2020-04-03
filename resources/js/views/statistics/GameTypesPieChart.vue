<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
export default {
    name: 'GameTypesPieChart',
    data() {
        return {
            series: [65, 15],
            options: {
                labels: ["1/2 NL Holdem", "1/1 Pot Limit Omaha"],
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
    }
}
</script>

<style>

</style>