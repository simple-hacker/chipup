import moment from 'moment'

export default {
    namespaced: true,
    state: {
        cash_games: [],
        defaultCashGame: {
            id: 0,
            game_type: 'cash_game',
            location: '',
            stake_id: 0,
            limit: {},
            limit_id: 0,
            variant: {},
            variant_id: 0,
            table_size: {},
            table_size_id: 0,
            start_time: moment.utc().format(),
            end_time: moment.utc().format(),
            comments: '',
            buy_ins: [],
            cash_out: {
                amount: 0
            },
            expenses: [],
        }
    },
    getters: {
        getCashGameById: (state) => (id) => {
            let cash_game = state.cash_games.find(cash_game => cash_game.id === id)
            return cash_game ?? state.defaultCashGame
        },
        cashGameProfit: state => {
            return state.cash_games.reduce((total, session) => total + session.locale_profit, 0)
        },
        cashGamesProfitSeries: (state) => {
            return [...state.cash_games]
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
    },
    mutations: {
        ASSIGN_CASH_GAMES(state, cash_games) {
            state.cash_games = cash_games
        },
        VIEW_CASH_GAME(state, id) {
            const index = state.cash_games.findIndex(cg => cg.id === id)
            state.session = state.cash_games[index]
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
        getCashGames({ commit }) {
            return axios.get('/api/cash')
            .then(response => {
                commit('ASSIGN_CASH_GAMES', response.data.cash_games)
            })
            .catch(error => { throw error })
        },
        addCashGame({ commit, dispatch, rootGetters }, cash_game) {
            return axios.post('/api/cash', cash_game)
            .then(response => {
                let resetFilters = ! rootGetters['filtered_sessions/filtersApplied']
                
                commit('ADD_CASH_GAME', response.data.cash_game)

                if (resetFilters)
                    dispatch({ type: 'filtered_sessions/resetFilters' }, { root: true })
            })
            .catch(error => { throw error })
        },
        updateCashGame({ commit, dispatch, rootGetters }, cash_game) {
            return axios.patch('/api/cash/'+cash_game.id, cash_game)
            .then(response => {
                let resetFilters = ! rootGetters['filtered_sessions/filtersApplied']

                commit('UPDATE_CASH_GAME', response.data.cash_game)

                if (resetFilters)
                    dispatch({ type: 'filtered_sessions/resetFilters' }, { root: true })
            })
            .catch(error => { throw error })
        },
        destroyCashGame({ commit, dispatch, rootGetters }, cash_game) {
            return axios.delete('/api/cash/'+cash_game.id)
            .then(response => {
                let resetFilters = ! rootGetters['filtered_sessions/filtersApplied']

                commit('REMOVE_CASH_GAME', cash_game)

                if (resetFilters)
                    dispatch({ type: 'filtered_sessions/resetFilters' }, { root: true })
            })
            .catch(error => { throw error })
        }
    }
}