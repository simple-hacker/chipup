export default {
    namespaced: true,
    state: {
        cash_games: [],
    },
    getters: {

    },
    mutations: {
        ASSIGN_CASH_GAMES(state, cash_games) {
            state.cash_games = cash_games
        },
        // ADD_CASH_GAME(state, cash_game) {
        //     state.cash_games.unshift(cash_game)
        // },
        // UPDATE_CASH_GAME(state, cash_game) {
        //     const index = state.cash_games.findIndex(cash_game => cash_game.id == cash_game.id)
        //     state.cash_games.splice(index, 1, cash_game)
        // },
        REMOVE_CASH_GAME(state, cash_game) {
            const index = state.cash_games.indexOf(cash_game)
            state.cash_games.splice(index, 1)
        }
    },
    actions: {
        getCashGames({ commit }) {
            return axios.get('/api/cash/')
            .then(response => {
                commit('ASSIGN_CASH_GAMES', response.data.cash_games)
            })
            .catch(error => {
                throw error
            })
        },
        // addCashGame({ commit }, cash_game) {
        //     return axios.post('/api/cash/create', {
        //         amount: cash_game.amount * 100,
        //         comments: cash_game.comments
        //     })
        //     .then(response => {
        //         commit('ADD_CASH_GAME', response.data.cash_game)
        //     })
        //     .catch(error => {
        //         throw error
        //     })
        // },
        // updateCashGame({ commit }, payload) {
        //     return axios.patch('/api/cash/'+payload.cash_game.id, {
        //         date: payload.data.date.split("T")[0],
        //         amount: payload.data.amount * 100,
        //         comments: payload.data.comments
        //     })
        //     .then(response => {
        //         commit('UPDATE_CASH_GAME', response.data.cash_game)
        //     })
        //     .catch(error => {
        //         throw error
        //     })
        // },
        deleteCashGame({ commit }, cash_game) {
            return axios.delete('/api/cash/'+cash_game.id)
            .then(response => {
                commit('REMOVE_CASH_GAME', cash_game)
            })
            .catch(error => {
                throw error
            })
        }
    }
}