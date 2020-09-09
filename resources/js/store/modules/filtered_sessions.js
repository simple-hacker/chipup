import moment from 'moment'

import stakeMixin from '@/mixins/stake'

export default {
    namespaced: true,
    state: {
        currentFilters: {}
    },
    getters: {
        filtersApplied: (state, getters, rootState, rootGetters) => {
            return ! _.isEqual(state.currentFilters, rootGetters['filters/unfilteredFilters'])
        },
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
                const validProfitRange = (state.currentFilters.profitRange && session.locale_profit >= state.currentFilters.profitRange[0] && session.locale_profit <= state.currentFilters.profitRange[1])
                // If validFromDate is set, session start_time must be greater than or equal to fromDate, else just return true
                const validFromDate = state.currentFilters.fromDate ? moment.utc(session.start_time) >= moment(state.currentFilters.fromDate) : true
                // If validToDate is set, session start_time must be less than or equal to toDate, else just return true
                const validToDate = state.currentFilters?.toDate ? moment.utc(session.start_time) <= moment(state.currentFilters.toDate) : true
                // Valid location
                const validLocation = state.currentFilters?.locations.includes(session.location)
                // Valid stakes
                const validStake = state.currentFilters?.cashGameFilters?.stakes.includes(stakeMixin.methods.stakeLabel(session.stake))
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
                const validProfitRange = (state.currentFilters.profitRange && session.locale_profit >= state.currentFilters.profitRange[0] && session.locale_profit <= state.currentFilters.profitRange[1])
                // If validFromDate is set, session start_time must be greater than or equal to fromDate, else just return true
                const validFromDate = state.currentFilters.fromDate ? moment.utc(session.start_time) >= moment(state.currentFilters.fromDate) : true
                // If validToDate is set, session start_time must be less than or equal to toDate, else just return true
                const validToDate = state.currentFilters?.toDate ? moment.utc(session.start_time) <= moment(state.currentFilters.toDate) : true
                // Valid location
                const validLocation = state.currentFilters?.locations.includes(session.location)
                // Valid BuyIn Range
                // Add up the buy in, add ons and rebuys
                const sessionBuyIn = session?.buy_in?.locale_amount ?? 0
                const sessionRebuys = session?.rebuys.reduce((total, rebuy) => total + rebuy.locale_amount, 0) ?? 0
                const sessionAddOns = session?.add_ons.reduce((total, add_on) => total + add_on.locale_amount, 0) ?? 0
                const totalBuyIn = sessionBuyIn + sessionRebuys + sessionAddOns
                const validBuyInRange = (state.currentFilters.tournamentFilters.buyInRange && totalBuyIn >= state.currentFilters.tournamentFilters.buyInRange[0] && totalBuyIn <= state.currentFilters.tournamentFilters.buyInRange[1])
                // Valid limits
                const validLimit = state.currentFilters?.tournamentFilters?.limits.includes(session.limit.limit)
                // Valid variants
                const validVariant = state.currentFilters?.tournamentFilters?.variants.includes(session.variant.variant)

                return validProfitRange && validFromDate && validToDate && validLocation && validLimit && validVariant && validBuyInRange
            })
            return filtered
        },
        filteredSessions: (state, getters, rootState) => {
            return [...getters.filteredCashGames, ...getters.filteredTournaments].sort(rootState.filters.sortByDate)
        },
        numberOfSessions: (state, getters) => {
            return getters.filteredSessions.length
        },
        cashGameProfit: (state, getters) => {
            return getters.filteredCashGames.reduce((total, session) => total + session.locale_profit, 0)
        },
        tournamentProfit: (state, getters) => {
            return getters.filteredTournaments.reduce((total, session) => total + session.locale_profit, 0)
        },
        totalProfit: (state, getters) => {
            return getters.filteredSessions.reduce((total, session) => total + session.locale_profit, 0)
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
                let buyInTotal = session?.buy_in?.locale_amount ?? 0
				let buyInsTotal = (session.buy_ins) ? session.buy_ins.reduce((total, buy_in) => total + buy_in.locale_amount, 0) : 0
				let addOnTotal = (session.add_ons) ? session.add_ons.reduce((total, add_ons) => total + add_ons.locale_amount, 0) : 0
				let rebuyTotal = (session.rebuys) ? session.rebuys.reduce((total, rebuys) => total + rebuys.locale_amount, 0) : 0
				let expenseTotal = (session.expenses) ? session.expenses.reduce((total, expenses) => total + expenses.locale_amount, 0) : 0
				return total + buyInTotal + buyInsTotal + addOnTotal + rebuyTotal + expenseTotal
            }, 0)
        },
        totalCashes: (state, getters) => {
            return getters.filteredSessions.reduce((total, session) => total + (session.cash_out?.locale_amount ?? 0), 0)
        },
        roi: (state, getters) => {
            return (getters.totalProfit / getters.totalBuyIns) * 100
        },
        profitPerHour: (state, getters) => {
            return getters.totalProfit / getters.totalDuration
        },
        profitPerSession: (state, getters) => {
            return getters.totalProfit / getters.numberOfSessions
        },
        sessionsProfitSeries: (state, getters) => {
            return [...getters.filteredSessions]
                    .reverse()
                    .reduce((series, session, index) => {
                        // Get the previous profit of the series so we can add on to the runing total.  Default to 0 on invalid index.
                        // y property of the series is profit
                        let runningTotal = series[index -1]?.y ?? 0
                        // Push new object in to series array, where x is the date of the session
                        // and y is the runningTotal adding on the current session's profit.
                        series.push({
                            x: moment.utc(session.start_time).format(),
                            y: runningTotal + session.locale_profit
                        })
                        return series
                    }, [])
        },
        cashGamesProfitSeries: (state, getters, rootState) => {
            return [...getters.filteredCashGames]
                    .sort(rootState.filters.sortByDateAsc)
                    .reduce((series, session, index) => {
                        // Get the previous profit of the series so we can add on to the runing total.  Default to 0 on invalid index.
                        // y property of the series is profit
                        let runningTotal = series[index -1]?.y ?? 0
                        // Push new object in to series array, where x is the date of the session
                        // and y is the runningTotal adding on the current session's profit.
                        series.push({
                            x: moment.utc(session.start_time).format(),
                            y: runningTotal + session.locale_profit
                        })
                        return series
                    }, [])
        },
        tournamentsProfitSeries: (state, getters, rootState) => {
            return [...getters.filteredTournaments]
                    .sort(rootState.filters.sortByDateAsc)
                    .reduce((series, session, index) => {
                        // Get the previous profit of the series so we can add on to the runing total.  Default to 0 on invalid index.
                        // y property of the series is profit
                        let runningTotal = series[index -1]?.y ?? 0
                        // Push new object in to series array, where x is the date of the session
                        // and y is the runningTotal adding on the current session's profit.
                        series.push({
                            x: moment.utc(session.start_time).format(),
                            y: runningTotal + session.locale_profit
                        })
                        return series
                    }, [])
        },
        gameTypeSeries: (state, getters) => {
            return {
                game_types: ['Cash Games', 'Tournaments'],
                profits: [
                    getters.filteredCashGames.reduce((total, session) => total + session.locale_profit, 0),
                    getters.filteredTournaments.reduce((total, session) => total + session.locale_profit, 0)
                ],
                counts: [
                    getters.filteredCashGames.length,
                    getters.filteredTournaments.length
                ]
            }
        },
        stakeSeries: (state, getters) => {

            console.log('filtered', getters.filteredCashGames)
            return getters.filteredCashGames
                // Sort by small blind values first, and then by big blind value if small blind is the same
                .sort((a, b) => a.stake.small_blind - b.stake.small_blind || a.stake.big_blind - b.stake.big_blind)
                .reduce((series, session) => {
                    if (series.profits.hasOwnProperty(session.stake.full_stake)) {
                        series.profits[session.stake.full_stake] += session.locale_profit
                    } else {
                        series.profits[session.stake.full_stake] = session.locale_profit
                    }
                    if (series.counts.hasOwnProperty(session.stake.full_stake)) {
                        series.counts[session.stake.full_stake] += 1
                    } else {
                        series.counts[session.stake.full_stake] = 1
                    }
                    return series
                }, { profits: {}, counts: {} })
        },
        variantSeries: (state, getters) => {
            return getters.filteredSessions
                // Sort by variant id and then limit id
                .sort((a, b) => a.variant.id - b.variant.id || a.limit.id - b.limit.id)
                .reduce((series, session) => {
                    if (series.profits.hasOwnProperty(`${session.limit.limit} ${session.variant.variant}`)) {
                        series.profits[`${session.limit.limit} ${session.variant.variant}`] += session.locale_profit
                    } else {
                        series.profits[`${session.limit.limit} ${session.variant.variant}`] = session.locale_profit
                    }
                    if (series.counts.hasOwnProperty(`${session.limit.limit} ${session.variant.variant}`)) {
                        series.counts[`${session.limit.limit} ${session.variant.variant}`] += 1
                    } else {
                        series.counts[`${session.limit.limit} ${session.variant.variant}`] = 1
                    }
                    return series
                }, { profits: {}, counts: {} })
        },
        locationSeries: (state, getters) => {
            return getters.filteredSessions.reduce((series, session) => {
                if (series.profits.hasOwnProperty(session.location)) {
                    series.profits[session.location] += session.locale_profit
                } else {
                    series.profits[session.location] = session.locale_profit
                }
                if (series.counts.hasOwnProperty(session.location)) {
                    series.counts[session.location] += 1
                } else {
                    series.counts[session.location] = 1
                }
                return series
            }, { profits: {}, counts: {} })
        }
    },
    mutations: {
        APPLY_FILTERS(state, filters) {
            state.currentFilters = filters
        },
    },
    actions: {
        applyFilters({ commit }, filters) {
            commit('APPLY_FILTERS', filters)
        },
        resetFilters({ commit, rootGetters }) {
            commit('APPLY_FILTERS', rootGetters['filters/unfilteredFilters'])
        }
    }
}