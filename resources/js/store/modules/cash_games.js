import moment from 'moment'

export default {
    namespaced: true,
    state: {
        cash_games: [],
        view_cash_game: {},
    },
    getters: {
        getCashGameById: (state) => (id) => {
            return state.cash_games.find(cash_game => cash_game.id === id)
        }
    },
    mutations: {
        ASSIGN_CASH_GAMES(state, cash_games) {
            state.cash_games = cash_games
        },
        VIEW_CASH_GAME(state, id) {
            const index = state.cash_games.findIndex(cg => cg.id === id)
            state.view_cash_game = state.cash_games[index]
        },
        ADD_CASH_GAME(state, cash_game) {
            state.cash_games.unshift(cash_game)
        },
        UPDATE_CASH_GAME(state, cash_game) {
            const index = state.cash_games.findIndex(cg => cg.id === cash_game.id)
            state.cash_games.splice(index, 1, cash_game)
        },
        REMOVE_CASH_GAME(state, cash_game) {
            const index = state.cash_games.findIndex(cg => cg.id === cash_game.id)
            state.cash_games.splice(index, 1)
        }
    },
    actions: {
        viewCashGame({ commit }, cash_game_id) {
            commit('VIEW_CASH_GAME',  cash_game_id)
        },
        getCashGames({ commit }) {
            return axios.get('/api/cash/')
            .then(response => {
                commit('ASSIGN_CASH_GAMES', response.data.cash_games)
            })
            .catch(error => {
                throw error
            })
        },
        addCashGame({ commit }, cash_game) {
            return axios.post('/api/cash/', {
                ...cash_game,
				start_time: moment(cash_game.start_time).format("YYYY-MM-DD HH:mm:ss"),
				end_time: moment(cash_game.end_time).format("YYYY-MM-DD HH:mm:ss")
            })
            .then(response => {
                commit('ADD_CASH_GAME', response.data.cash_game)
            })
            .catch(error => {
                console.timeEnd()
                console.log('error', error)
                throw error
            })
        },
        updateCashGame({ commit }, cash_game) {
            return axios.patch('/api/cash/'+cash_game.id, {
                ...cash_game,
				start_time: moment(cash_game.start_time).format("YYYY-MM-DD HH:mm:ss"),
				end_time: moment(cash_game.end_time).format("YYYY-MM-DD HH:mm:ss")
            })
            .then(response => {
                commit('UPDATE_CASH_GAME', response.data.cash_game)
            })
            .catch(error => {
                throw error
            })
        },
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