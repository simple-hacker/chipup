export default {
    namespaced: true,
    state: {
        bankrollTransactions: [],
    },
    getters: {
        deposits: state => {
            return state.bankrollTransactions.filter(bankrollTransaction => bankrollTransaction.amount > 0)
        },
        withdrawals: state => {
            return state.bankrollTransactions.filter(bankrollTransaction => bankrollTransaction.amount <= 0)
        },
        depositsTotal: (state, getters) => {
            return getters.deposits.reduce((total, deposit) => total + deposit.amount, 0) / 100
        },
        withdrawalsTotal: (state, getters) => {
            return getters.withdrawals.reduce((total, withdrawal) => total + withdrawal.amount, 0) / 100
        },
        bankroll: state => {
            return state.bankrollTransactions.reduce((total, transaction) => total + transaction.amount, 0) /100
        }
    },
    mutations: {
        ASSIGN_BANKROLL_TRANSACTIONS(state, transactions) {
            state.bankrollTransactions = transactions
        },
        ADD_BANKROLL_TRANSACTION(state, transaction) {
            state.bankrollTransactions.unshift(transaction)
        },
        UPDATE_BANKROLL_TRANSACTION(state, transaction) {
            const index = state.bankrollTransactions.findIndex(bankrollTransaction => bankrollTransaction.id == transaction.id)
            state.bankrollTransactions.splice(index, 1, transaction)
        },
        REMOVE_BANKROLL_TRANSACTION(state, transaction) {
            const index = state.bankrollTransactions.indexOf(transaction)
            state.bankrollTransactions.splice(index, 1)
        }
    },
    actions: {
        getBankrollTransactions({ commit }) {
            return axios.get('/api/bankroll/')
            .then(response => {
                commit('ASSIGN_BANKROLL_TRANSACTIONS', response.data.bankrollTransactions)
            })
            .catch(error => {
                throw error
            })
        },
        addBankrollTransaction({ commit }, transaction) {
            return axios.post('/api/bankroll/create', {
                amount: transaction.amount * 100,
                comments: transaction.comments
            })
            .then(response => {
                commit('ADD_BANKROLL_TRANSACTION', response.data.bankrollTransaction)
            })
            .catch(error => {
                throw error
            })
        },
        updateBankrollTransaction({ commit }, payload) {
            return axios.patch('/api/bankroll/'+payload.transaction.id, {
                date: payload.data.date.split("T")[0],
                amount: payload.data.amount * 100,
                comments: payload.data.comments
            })
            .then(response => {
                commit('UPDATE_BANKROLL_TRANSACTION', response.data.bankrollTransaction)
            })
            .catch(error => {
                throw error
            })
        },
        deleteBankrollTransaction({ commit }, transaction) {
            return axios.delete('/api/bankroll/'+transaction.id)
            .then(response => {
                commit('REMOVE_BANKROLL_TRANSACTION', transaction)
            })
            .catch(error => {
                throw error
            })
        }
    }
}