<template>
    <div>
        <apexchart type="pie" width="100%" height="300" :options="options" :series="series"></apexchart>
    </div>
</template>

<script>
export default {
    name: 'LocationsPieChart',
    data() {
        return {
            series: [25, 15, 44, 55],
            options: {
                labels: ["CasinoMK", "Luton Groveners", "The Wynn", "Casino Royale"],
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