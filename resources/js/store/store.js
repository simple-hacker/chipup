// vuex
import Vue from 'vue'
import Vuex from 'vuex'

import createPersistedState from "vuex-persistedstate"

import bankroll from '@modules/bankroll'
import cash_games from '@modules/cash_games'
import tournaments from '@modules/tournaments'
import sessions from '@modules/sessions'
import transactions from '@modules/transactions'
import live from '@modules/live'
import filters from '@modules/filters'

Vue.use(Vuex)

const store = new Vuex.Store({
    strict: true,
    modules: {
        bankroll,
        cash_games,
        tournaments,
        sessions,
        transactions,
        live,
        filters,
    },
    plugins: [createPersistedState({
        paths: [
            // 'sessions.sessions',
            'cash_games.cash_games',
            'tournaments.tournaments',
            'sessions.loadSessionState',
        ],
    })],
    state: {
        user: {},
        stakes: [],
        limits: [],
        variants: [],
        table_sizes: [],
    },
    mutations: {
        SET_USER(state, user) {
            state.user = user
        },
        SET_EMAIL(state, email) {
            state.user.email = email
        },
        SET_DEFAULT_VALUES(state, default_values) {
            Object.keys(default_values).forEach(variable => {
                state.user[variable] = default_values[variable]
            })
        },
        SET_VARIABLES(state, variables) {
            state.stakes = variables.stakes
            state.limits = variables.limits
            state.variants = variables.variants
            state.table_sizes = variables.table_sizes
        }
    },
    actions: {
        populateState({ commit }, payload) {
            commit('SET_USER', payload.user)
            commit('SET_VARIABLES', payload.variables)
        },
        updateEmailAddress({ commit }, email) {
            return axios.post('/settings/email', { email: email })
            .then(response => {
                if (response.data.success === true) {
                    commit('SET_EMAIL', email)
                } else {
                    reject({response: {data: { message: 'Something went wrong.'}}})
                }
            })
            .catch(error => { throw error })
        },
        updateDefaultValues({ commit }, default_values) {
            return axios.post('/settings/defaults', default_values)
            .then(response => {
                if (response.data.success === true) {
                    commit('SET_DEFAULT_VALUES', default_values)
                } else {
                    reject({response: {data: { message: 'Something went wrong.'}}})
                }
            })
            .catch(error => { throw error })
        }
    }
})

export default store