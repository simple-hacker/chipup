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
        ASSIGN_BANKROLL_TRANSACTIONS (state, transactions) {
            state.bankrollTransactions = transactions
        },
        ADD_BANKROLL_TRANSACTION (state, transaction) {
            state.bankrollTransactions.unshift(transaction)
        }
    },
    actions: {
        getBankrollTransactions({ commit}) {
            axios.get('/api/bankroll/')
            .then((response) => {
                commit('ASSIGN_BANKROLL_TRANSACTIONS', response.data.bankrollTransactions)
            })
            .catch((err) => {
                console.log('There was an error retrieving the user\'s bankroll transactions')
            })
        },
        addBankrollTransaction({ commit }, transaction) {
            axios.post('/api/bankroll/create', {
                amount: transaction.amount * 100,
                comments: transaction.comments
            })
            .then((response) => {
                commit('ADD_BANKROLL_TRANSACTION', response.data.bankrollTransaction)
            })
            .catch((err) => {
                console.log('There was an error adding the bankroll transaction.')
            })
        }
    }
}