<template>
    <div class="flex flex-col flex-1 items-center py-2">
        <h1>Session</h1>
        <start-cash-game></start-cash-game>
    </div>
</template>

<script>
import StartCashGame from '../components/StartCashGame';

export default {
    name: 'Session',
    components: {
        'start-cash-game': StartCashGame
    }
}
</script>

<style scoped>

</style>