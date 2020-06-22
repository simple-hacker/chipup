<template>
    <div
        class="w-full flex flex-col md:flex-row items-center"
        :class="{'error-input': error}"
    >
        <div class="w-full md:w-2/5 md:mr-1 mb-3 md:mb-0">
            <v-select
                class="w-full text-white"
                v-model="transactionCurrency"
                :options="currencies"
                :clearable="false"
                @input="$emit('clear-error')"
            >
                <template slot="selected-option" slot-scope="currency">
                    <div class="flex items-center">
                        <div class="currency-flag mr-3" :class="`currency-flag-${currency.label.toLowerCase()}`"></div>
                        <div class="mr-3" v-text="currency.label"></div>
                    </div>
                </template>
                <template v-slot:option="currency">
                    <div class="flex items-center">
                        <div class="currency-flag mr-3" :class="`currency-flag-${currency.label.toLowerCase()}`"></div>
                        <div class="mr-3" v-text="currency.label"></div>
                    </div>
                </template>
            </v-select>
        </div>
        <div class="w-full md:w-3/5 md:ml-1">
            <currency-input
                v-model="transactionAmount"
                class="w-full text-2xl border-r-4"
                :class="border"
                :currency="transactionCurrency"
                :locale="locale"
                :distraction-free="false"
                :allow-negative="allowNegative"
                @input="$emit('clear-error')"
            />
        </div>
    </div>
</template>

<script>
import { currencies } from '@/currencies'

import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import 'currency-flags/dist/currency-flags.css'

import { CurrencyInput } from 'vue-currency-input'

export default {
    name: 'TransactionAmount',
    components: { vSelect, CurrencyInput },
    props: {
        currency: {
            type: String,
            required: true
        },
        amount: {
            required: true,
            default: 0,
        },
        allowNegative: {
            type: Boolean,
            default: false,
        },
        border: {
            type: String,
            default: 'border-gray-400'
        },
        error: {
            default: false
        },
    },
    data() {
        return {
            currencies,
        }
    },
    computed: {
        locale() {
            return this.$store.state.user.locale
        },
        hasError: {
            get: function () {
                return this.error
            },
            set: function (value) {
                this.hasError = value
            },
        },
        transactionCurrency: {
            get() { return this.currency },
            set(currency) {this.$emit('update-currency', currency)}
        },
        transactionAmount: {
            get() { return this.amount },
            set(amount) {this.$emit('update-amount', amount)}
        }
    },
}
</script>

<style lang="scss">
    .v-select .vs__search::placeholder,
    .v-select .vs__dropdown-toggle {
        @apply w-full bg-gray-500 text-white rounded shadow p-2 text-lg;
    }

    .v-select .vs__dropdown-menu {
        @apply p-1 rounded bg-gray-500;
    }

    .v-select .vs__dropdown-option,
    .v-select .vs__selected-option {
        @apply text-white rounded p-3;
        &:hover {
            @apply bg-gray-450;
        }
    }

    .v-select .vs__dropdown-option--selected,
    .v-select .vs__dropdown-option--highlight {
        @apply bg-gray-400;
    }

    .v-select .vs__selected {
        @apply text-white;
    }

    .vs__clear,
    .vs__open-indicator {
        color: #FFFFFF;
    }
</style>