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
        runTime: state => {
            let start_time = moment.utc(state.liveSession.start_time)
			let diff = moment(state.now).diff(start_time, 'hours', true)
			return moment.duration(diff, 'hours').format("hh:mm:ss", { trim: false})
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
        startLiveSession({ commit }, {game_type, session}) {
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
        endLiveSession({ commit }, cashOut) {
            return axios.post('/api/live/end', cashOut)
            .then(response => {
                commit('END_LIVE_SESSION', response.data.game)
                if (response.data.game.game_type === 'cash_game') {
                    commit('cash_games/ADD_CASH_GAME', response.data.game, { root: true})
                } else if (response.data.game.game_type === 'tournament')
                    commit('tournaments/ADD_TOURNAMENT', response.data.game, { root: true})
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