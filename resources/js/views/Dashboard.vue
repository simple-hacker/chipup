<template>
    <div class="w-full flex flex-col">
        <div class="mb-4">
            <statistics/>
        </div>
        <div
            v-if="sessions.length > 0"
            class="grid grid-cols-4 gap-4"
        >
            <div class="col-span-4 xxl:col-span-3 xxl:row-span-5 card">
                <chart/>
            </div>
            <div class="col-span-4 xxl:col-span-1 xxl:row-span-5 card">
                <sessions/>
            </div>
        </div>
        <div
            v-else
            class="col-span-4 card"
        >
            <empty-sessions></empty-sessions>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'

import EmptySessions from '@components/Session/EmptySessions'
import Bankroll from '@views/dashboard/Bankroll'
import Chart from '@views/dashboard/Chart'
import Statistics from '@views/dashboard/Statistics'
import Sessions from '@views/dashboard/Sessions'

export default {
    name: 'Dashboard',
    components: { EmptySessions, Chart, Bankroll, Statistics, Sessions },
    computed: {
		...mapGetters('sessions', ['sessions']),
    }
  }
</script>

<style scoped>

</style>