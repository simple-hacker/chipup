export default {
    namespaced: true,
    state: {
        sortByProfit: (a, b) => {
            return b.profit - a.profit
        },
        sortByProfitDesc: (a, b) => {
            return a.profit - b.profit
        },
        sortByDate: (a , b) => {
            let startTimeA = new Date(a.start_time)
            let startTimeB = new Date(b.start_time)
            return startTimeA < startTimeB ? 1 : -1
        },
        sortByDateAsc: (a , b) => {
            let startTimeA = new Date(a.start_time)
            let startTimeB = new Date(b.start_time)
            return startTimeA > startTimeB ? 1 : -1
        },
    },
    getters: {
        unfilteredFilters: (state, getters) => {
            return {
                fromDate: '',
                toDate: '',
                gameTypes: [...getters.gameTypes],
                profitRange: getters.profitRange,
                cashGameFilters: {
                    stakes: getters.cashGameStakeFilters,
                    limits: getters.cashGameLimitFilters,
                    variants: getters.cashGameVariantFilters,
                    table_sizes: getters.cashGameTableSizeFilters,
                },
                tournamentFilters: {
                    buyInRange: getters.tournamentBuyInRange,
                    limits: getters.tournamentLimitFilters,
                    variants: getters.tournamentVariantFilters
                },
                locations: getters.locationFilters
            }
        },
        gameTypes: (state, getters, rootState, rootGetters) => {
            const gameTypes = rootGetters['sessions/sessions']
                                .map(session => { return session?.game_type ?? '' })
                                .filter(gameType => gameType) // Remove undefined

            return [...new Set(gameTypes)]
        },
        profitRange: (state, getters, rootState, rootGetters) => {
            const profits = rootGetters['sessions/sessions'].map(session => { return session?.profit ?? 0 })

            if (profits.length > 0) {
                const minProfit = Math.min(...profits) ?? 0
                const maxProfit = Math.max(...profits) ?? 0

                return [minProfit, maxProfit]
            }
            
            return [0, 0]
        },
        cashGameStakeFilters: (state, getters, rootState) => {
            let stakes =  rootState.cash_games.cash_games
                            .map(session => {
                                return session?.stake
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(stake => stake) // Remove undefined
                            .map(stake => stake.stake)

            return [...new Set(stakes)]
        },
        cashGameLimitFilters: (state, getters, rootState) => {
            let limits =  rootState.cash_games.cash_games
                            .map(session => {
                                return session?.limit
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(limit => limit)
                            .map(limit => limit.limit)

            return [...new Set(limits)]
        },
        cashGameVariantFilters: (state, getters, rootState) => {
            let variants =  rootState.cash_games.cash_games
                            .map(session => {
                                return session?.variant
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(variant => variant)
                            .map(variant => variant.variant)

            return [...new Set(variants)]
        },
        cashGameTableSizeFilters: (state, getters, rootState) => {
            let table_sizes =  rootState.cash_games.cash_games
                            .map(session => {
                                return session?.table_size
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(table_size => table_size)
                            .map(table_size => table_size.table_size)

            return [...new Set(table_sizes)]
        },
        tournamentBuyInRange: (state, getters, rootState) => {
            const buyIns = rootState.tournaments.tournaments.map(session => {
                const sessionBuyIn = session?.buy_in?.amount ?? 0
                const sessionRebuys = session?.rebuys.reduce((total, rebuy) => total + rebuy.amount, 0) ?? 0
                const sessionAddOns = session?.add_ons.reduce((total, add_on) => total + add_on.amount, 0) ?? 0
                return sessionBuyIn + sessionRebuys + sessionAddOns
            })

            if (buyIns.length > 0) {
                return [0, Math.max(...buyIns) ?? 0]
            }
            
            return [0, 0]
        },
        tournamentLimitFilters: (state, getters, rootState) => {
            let limits =  rootState.tournaments.tournaments
                            .map(session => {
                                return session?.limit
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(limit => limit)
                            .map(limit => limit.limit)

            return [...new Set(limits)]
        },
        tournamentVariantFilters: (state, getters, rootState) => {
            let variants =  rootState.tournaments.tournaments
                            .map(session => {
                                return session?.variant
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(variant => variant)
                            .map(variant => variant.variant)

            return [...new Set(variants)]
        },
        locationFilters: (state, getters, rootState, rootGetters) => {
            let locations =  rootGetters['sessions/sessions']
                            .map(session => {
                                return session?.location
                            })
                            .sort()
                            // .filter(location => location)

            return [...new Set(locations)]
        }
    }
}