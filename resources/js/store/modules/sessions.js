import moment from 'moment'

export default {
    namespaced: true,
    state: {
        loadSessionState: {},
    },
    getters: {
        sessions: (state, getters, rootState) => {
            return [
                ...rootState.cash_games.cash_games,
                ...rootState.tournaments.tournaments
            ].sort(rootState.filters.sortByDate)
        },
        getSession: (state, getters, rootState, rootGetters) => (id, game_type) => {
            if (game_type === 'cash_game') {
                return rootGetters.cash_games.CashGameById(id)
            } else if (game_type === 'tournament') {
                return rootGetters.tournament.TournamentById(id)
            } else {
                throw new Error('Unknown Game Type')
            }
        },
        numberOfSessions: (state, getters) => {
            return getters.sessions.length
        },
        totalProfit: (state, getters) => {
            return getters.sessions.reduce((total, session) => total + session.profit, 0)
        },
        totalDurationHours: (state, getters) => {
            return getters.sessions.reduce((total, session) => {
            	const end_time = moment.utc(session.end_time)
            	const start_time = moment.utc(session.start_time)
                return total + end_time.diff(start_time, 'hours', true)
            }, 0)
        },
        totalBuyIns: (state, getters) => {
            return getters.sessions.reduce((total, session) => {
                let buyInTotal = session?.buy_in?.amount ?? 0
				let buyInsTotal = (session.buy_ins) ? session.buy_ins.reduce((total, buy_in) => total + buy_in.amount, 0) : 0
				let addOnTotal = (session.add_ons) ? session.add_ons.reduce((total, add_ons) => total + add_ons.amount, 0) : 0
				let rebuyTotal = (session.rebuys) ? session.rebuys.reduce((total, rebuys) => total + rebuys.amount, 0) : 0
				let expenseTotal = (session.expenses) ? session.expenses.reduce((total, expenses) => total + expenses.amount, 0) : 0
				return total + buyInTotal + buyInsTotal + addOnTotal + rebuyTotal + expenseTotal
            }, 0)
        },
        totalCashes: (state, getters) => {
            return getters.sessions.reduce((total, session) => total + (session.cash_out?.amount ?? 0), 0)
        },
        averageROI: (state, getters) => {
            return getters.totalProfit / getters.totalBuyIns
        },
        profitPerHour: (state, getters) => {
            return getters.totalProfit / getters.totalDurationHours
        },
        profitPerSession: (state, getters) => {
            return getters.totalProfit / getters.numberOfSessions
        },
        averageDuration: (state, getters) => {
            return moment.duration(getters.totalDurationHours / getters.numberOfSessions, 'hours').format("d [days] h [hours] m [mins]")
        },
        totalDuration: (state, getters) => {
            return moment.duration(getters.totalDurationHours, 'hours').format("d [days] h [hours] m [mins]")
        },
    },
    mutations: {
        LOAD_SESSION(state, session) {
            state.loadSessionState = session
        },
    },
    actions: {
        saveLoadSession({ commit }, session) {
            commit('LOAD_SESSION', session)
        },
        updateSession({ dispatch, commit }, session) {
            return new Promise((resolve, reject) => {
                if (session.game_type === 'cash_game') {
                    dispatch('cash_games/updateCashGame', session, { root: true }).then(response => resolve(response)).catch(error => reject(error))
                } else if (session.game_type === 'tournament') {
                    dispatch('tournaments/updateTournament', session, { root: true}).then(response => resolve(response)).catch(error => reject(error))
                } else {
                    reject({response: {data: { message: 'Unknown Game Type'}}})
                }
            })
        },
        destroySession({ dispatch, commit }, session) {
            return new Promise((resolve, reject) => {
                if (session.game_type === 'cash_game') {
                    dispatch('cash_games/destroyCashGame', session, { root: true })
                    .then(response => {
                        // If session was successfully destroy then clear load session details in case
                        // user directly visits /session after deleting, it won't load the old deleted session.
                        commit('LOAD_SESSION', {})
                        resolve(response)
                    })
                    .catch(error => reject(error))
                } else if (session.game_type === 'tournament') {
                    dispatch('tournaments/destroyTournament', session, { root: true})
                    .then(response => {
                        // If session was successfully destroy then clear load session details in case
                        // user directly visits /session after deleting, it won't load the old deleted session.
                        commit('LOAD_SESSION', {})
                        resolve(response)
                    })
                    .catch(error => reject(error))
                } else {
                    reject({response: {data: { message: 'Unknown Game Type'}}})
                }
            })
        }
    }
}