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
        sortByDateDesc: (a , b) => {
            let startTimeA = new Date(a.start_time)
            let startTimeB = new Date(b.start_time)
            return startTimeA > startTimeB ? 1 : -1
        },
    },
    getters: {
        filteredSessions: (state, getters, rootState, rootGetters) => {
            return rootGetters['sessions/sessions']
        },
        stakeFilters: (state, getters, rootState, rootGetters) => {
            let stakes =  rootGetters['sessions/sessions']
                            .map(session => {
                                return session?.stake
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(stake => stake)
                            .map(stake => stake.stake)

            return [...new Set(stakes)]
        },
        limitFilters: (state, getters, rootState, rootGetters) => {
            let limits =  rootGetters['sessions/sessions']
                            .map(session => {
                                return session?.limit
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(limit => limit)
                            .map(limit => limit.limit)

            return [...new Set(limits)]
        },
        variantFilters: (state, getters, rootState, rootGetters) => {
            let variants =  rootGetters['sessions/sessions']
                            .map(session => {
                                return session?.variant
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(variant => variant)
                            .map(variant => variant.variant)

            return [...new Set(variants)]
        },
        tableSizeFilters: (state, getters, rootState, rootGetters) => {
            let table_sizes =  rootGetters['sessions/sessions']
                            .map(session => {
                                return session?.table_size
                            })
                            .sort((a, b) => a.id - b.id)
                            .filter(table_size => table_size)
                            .map(table_size => table_size.table_size)

            return [...new Set(table_sizes)]
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