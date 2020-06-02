export default {
    namespaced: true,
    state: {
        session: {}
    },
    getters: {
        sessions: (state, getters, rootState) => {
            return [
                ...rootState.cash_games.cash_games,
                ...rootState.tournaments.tournaments
            ].sort(rootState.filters.sortByDate)
        }
    },
    mutations: {
        VIEW_SESSION(state, session) {
            state.session = session
        }
    }
}