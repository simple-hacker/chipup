<template>
    <div class="w-full h-full mx-auto">
        <current-session v-if="sessionInProgress"></current-session>
        <start-session v-else></start-session>
    </div>
</template>

<script>
import StartSession from '@components/Session/StartSession';
import CurrentSession from '@components/Session/CurrentSession';

import { mapState, mapGetters } from 'vuex'

export default {
    name: 'LiveSession',
    components: { StartSession, CurrentSession },
    computed: {
        ...mapState('live', ['liveSession']),
        ...mapGetters('live', ['sessionInProgress'])
    }
}
</script>

<style scoped>

</style>