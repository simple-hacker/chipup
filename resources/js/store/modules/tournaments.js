import moment from 'moment'

export default {
    namespaced: true,
    state: {
        tournaments: [],
        defaultTournament: {
            id: 0,
            game_type: 'tournament',
            location: '',
            name: '',
            limit: {},
            limit_id: 0,
            variant: {},
            variant_id: 0,
            prize_pool: 0,
            position: 0,
            entries: 0,
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
        getTournamentById: state => id => {
            let tournament = state.tournaments.find(tournament => tournament.id === id)
            return tournament ?? state.defaultTournament
        },
        tournamentProfit: state => {
            return state.tournaments.reduce((total, session) => total + session.locale_profit, 0)
        },
        tournamentsProfitSeries: (state) => {
            return [...state.tournaments]
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
        ASSIGN_TOURNAMENTS(state, tournaments) {
            state.tournaments = tournaments
        },
        VIEW_TOURNAMENT(state, id) {
            const index = state.tournaments.findIndex(t => t.id === id)
            state.session = state.tournaments[index]
        },
        ADD_TOURNAMENT(state, tournament) {
            state.tournaments.unshift(tournament)
        },
        UPDATE_TOURNAMENT(state, tournament) {
            const index = state.tournaments.findIndex(t => t.id === tournament.id)
            state.tournaments.splice(index, 1, tournament)
        },
        REMOVE_TOURNAMENT(state, tournament) {
            const index = state.tournaments.findIndex(t => t.id === tournament.id)
            state.tournaments.splice(index, 1)
        }
    },
    actions: {
        getTournaments({ commit }) {
            return axios.get('/api/tournament')
            .then(response => {
                commit('ASSIGN_TOURNAMENTS', response.data.tournaments)
            })
            .catch(error => {
                throw error
            })
        },
        addTournament({ commit, dispatch, rootGetters }, tournament) {
            return axios.post('/api/tournament', {
                ...tournament,
            })
            .then(response => {
                let resetFilters = ! rootGetters['filtered_sessions/filtersApplied']
                
                commit('ADD_TOURNAMENT', response.data.tournament)

                if (resetFilters)
                    dispatch({ type: 'filtered_sessions/resetFilters' }, { root: true })
            })
            .catch(error => {
                throw error
            })
        },
        updateTournament({ commit, dispatch, rootGetters }, tournament) {
            return axios.patch('/api/tournament/'+tournament.id, {
                ...tournament,
            })
            .then(response => {
                let resetFilters = ! rootGetters['filtered_sessions/filtersApplied']

                commit('UPDATE_TOURNAMENT', response.data.tournament)

                if (resetFilters)
                    dispatch({ type: 'filtered_sessions/resetFilters' }, { root: true })
            })
            .catch(error => {
                throw error
            })
        },
        destroyTournament({ commit, dispatch, rootGetters }, tournament) {
            return axios.delete('/api/tournament/'+tournament.id)
            .then(response => {
                let resetFilters = ! rootGetters['filtered_sessions/filtersApplied']

                commit('REMOVE_TOURNAMENT', tournament)

                if (resetFilters)
                    dispatch({ type: 'filtered_sessions/resetFilters' }, { root: true })
            })
            .catch(error => {
                throw error
            })
        }
    }
}