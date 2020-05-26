// Currently the GameTransactionController returns a fresh copy of the Game (as well as all it's transactions)
// so we just replace the entire Game in the store even though we've only updated one of it's transactions.
// This is another DB call we can remove in the future.
// This is because we use Game.profit but this value isn't returned when we update a transaction.
// TODO: Calculate and display profit client side by adding/subtracting all of it's transaction amounts
// and then when updating/creating a transaction we can replace just the transaction in the store.

export default {
    namespaced: true,
    state: {
    },
    mutations: {
    },
    actions: {
        createTransaction({ commit }, payload) {
            return axios.post(`/api/${payload.transactionType}`, {
                ...payload.transaction,
                game_id: payload.game_id,
                game_type: payload.game_type
            })
            .then(response => {
                commit('cash_games/UPDATE_CASH_GAME', response.data.game, { root: true})
            })
            .catch(error => {
                throw error
            })
        },
        updateTransaction({ commit }, payload) {
            return axios.patch(`/api/${payload.transactionType}/${payload.transaction.id}`, payload.transaction)
            .then(response => {
                commit('cash_games/UPDATE_CASH_GAME', response.data.game, { root: true})
            })
            .catch(error => {
                throw error
            })
        },
        deleteTransaction({ commit }, payload) {
            return axios.delete(`/api/${payload.transactionType}/${payload.transaction.id}`)
            .then(response => {
                commit('cash_games/UPDATE_CASH_GAME', response.data.game, { root: true})     
            })
            .catch(error => {
                throw error
            })
        }
    }
}