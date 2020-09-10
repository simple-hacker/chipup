<template>
    <div class="flex flex-col h-full">
        <!-- Top Nav -->
        <nav aria-label="top-navigation" class="sticky top-0 z-50 flex justify-between items-center px-3 py-2 bg-gray-700 border-gray-800 border-b-2">
            <h1 class="text-2xl font-bold text-white">
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="flex items-center p-1"
                >
                    <img src="/images/logo.svg" alt="Poker Logo" width="60px" class="mr-3" />
                    <span v-text="appName"></span>
                </router-link>
            </h1>
            <router-link
				:to="{ name: 'live' }"
				class="btn btn-green"
                :class="!sessionInProgress ? 'btn btn-green' : 'btn-red'"
			>
                <div v-if="!sessionInProgress">Start Session</div>
                <div v-if="sessionInProgress"><i class="fas fa-circle-notch fa-spin mr-2"></i><span v-text="runTime"></span></div>
			</router-link>
        </nav>

        <div class="flex flex-1 flex-col lg:flex-row lg:relative overflow-hidden">

            <div ref="scroll" class="flex-1 justify-center pt-4 px-2 w-full lg:px-4 lg:order-last overflow-y-auto scrolling-touch mb-1">
                <transition name="fade" mode="out-in">
                    <router-view></router-view>
                </transition>
            </div>

            <!-- Bottom Nav -->
            <nav aria-label="bottom-navigation" class="bg-gray-700 border-gray-800 border-t-2 sticky bottom-0 p-2 flex justify-around items-center lg:order-first lg:flex-col lg:justify-start lg:items-start lg:w-1/6 lg:max-w-nav lg:p-2 xl:p-3 lg:border-none">
                <router-link
                    :to="{ name: 'live' }"
                    class="nav-link flex w-1/6 justify-center items-center rounded p-4 lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'nav-link-active'"
                >
                    <i v-if="!sessionInProgress" class="fas fa-plus fa-lg lg:w-1/5 lg:mr-2"></i>
                    <i v-if="sessionInProgress" class="text-white fas fa-circle-notch fa-spin lg:w-1/5 lg:mr-2"></i>
                    <span class="hidden lg:block text-lg font-medium">Live</span>
                </router-link>
                <router-link
                    :to="{ name: 'statistics' }"
                    class="nav-link flex w-1/6 justify-center items-center rounded p-4 lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'nav-link-active'"
                >
                    <i class="fas fa-chart-line fa-lg lg:w-1/5 lg:mr-2"></i><span class="hidden lg:block text-lg font-medium">Statistics</span>
                </router-link>
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="nav-link flex w-1/6 justify-center items-center rounded p-4 lg:w-full lg:justify-start lg:p-3 lg:mb-2 lg:order-first"
                    exact
                    :active-class="'nav-link-active'"
                >
                    <i class="fas fa-th-large fa-lg lg:w-1/5 lg:mr-2"></i><span class="hidden lg:block text-lg font-medium">Dashboard</span>
                </router-link>
                <router-link
                    :to="{ name: 'sessions' }"
                    class="nav-link flex w-1/6 justify-center items-center rounded p-4 lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'nav-link-active'"
                >
                    <i class="fas fa-bars fa-lg lg:w-1/5 lg:mr-2"></i><span class="hidden lg:block text-lg font-medium">Sessions</span>
                </router-link>
                <router-link
                    :to="{ name: 'settings' }"
                    class="nav-link flex w-1/6 justify-center items-center rounded p-4 lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'nav-link-active'"
                >
                    <i class="fas fa-cog fa-lg lg:w-1/5 lg:mr-2"></i><span class="hidden lg:block text-lg font-medium">Settings</span>
                </router-link>
            </nav>
            
        </div>

        <vue-snotify/>

        <modals-container/>
        <v-dialog/>

    </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'

export default {
    name: 'App',
    props: ['user', 'stakes', 'limits', 'variants', 'table_sizes'],
    data() {
        return {
            sessionsScrollTo: 0,
            appName: process.env.MIX_APP_NAME
        }
    },
    computed: {
        ...mapState('cash_games', ['cash_games', 'defaultCashGame']),
        ...mapState('filtered_sessions', ['currentFilters']),
        ...mapGetters('live', ['sessionInProgress', 'runTime']),
        ...mapGetters('filters', ['unfilteredFilters']),
        variables() {
            return {
                stakes: this.stakes,
                limits: this.limits,
                variants: this.variants,
                table_sizes: this.table_sizes,
            }
        },
    },
    watch: {
        sessionInProgress: function(running) {
            // running is the updated value on watcher.
            // If sessionInProgress returns true then running else not running.
            if (running) {
                this.$store.dispatch('live/startRunTime')
            } else {
                this.$store.dispatch('live/endRunTime')
            }
        }
    },
    created() {
        // ****
        // Filter bug Fix 2020-09-10
        // All of these dispatches which get the session data is asynchronous.
        // So checking for currentFilters was firing before data had been retrieved, and those the unfilteredFilters was completely blank
        // Only perform the filter check when we have all the data.
        // ***

        Promise.all([
            this.$store.dispatch('populateState', { user: this.user, variables: this.variables }),
            this.$store.dispatch('bankroll/getBankrollTransactions'),
            this.$store.dispatch('cash_games/getCashGames'),
            this.$store.dispatch('tournaments/getTournaments'),
            this.$store.dispatch('live/currentLiveSession'),
        ]).then(() => {
            // By default currentFilters is set to an empty object.
            // If reloading the page and currentFilters is an empty object, then populate with the default unfilteredFilters
            // Else currentFilters will be the persisted currentFilters state, so filters are saved on page reloads.
            if (Object.keys(this.$store.state.filtered_sessions.currentFilters).length === 0) {
                this.$store.dispatch('filtered_sessions/resetFilters')
            }
        })

        if (this.sessionInProgress) {
            this.$store.dispatch('live/startRunTime')
        }

        // Set the i18n locale to user preferred locale
        // Used for displaying numbers, currencies and in the future language translations
        this.$i18n.locale = this.user.locale

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
    },
}
</script>

<style scoped>

</style>