export default {
    namespaced: true,
    state: {
        bankrollTransactions: [],
    },
    getters: {
        deposits: state => {
            return state.bankrollTransactions.filter(bankrollTransaction => bankrollTransaction.locale_amount > 0)
        },
        withdrawals: state => {
            return state.bankrollTransactions.filter(bankrollTransaction => bankrollTransaction.locale_amount <= 0)
        },
        depositsTotal: (state, getters) => {
            return getters.deposits.reduce((total, deposit) => total + deposit.locale_amount, 0)
        },
        withdrawalsTotal: (state, getters) => {
            return getters.withdrawals.reduce((total, withdrawal) => total + withdrawal.locale_amount, 0)
        },
        bankroll: (state, getters, rootState, rootGetters) => {
            // Bankroll is the total number of despoits, subtract total withdrawals, add totalProfit in sessions.js
            // Withdrawals are persisted as negative values in database.  So withdrawalsTotal is sum of all negative which is negative
            return getters.depositsTotal + getters.withdrawalsTotal + rootGetters['sessions/totalProfit']
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
            return axios.get('/api/bankroll')
            .then(response => {
                commit('ASSIGN_BANKROLL_TRANSACTIONS', response.data.bankrollTransactions)
            })
            .catch(error => {
                throw error
            })
        },
        addBankrollTransaction({ commit }, transaction) {
            return axios.post('/api/bankroll', transaction)
            .then(response => {
                commit('ADD_BANKROLL_TRANSACTION', response.data.bankrollTransaction)
            })
            .catch(error => {
                throw error
            })
        },
        updateBankrollTransaction({ commit }, bankrollTransaction) {
            return axios.patch('/api/bankroll/'+bankrollTransaction.id, bankrollTransaction)
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