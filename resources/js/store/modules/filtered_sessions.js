import moment from 'moment'

export default {
    namespaced: true,
    state: {
        currentFilters: {}
    },
    getters: {
        //NOTE: If I start getting errors it could be because currentFilters object keys don't exist
        // as it wasn't loaded properly so defaults to {} as above.
        filteredCashGames: (state, getters, rootState) => {
            // If cash_game is not included in filters then return empty object
            if (! state.currentFilters.gameTypes.includes('cash_game')) {
                return []
            }

            // Else continue applying filters.
            const filtered = rootState.cash_games.cash_games.filter(session => {
                // Session profit must be equal and between profitRange
                const validProfitRange = (state.currentFilters.profitRange && session.profit >= state.currentFilters.profitRange[0] && session.profit <= state.currentFilters.profitRange[1])
                // If validFromDate is set, session start_time must be greater than or equal to fromDate, else just return true
                const validFromDate = state.currentFilters.fromDate ? moment.utc(session.start_time) >= moment(state.currentFilters.fromDate) : true
                // If validToDate is set, session start_time must be less than or equal to toDate, else just return true
                const validToDate = state.currentFilters?.toDate ? moment.utc(session.start_time) <= moment(state.currentFilters.toDate) : true
                // Valid location
                const validLocation = state.currentFilters?.locations.includes(session.location)
                // Valid stakes
                const validStake = state.currentFilters?.cashGameFilters?.stakes.includes(session.stake.stake)
                // Valid limits
                const validLimit = state.currentFilters?.cashGameFilters?.limits.includes(session.limit.limit)
                // Valid variants
                const validVariant = state.currentFilters?.cashGameFilters?.variants.includes(session.variant.variant)
                // Valid table_sizes
                const validTableSize = state.currentFilters?.cashGameFilters?.table_sizes.includes(session.table_size.table_size)

                return validProfitRange && validFromDate && validToDate && validLocation && validStake && validLimit && validVariant && validTableSize
            })

            return filtered
        },
        filteredTournaments: (state, getters, rootState) => {
            // If tournament is not included in filters then return empty object
            if (! state.currentFilters.gameTypes.includes('tournament')) {
                return []
            }

            // Else continue applying filters.
            const filtered = rootState.tournaments.tournaments.filter(session => {
                // Session profit must be equal and between profitRange
                const validProfitRange = (state.currentFilters.profitRange && session.profit >= state.currentFilters.profitRange[0] && session.profit <= state.currentFilters.profitRange[1])
                // If validFromDate is set, session start_time must be greater than or equal to fromDate, else just return true
                const validFromDate = state.currentFilters.fromDate ? moment.utc(session.start_time) >= moment(state.currentFilters.fromDate) : true
                // If validToDate is set, session start_time must be less than or equal to toDate, else just return true
                const validToDate = state.currentFilters?.toDate ? moment.utc(session.start_time) <= moment(state.currentFilters.toDate) : true
                // Valid location
                const validLocation = state.currentFilters?.locations.includes(session.location)
                // Valid BuyIn Range
                // Add up the buy in, add ons and rebuys
                const sessionBuyIn = session?.buy_in?.amount ?? 0
                const sessionRebuys = session?.rebuys.reduce((total, rebuy) => total + rebuy.amount, 0) ?? 0
                const sessionAddOns = session?.add_ons.reduce((total, add_on) => total + add_on.amount, 0) ?? 0
                const totalBuyIn = sessionBuyIn + sessionRebuys + sessionAddOns
                const validBuyInRange = (state.currentFilters.tournamentFilters.buyInRange && totalBuyIn >= state.currentFilters.tournamentFilters.buyInRange[0] && totalBuyIn <= state.currentFilters.tournamentFilters.buyInRange[1])
                // Valid limits
                const validLimit = state.currentFilters?.tournamentFilters?.limits.includes(session.limit.limit)
                // Valid variants
                const validVariant = state.currentFilters?.tournamentFilters?.variants.includes(session.variant.variant)

                return validProfitRange && validFromDate && validToDate && validLocation && validBuyInRange && validLimit && validVariant
            })

            // Else continue applying filters.
            return filtered
        },
        filteredSessions: (state, getters, rootState) => {
            return [...getters.filteredCashGames, ...getters.filteredTournaments].sort(rootState.filters.sortByDate)
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