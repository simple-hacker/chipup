// vuex
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
    strict: true,
    state: {
        user: {
            email: 'example@email.com'
        },
        // bankroll: 8575,
        bankrollTransactions: []
    },
    getters: {
        deposits: state => {
            return state.bankrollTransactions.filter(bankrollTransaction => bankrollTransaction.amount > 0)
        },
        withdrawals: state => {
            return state.bankrollTransactions.filter(bankrollTransaction => bankrollTransaction.amount <= 0)
        },
        depositsTotal: (state, getters) => {
            return getters.deposits.reduce((total, deposit) => total + deposit.amount, 0)
        },
        withdrawalsTotal: (state, getters) => {
            return getters.withdrawals.reduce((total, withdrawal) => total + withdrawal.amount, 0)
        },
        bankroll: state => {
            return state.bankrollTransactions.reduce((total, transaction) => total + transaction.amount, 0)
        }
    },
    mutations: {
        ADD_BANKROLL_TRANSACTION (state, transaction) {
            state.bankrollTransactions.unshift(transaction)
        }
    },
    actions: {
        addBankrollTransaction({ commit }, transaction) {
            axios.post('/api/bankroll/create', {
                amount: transaction.amount,
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
})

export default store