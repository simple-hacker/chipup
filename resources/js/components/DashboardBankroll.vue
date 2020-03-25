<template>
  <div class="flex">
      <div class="w-full text-5xl sm:text-6xl xl:text-5xl text-green-600 font-extrabold text-center">
        <number
            ref="dashboard-bankroll"
            :from="0"
            :to="10000"
            :duration="0.2"
            :format="(number) => '£'+number.toFixed(2)"
            easing="Power1.easeOut"
        />
    </div>
  </div>
</template>

<script>
export default {
    name: 'DashboardBankroll',
}
</script>

<style>

</style>