import moment from 'moment'

export default {
    namespaced: true,
    state: {
        tournaments: [],
    },
    getters: {
        getTournamentById: (state) => (id) => {
            return state.tournaments.find(tournament => tournament.id === id)
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
        viewTournament({ commit, getters }, tournament_id) {
            // commit('VIEW_TOURNAMENT',  tournament_id)
            let tournament = getters.getTournamentById(tournament_id)
            commit('sessions/VIEW_SESSION', tournament, {root: true})
        },
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
        deleteTournament({ commit }, tournament) {
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