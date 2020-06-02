export default {
    namespaced: true,
    state: {},
    getters: {
        sessions: (state, getters, rootState) => {
            let sessions = [
                ...rootState.cash_games.cash_games,
                ...rootState.tournaments.tournaments
            ]
            return sessions.sort(rootState.filters.sortByDate)
        }
    },
}