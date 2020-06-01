<template>
    <div class="w-full grid grid-cols-4 gap-4 pb-4">
        <div class="col-span-4 xl:col-span-3 p-2 bg-card border border-muted-dark rounded shadow">
			<chart/>
        </div>
        <div class="col-span-4 md:col-span-2 xl:col-span-1 xl:order-first p-2 bg-card border border-muted-dark rounded shadow">
            <bankroll/>
        </div>
        <div class="col-span-4 md:col-span-2 p-2 bg-card border border-muted-dark rounded shadow">
            <statistics/>
        </div>
        <div class="col-span-4 xl:col-span-2 p-2 bg-card border border-muted-dark rounded shadow">
			<sessions/>
        </div>
    </div>
</template>

<script>
import Bankroll from '@views/dashboard/Bankroll'
import Chart from '@views/dashboard/Chart'
import Statistics from '@views/dashboard/Statistics'
import Sessions from '@views/dashboard/Sessions'

export default {
    name: 'Dashboard',
    components: { Chart, Bankroll, Statistics, Sessions },
  }
</script>

<style scoped>

</style>