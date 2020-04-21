// vuex
import Vue from 'vue'
import Vuex from 'vuex'

import bankroll from '@modules/bankroll'

Vue.use(Vuex)

const store = new Vuex.Store({
    strict: true,
    modules: {
        bankroll
    },
    state: {
        user: {
            email: 'example@email.com'
        }
    },
    
})

export default store