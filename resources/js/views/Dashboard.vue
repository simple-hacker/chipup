<template>
    <div>
        <h1>Dashboard</h1>
        <div class="w-full md:w-5/6 justify-center mx-auto">
            <line-chart :chart-data="datacollection" :options="chartoptions"></line-chart>
        </div>
    </div>
</template>

<script>
import LineChart from '../charts/LineChart.js'

export default {
    name: 'Dashboard',
    components: {
      LineChart
    },
    data () {
      return {
        datacollection: null,
        chartoptions: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
            }
        }
      }
    },
    mounted () {
      this.fillData();
      this.$snotify.success('Dashboard loaded');
    },
    methods: {
      fillData () {
        this.datacollection = {
          labels: [this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt()],
          datasets: [
            {
              label: 'Data One',
              backgroundColor: '#38a169',
              data: [this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt()]
            }, {
              label: 'Data Two',
              backgroundColor: '#2f855a',
              data: [this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt(), this.getRandomInt()]
            }
          ]
        }
      },
      getRandomInt () {
        return Math.floor(Math.random() * (500 - 5 + 1)) + 5
      }
    }
  }
</script>

<style scoped>

</style>