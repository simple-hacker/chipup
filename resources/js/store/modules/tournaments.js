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
            cash_out_model: {
                amount: 0
            },
            expenses: [],
        }
    },
    getters: {
        getTournamentById: (state) => (id) => {
            let tournament = state.tournaments.find(tournament => tournament.id === id)
            return tournament ?? state.defaultTournament
        }
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
        addTournament({ commit }, tournament) {
            return axios.post('/api/tournament', {
                ...tournament,
            })
            .then(response => {
                commit('ADD_TOURNAMENT', response.data.tournament)
            })
            .catch(error => {
                throw error
            })
        },
        updateTournament({ commit }, tournament) {
            return axios.patch('/api/tournament/'+tournament.id, {
                ...tournament,
            })
            .then(response => {
                commit('UPDATE_TOURNAMENT', response.data.tournament)
            })
            .catch(error => {
                throw error
            })
        },
        destroyTournament({ commit }, tournament) {
            return axios.delete('/api/tournament/'+tournament.id)
            .then(response => {
                commit('REMOVE_TOURNAMENT', tournament)          
            })
            .catch(error => {
                throw error
            })
        }
    }
}