<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div class="col-span-4 xl:col-span-3 p-2 border shadow">
            <line-chart :chart-data="datacollection" :options="chartoptions"></line-chart>
        </div>
        <div class="col-span-4 md:col-span-2 xl:col-span-1 xl:order-first p-2 border shadow">
            <dashboard-bankroll></dashboard-bankroll>
        </div>
        <div class="col-span-4 md:col-span-2 p-2 border shadow">
            <h1>Stats</h1>
        </div>
        <div class="col-span-4 xl:col-span-2 p-2 border shadow">
			<h1>Sessions</h1>
        </div>
    </div>
</template>

<script>
import LineChart from '../charts/LineChart.js'
import DashboardBankroll from '../components/DashboardBankroll'

export default {
    name: 'Dashboard',
    components: {
	  LineChart,
	  DashboardBankroll
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