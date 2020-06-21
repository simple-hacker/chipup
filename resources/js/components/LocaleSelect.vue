<template>
    <v-select
        class="w-full text-white"
        v-model="locale"
        :options="locales"
        :clearable="false"
        :getOptionLabel="opt => opt.currency.currency"
        @input="$emit('locale-selected', locale)"
    >
        <template slot="selected-option" slot-scope="locale">
            <div class="flex items-center">
                <div class="currency-flag mr-3" :class="`currency-flag-${locale.currency.currency.toLowerCase()}`"></div>
                <div class="mr-3" v-text="locale.currency.currency"></div>
            </div>
        </template>
        <template v-slot:option="locale">
            <div class="flex items-center">
                <div class="currency-flag mr-3" :class="`currency-flag-${locale.currency.currency.toLowerCase()}`"></div>
                <div class="mr-3" v-text="locale.currency.currency"></div>
                <div v-html="new Intl.NumberFormat(locale.code, locale.currency).format(12345.67)"></div>
            </div>
        </template>
    </v-select>
</template>

<script>
import { locales, currencies } from '@/currencies'

import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import 'currency-flags/dist/currency-flags.css'

export default {
    name: 'LocaleSelect',
    components: { vSelect },
    data() {
        return {
            locales,
            currencies,
            locale: {
                code: 'en-GB',
                currency: {
                    style: 'currency', currency: 'GBP'
                }
            }
        }
    },
}
</script>

<style lang="scss">
    .v-select .vs__search::placeholder,
    .v-select .vs__dropdown-toggle {
        @apply w-full bg-gray-500 text-white rounded shadow p-2;
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