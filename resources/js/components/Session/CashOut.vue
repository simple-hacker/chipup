<template>
      <div class="flex flex-col">
        <div class="text-2xl capitalize mb-5 border-b border-muted-light">
            Cash Out
        </div>
        <div class="flex flex-col">
            <p class="text-base md:text-lg mb-2">How much did you cash out?</p>
            <input
                v-model="cashOut.amount"
                type="number"
                min=0
                :class="{'error-input' : errors.amount}"
                @input="delete errors.amount"
            >
            <span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>
        </div>
        <div
            class="flex flex-col mt-3"
        >
            <p class="text-base md:text-lg mb-2">When did you finish?</p>
            <datetime
                v-model="cashOut.end_time"
                input-id="end_time"
                type="datetime"
                :minute-step="5"
                :flow="['time']"
                placeholder="End At"
                title="End Live Session At"
                auto
                class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
                :input-class="{'error-input' : errors.end_time, 'p-3' : true}"
                @input="delete errors.end_time"	
            >
            </datetime>
            <span v-if="errors.end_time" class="error-message">{{ errors.end_time[0] }}</span>
        </div>
        <div class="flex mt-4">
            <button
                @click.prevent="endSessionAndCashOut"
                type="button"
                class="w-full bg-red-600 border border-red-700 hover:bg-red-700 rounded p-4 uppercase text-white font-bold text-center ml-1"
            >
                Cash Out
            </button>				
        </div>
    </div>
</template>

<script>
import moment from 'moment'
import { mapActions } from 'vuex'

export default {
    name: 'CashOut',
    props: ['buyInTotal'],
    data() {
        return {
            cashOut: {
                end_time: moment().format(),
                amount: 0
            },
            errors: {}
        }
    },
    methods: {
        ...mapActions('live', ['endLiveSession']),
		endSessionAndCashOut() {
			this.endLiveSession(this.cashOut)
			.then(response => {
                this.$emit('close')
                this.$router.push('sessions')               

                if ((this.cashOut.amount - this.buyInTotal) > 0) {
                    this.$snotify.success(`Nice win!`)
                } else {
                    this.$snotify.warning(`Better luck next time.`)
                }
			})
			.catch(error => {
				this.$snotify.error('Error: '+error.response.data.message)
				this.errors = error.response.data.errors
			})
		},
    }
}
</script>

<style>

</style>