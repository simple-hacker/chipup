export default {
    namespaced: true,
    state: {},
    getters: {
        sessions: (state, getters, rootState) => {
            return [
                ...rootState.cash_games.cash_games,
                ...rootState.tournaments.tournaments
            ]
        }
    },
}