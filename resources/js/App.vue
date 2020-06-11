<template>
    <div class="flex flex-col h-screen">
        <!-- Top Nav -->
        <nav aria-label="top-navigation" class="sticky top-0 z-50 flex justify-between items-center px-3 py-2 bg-card border-b-2 border-background">
            <h1 class="text-2xl font-bold text-white">
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="flex items-center p-1"
                >
                    <img src="/images/logo.svg" alt="Poker Logo" width="60px" class="mr-3" />
                    <span>Poker</span>
                </router-link>
            </h1>
            <router-link
				:to="{ name: 'live' }"
				class="btn btn-green"
                :class="!sessionInProgress ? 'btn btn-green' : 'btn-red'"
			>
                <div v-if="!sessionInProgress">Start Session</div>
                <div v-if="sessionInProgress"><i class="fas fa-circle-notch fa-spin mr-2"></i>{{ runTime }}</div>
			</router-link>
        </nav>

        <div class="flex flex-1 flex-col lg:flex-row lg:relative overflow-hidden">
            <div ref="scroll" class="flex-1 justify-center pt-4 px-2 w-full lg:px-4 lg:order-last overflow-y-auto scrolling-touch mb-1">
                <transition name="fade" mode="out-in">
                    <router-view></router-view>
                </transition>
            </div>

            <!-- Bottom Nav -->
            <nav aria-label="bottom-navigation" class="sticky bottom-0 p-2 flex justify-around items-center bg-card border-t-2 border-background lg:order-first lg:flex-col lg:justify-start lg:items-start lg:w-1/6 lg:max-w-nav lg:p-2 xl:p-3 lg:border-none">
                <router-link
                    :to="{ name: 'live' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-plus fa-lg lg:w-1/5 lg:mr-2 text-muted-light"></i><span class="hidden lg:block text-lg font-medium">Session</span>
                </router-link>
                <router-link
                    :to="{ name: 'statistics' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-chart-line fa-lg lg:w-1/5 lg:mr-2 text-muted-light"></i><span class="hidden lg:block text-lg font-medium">Statistics</span>
                </router-link>
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2 lg:order-first"
                    exact
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-th-large fa-lg lg:w-1/5 lg:mr-2 text-muted-light"></i><span class="hidden lg:block text-lg font-medium">Dashboard</span>
                </router-link>
                <router-link
                    :to="{ name: 'sessions' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-bars fa-lg lg:w-1/5 lg:mr-2 text-muted-light"></i><span class="hidden lg:block text-lg font-medium">Sessions</span>
                </router-link>
                <router-link
                    :to="{ name: 'settings' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-cog fa-lg lg:w-1/5 lg:mr-2 text-muted-light"></i><span class="hidden lg:block text-lg font-medium">Settings</span>
                </router-link>
            </nav>
            
        </div>

        <vue-snotify/>

        <modals-container/>
        <v-dialog/>

    </div>
</template>

<script>
import moment from 'moment'
import 'moment-duration-format'
import { mapState, mapGetters } from 'vuex'

export default {
    name: 'App',
    props: ['user', 'stakes', 'limits', 'variants', 'table_sizes'],
    data(){
        return {
            sessionsScrollTo: 0,
            now: moment().utc(),
            runTimeInterval: null,
        }
    },
    computed: {
        ...mapState('live', ['liveSession']),
        ...mapGetters('live', ['sessionInProgress']),
        ...mapGetters('filters', ['unfilteredFilters']),
        runTime() {
			let start_time = moment.utc(this.liveSession.start_time)
			let diff = this.now.diff(start_time, 'hours', true)
			return moment.duration(diff, 'hours').format("hh:mm:ss", { trim: false})
        },
        variables() {
            return {
                stakes: this.stakes,
                limits: this.limits,
                variants: this.variants,
                table_sizes: this.table_sizes,
            }
        }
    },
    watch: {
        sessionInProgress: function(running) {
            // running is the updated value on watcher.
            // If sessionInProgress returns true then running else not running.
            if (running) {
                this.runTimeInterval = setInterval(() => {
                    this.now = moment().utc()
                }, 1000)
            } else {
                clearInterval(this.runTimeInterval)
                this.runTimeInterval = null
            }
        }
    },
    created() {
        this.$store.dispatch('populateState', { user: this.user, variables: this.variables })
        this.$store.dispatch('bankroll/getBankrollTransactions')
        this.$store.dispatch('cash_games/getCashGames')
        this.$store.dispatch('tournaments/getTournaments')
        this.$store.dispatch('live/currentLiveSession')

        // By default currentFilters is set to an empty object.
        // If reloading the page and currentFilters is an empty object, then populate with the default unfilteredFilters
        // Else currentFilters will be the persisted currentFilters state, so filters are saved on page reloads.
        if (Object.keys(this.$store.state.filtered_sessions.currentFilters).length === 0) {
            this.$store.dispatch('filtered_sessions/resetFilters', this.unfilteredFilters)
        }

        // App uses main-content ref=scroll as a scrollable div for main content, where as vue-router uses window.scrollTop for scrollBehaviour
        // which is always at 0,0 because it's fixed and overflow is hidden.
        // Code found on https://github.com/vuejs/vue-router/issues/1187
        // I only want to save the scroll position from sessions->session so that if the user clicks back (session->sessions)
        // they will be in the same scroll position with the list of sessions.

        this.$router.afterEach( (to, from) => {

            let scrollTo = 0;
            
            // If sessions->session then save current scroll position but continue to scroll to 0 on session
            if (from.name === 'sessions' && to.name === 'session') {
                this.sessionsScrollTo = this.$refs.scroll.scrollTop
            }

            // If session->sessions then load saved sessions scroll position
            if (from.name === 'session' && to.name === 'sessions') {
                scrollTo = this.sessionsScrollTo
            }

            this.$nextTick(()=>{
                this.$refs.scroll.scrollTop = scrollTo
            });
        });
    }
}
</script>

<style scoped>

</style>