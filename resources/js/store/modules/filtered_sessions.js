import moment from 'moment'

export default {
    namespaced: true,
    state: {
        currentFilters: {}
    },
    getters: {
        filteredCashGames: (state, getters, rootState) => {
            return rootState.cash_games.cash_games
        },
        filteredTournaments: (state, getters, rootState) => {
            return rootState.tournaments.tournaments
        },
        filteredSessions: (state, getters, rootState) => {
            return [...getters.filteredCashGames, ...getters.filteredTournaments]
        },
        numberOfSessions: (state, getters) => {
            return getters.filteredSessions.length
        },
        totalProfit: (state, getters) => {
            return getters.filteredSessions.reduce((total, session) => total + session.profit, 0)
        },
        totalDuration: (state, getters) => {
            return getters.filteredSessions.reduce((total, session) => {
            	const end_time = moment.utc(session.end_time)
            	const start_time = moment.utc(session.start_time)
                return total + end_time.diff(start_time, 'hours', true)
            }, 0)
        },
        averageDuration: (state, getters) => {
            return getters.totalDuration / getters.numberOfSessions
        },
        totalBuyIns: (state, getters) => {
            return getters.filteredSessions.reduce((total, session) => {
                let buyInTotal = session?.buy_in?.amount ?? 0
				let buyInsTotal = (session.buy_ins) ? session.buy_ins.reduce((total, buy_in) => total + buy_in.amount, 0) : 0
				let addOnTotal = (session.add_ons) ? session.add_ons.reduce((total, add_ons) => total + add_ons.amount, 0) : 0
				let rebuyTotal = (session.rebuys) ? session.rebuys.reduce((total, rebuys) => total + rebuys.amount, 0) : 0
				let expenseTotal = (session.expenses) ? session.expenses.reduce((total, expenses) => total + expenses.amount, 0) : 0
				return total + buyInTotal + buyInsTotal + addOnTotal + rebuyTotal + expenseTotal
            }, 0)
        },
        totalCashes: (state, getters) => {
            return getters.filteredSessions.reduce((total, session) => total + (session.cash_out?.amount ?? 0), 0)
        },
        averageROI: (state, getters) => {
            return getters.totalProfit / getters.totalBuyIns
        },
        profitPerHour: (state, getters) => {
            return getters.totalProfit / getters.totalDuration
        },
        profitPerSession: (state, getters) => {
            return getters.totalProfit / getters.numberOfSessions
        },
    },
    mutations: {
        APPLY_FILTERS(state, filters) {
            state.currentFilters = filters
        },
        RESET_FILTERS(state, unfilteredFilters) {
            state.currentFilters = unfilteredFilters
        }
    },
    actions: {
        applyFilters({ commit }, filters) {
            commit('APPLY_FILTERS', filters)
        },
        resetFilters({ commit }, unfilteredFilters) {
            commit('RESET_FILTERS', unfilteredFilters)
        }
    }
}