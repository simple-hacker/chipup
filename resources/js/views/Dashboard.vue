<template>
    <div class="w-full grid grid-cols-4 xxl:grid-rows-6 gap-4 pb-4">
        <div class="col-span-4 row-span-1">
            <statistics/>
        </div>
        <div class="col-span-4 xxl:col-span-3 xxl:row-span-5 card">
			<chart/>
        </div>
        <div class="col-span-4 xxl:col-span-1 xxl:row-span-5 card">
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