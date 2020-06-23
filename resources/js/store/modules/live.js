import moment from 'moment'
import 'moment-duration-format'

export default {
    namespaced: true,
    state: {
        liveSession: {},
        now: moment().utc(),
        runTimeInterval: null,
    },
    getters: {
        sessionInProgress: state => {
            return (Object.keys(state.liveSession).length > 0)
        },
        runTime: (state, getters) => {
			return moment.duration(getters.runTimeHours, 'hours').format("hh:mm:ss", { trim: false})
        },
        runTimeHours: state => {
            let start_time = moment.utc(state.liveSession.start_time)
			return moment(state.now).diff(start_time, 'hours', true)
        },
        liveSessionId: state => {
            return state.liveSession.id
        },
        isLiveSession: state => game => {
            return state.liveSession.id === game.id && state.liveSession.game_type === game.game_type
        }
    },
    mutations: {
        ASSIGN_LIVE_SESSION(state, session) {
            state.liveSession = session
        },
        END_LIVE_SESSION(state, session) {
            state.liveSession = {}
        },
        UPDATE_LIVE_SESSION(state, session) {
            state.liveSession = session
        },
        UPDATE_NOW(state) {
            state.now = moment().utc()
        },
        START_RUNTIME(state, interval) {
            state.now = moment().utc()
            state.runTimeInterval = interval
        },
        END_RUNTIME(state) {
            clearInterval(state.runTimeInterval)
            state.runTimeInterval = null
        }
    },
    actions: {
        startLiveSession({ commit }, { game_type, session }) {
            let url = ''
            if (game_type === 'cash_game') {
                url = '/api/cash/live/start'
            } else if (game_type === 'tournament') {
                url = '/api/tournament/live/start'
            }
            return axios.post(url, session)
            .then(response => {
                commit('ASSIGN_LIVE_SESSION', response.data.game)
            })
            .catch(error => {
                throw error
            })
        },
        currentLiveSession({ commit }) {
            return axios.get('/api/live/current')
            .then(response => {
                if (response.data.success === true) {
                    commit('ASSIGN_LIVE_SESSION', response.data.game)
                } else {
                    commit('ASSIGN_LIVE_SESSION', {})
                }
            })
            .catch(error => {
                throw error
            })
        },
        updateLiveSession({ commit }, session) {
            let url = ''
            if (session.game_type === 'cash_game') {
                url = '/api/cash/live/update'
            } else if (session.game_type === 'tournament') {
                url = '/api/tournament/live/update'
            }
            return axios.patch(url, session)
            .then(response => {
                commit('UPDATE_LIVE_SESSION', response.data.game)
            })
            .catch(error => {
                throw error
            })
        },
        endLiveSession({ commit, dispatch, rootGetters }, cashOut) {
            return axios.post('/api/live/end', cashOut)
            .then(response => {
                // When ending a new live session it's not included in the sessions because filters have been applied
                // as the live session exists outside of the date, buy_in filters etc.
                // First, determine if the user had applied filters (because we'll keep those filters applied afterwards)
                // We'll need to reset the filters after commiting if there are NO filters currently applied.
                // This check needs to be done here before commiting sessions to Cash Games or Tournaments
                // as the currentFilters(=unfilteredFilters) will not equal the new unfilteredFilters after commiting.
                let resetFilters = ! rootGetters['filtered_sessions/filtersApplied']
                //End the Session
                commit('END_LIVE_SESSION', response.data.game)
                // Commit session to relevant game.
                if (response.data.game.game_type === 'cash_game') {
                    commit('cash_games/ADD_CASH_GAME', response.data.game, { root: true })
                } else if (response.data.game.game_type === 'tournament')
                    commit('tournaments/ADD_TOURNAMENT', response.data.game, { root: true })

                // If there were no filters applied earlier, then reset currentFilters to be the
                // new updated unfilteredFilters
                if (resetFilters)
                    dispatch({ type: 'filtered_sessions/resetFilters' }, { root: true })
            })
            .catch(error => {
                throw error
            })
        },
        startRunTime({ commit }) {
            let interval = setInterval(() => {
                commit('UPDATE_NOW')
            }, 1000)

            commit('START_RUNTIME', interval)
        },
        endRunTime({ commit }) {
            commit('END_RUNTIME')
        }
    }
}