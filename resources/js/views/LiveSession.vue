<template>
    <div class="w-full mx-auto">
        <current-cash-game v-if="sessionInProgress"></current-cash-game>
        <start-cash-game v-else></start-cash-game>
    </div>
</template>

<script>
import StartCashGame from '@components/Session/StartCashGame';
import CurrentCashGame from '@components/Session/CurrentCashGame';

import { mapState, mapGetters } from 'vuex'

export default {
    name: 'LiveSession',
    components: {
        'start-cash-game': StartCashGame,
        'current-cash-game': CurrentCashGame,
    },
    computed: {
        ...mapState('live', ['liveSession']),
        ...mapGetters('live', ['sessionInProgress'])
    }
}
</script>

<style scoped>

</style>