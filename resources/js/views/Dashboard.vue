<template>
    <div class="w-full grid grid-cols-4 gap-4">
        <div class="col-span-4 xl:col-span-3 p-2 border shadow">
			<chart/>
        </div>
        <div class="col-span-4 md:col-span-2 xl:col-span-1 xl:order-first p-2 border shadow">
            <bankroll/>
        </div>
        <div class="col-span-4 md:col-span-2 p-2 border shadow">
            <statistics/>
        </div>
        <div class="col-span-4 xl:col-span-2 p-2 border shadow">
			<sessions/>
        </div>
    </div>
</template>

<script>
import Bankroll from '../components/Dashboard/Bankroll'
import Chart from '../components/Dashboard/Chart'
import Statistics from '../components/Dashboard/Statistics'
import Sessions from '../components/Dashboard/Sessions'

export default {
    name: 'Dashboard',
    components: { Chart, Bankroll, Statistics, Sessions },
  }
</script>

<style scoped>

</style>