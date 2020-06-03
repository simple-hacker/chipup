import {router} from '@/app.js'

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
        }
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