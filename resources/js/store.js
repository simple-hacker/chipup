// vuex
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        user: {
            email: 'example@email.com'
        },
        bankroll: 8575,
        bankrollTransactions: [
            {
                id: 1,
                date: 'Today',
                amount: -300000
            },
            {
                id: 2,
                date: 'Yesterday',
                amount: 75.85
            },
            {
                id: 3,
                date: 'Monday 25th March',
                amount: -60.00
            },
            {
                id: 4,
                date: 'Saturday 23rd March',
                amount: 212
            },
            {
                id: 5,
                date: 'Friday 22nd March',
                amount: 118
            },
            {
                id: 6,
                date: 'Thursday 21st March',
                amount: 45
            },
            {
                id: 7,
                date: 'Sunday 16th March',
                amount: -214
            },
            {
                id: 8,
                date: 'Saturday 15th March',
                amount: -200
            },
            {
                id: 9,
                date: 'Friday 14th March',
                amount: 415
            },
            {
                id: 10,
                date: 'Sunday 9th March',
                amount: 85
            },
            {
                id: 11,
                date: 'Saturday 8th March',
                amount: 725
            },
        ]
    },
})

export default store