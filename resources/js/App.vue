<template>
    <div class="flex flex-col h-screen">
        <!-- Top Nav -->
        <nav aria-label="top-navigation" class="sticky top-0 z-50 flex justify-between items-center px-3 py-2 bg-card border-b-2 border-background">
            <h1 class="text-2xl font-bold text-white">
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="flex items-center"
                >
                    <svg viewBox="-29.65 -29.65 355.78 355.78" style="enable-background:new 0 0 296.477 296.477;" width="60px" height="60px" fill="#48bb78" stroke="#48bb78" stroke-width="0" transform="matrix(1, 0, 0, 1, 0, 0)"><g id="IconsRepo_bgCarrier"></g> <path d="M244.63,35.621c-21.771-18.635-47.382-29.855-73.767-33.902C121.871-5.797,70.223,11.421,35.622,51.847 c-53.236,62.198-45.972,155.773,16.226,209.01c21.771,18.634,47.381,29.853,73.766,33.901 c48.991,7.517,100.641-9.703,135.241-50.13C314.091,182.431,306.826,88.856,244.63,35.621z M273.361,191.241l-45.305-15.618 c6.102-17.803,6.028-37.107,0.014-54.724l45.257-15.575c3.577,10.453,5.862,21.429,6.74,32.741 C281.489,156.374,279.152,174.388,273.361,191.241z M275.905,104.058c0-0.003,0-0.005,0-0.008 C275.905,104.053,275.905,104.055,275.905,104.058z M247.935,61.472l-36.069,31.332c-2.669-3.055-5.579-5.961-8.752-8.677 c-11.467-9.814-24.81-15.995-38.637-18.692l9.095-46.741c22.33,4.33,43.21,14.294,60.635,29.209 C239.147,52.131,243.728,56.669,247.935,61.472z M103.251,23.983c6.428-2.315,13.021-4.109,19.71-5.388l9.087,46.843 c-17.789,3.467-34.584,12.651-47.393,27.341L48.55,61.38C63.334,44.416,82.206,31.568,103.251,23.983z M23.124,105.236 l45.297,15.617c-6.102,17.803-6.028,37.105-0.015,54.723l-45.295,15.588c-3.562-10.441-5.837-21.4-6.713-32.688 C14.976,140.151,17.32,122.11,23.124,105.236z M48.467,235.066l36.145-31.395c2.669,3.056,5.58,5.964,8.754,8.68 c11.466,9.814,24.808,15.993,38.634,18.691l-9.143,46.997c-22.325-4.348-43.185-14.422-60.604-29.333 C57.288,244.458,52.689,239.898,48.467,235.066z M193.203,272.635c-6.409,2.309-12.986,4.11-19.658,5.403l-9.117-47 c17.789-3.467,34.585-12.651,47.394-27.342l36.121,31.409C233.154,252.087,214.257,265.047,193.203,272.635z"></path> <circle cx="93.372" cy="53.498" r="8"></circle> <circle cx="38.758" cy="148.382" r="8"></circle> <circle cx="93.623" cy="243.123" r="8"></circle> <circle cx="203.105" cy="242.977" r="8.001"></circle> <circle cx="257.717" cy="148.091" r="8"></circle> <circle cx="202.853" cy="53.351" r="8"></circle> </svg>
                    <span class="ml-4">Poker</span>
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
            <div ref="scroll" class="flex-1 justify-center pt-4 px-2 w-full lg:px-4 lg:order-last overflow-y-auto scrolling-touch">
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
        runTime() {
			let start_time = moment.utc(this.liveSession.start_time)
			let diff = this.now.diff(start_time, 'hours', true)
			return moment.duration(diff, 'hours').format("hh:mm:ss", { trim: false})
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
    beforeCreate() {
        this.$store.dispatch('bankroll/getBankrollTransactions')
        this.$store.dispatch('cash_games/getCashGames')
        this.$store.dispatch('tournaments/getTournaments')
        this.$store.dispatch('live/currentLiveSession')
    },
    created() {
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