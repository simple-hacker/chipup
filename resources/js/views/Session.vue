<template>
    <div class="w-full md:w-5/6 mx-auto bg-card rounded border border-muted-dark p-4 text-white">
        <current-cash-game v-if="showCurrentCashGame"></current-cash-game>
        <start-cash-game v-else></start-cash-game>
    </div>
</template>

<script>
import StartCashGame from '../components/Session/StartCashGame';
import CurrentCashGame from '../components/Session/CurrentCashGame';

export default {
    name: 'Session',
    components: {
        'start-cash-game': StartCashGame,
        'current-cash-game': CurrentCashGame,
    },
    data() {
        return {
            currentCashGame: {},
        }
    },
    computed: {
        showCurrentCashGame: function() {
            return (Object.keys(this.currentCashGame).length > 0)
        }
    }
}
</script>

<style scoped>

</style>