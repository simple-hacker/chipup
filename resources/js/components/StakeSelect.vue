<template>
    <div class="w-full">
        <div
            v-if="!createStake"
            class="flex"
        >
            <v-select
                v-model="chosenStake"
                class="w-full bg-gray-450"
                :options="stakes"
                :clearable="false"
                :getOptionLabel="opt => stakeLabel(opt, currency, locale)"
                @input="stakeChanged"
            >
                <template v-slot:option="stake">
                    <div class="flex items-center" v-text="stakeLabel(stake, currency, locale)"></div>
                </template>
            </v-select>
            <button
                @click="createStake = true"
                class="btn btn-gray ml-1 border-r-4 border-green-500"
                title="Add new stake"
            >
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div
            v-if="createStake"
        >
            <div class="w-full">
                <div class="mb-2">
                    <h2 class="text-sm uppercase text-gray-200 font-extrabold tracking-wider mb-1">Small Blind</h2>
                    <currency-input
                        v-model="newStake.small_blind"
                        class="p-1 md:p-2 text-lg bg-gray-450"
                        :class="{'error-input' : errors.small_blind}"
                        placeholder="Small Blind"
                        :locale="locale"
                        :currency="currency"
                        :distraction-free="false"
                        :allow-negative="false"
                        @input="delete errors.small_blind"
                    />
                    <span v-if="errors.small_blind" class="error-message">{{ errors.small_blind[0] }}</span>
                </div>
                <div class="mb-2">
                    <h2 class="text-sm uppercase text-gray-200 font-extrabold tracking-wider mb-1">Big Blind</h2>
                    <currency-input
                        v-model="newStake.big_blind"
                        class="p-1 md:p-2 text-lg bg-gray-450"
                        :class="{'error-input' : errors.big_blind}"
                        placeholder="Big Blind"
                        :locale="locale"
                        :currency="currency"
                        :distraction-free="false"
                        :allow-negative="false"
                        @input="delete errors.big_blind"
                    />
                    <span v-if="errors.big_blind" class="error-message">{{ errors.big_blind[0] }}</span>
                </div>
                <div class="mb-2">
                    <h2 class="text-sm uppercase text-gray-200 font-extrabold tracking-wider mb-1">Straddle</h2>
                    <currency-input
                        v-model="newStake.straddle_1"
                        class="p-1 md:p-2 text-lg bg-gray-450"
                        :class="{'error-input' : errors.straddle_1}"
                        placeholder="Straddle"
                        :locale="locale"
                        :currency="currency"
                        :distraction-free="false"
                        :allow-negative="false"
                        @input="delete errors.straddle_1"
                    />
                    <span v-if="errors.straddle_1" class="error-message">{{ errors.straddle_1[0] }}</span>
                </div>
            </div>
            <div class="w-full flex justify-between mt-4">
                <button
                    @click="cancelNewStake"
                    class="btn btn-gray border-r-4 border-red-500"
                    title="Add new stake"
                >
                    <i class="fas fa-undo mr-2"></i>
                    <span>Cancel</span>
                </button>
                <button
                    @click="addNewStake"
                    class="btn btn-gray border-r-4 border-green-500"
                    title="Add new stake"
                >
                    <i class="fas fa-check mr-2"></i>
                    <span>Add Stake</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import vSelect from 'vue-select'
import { CurrencyInput } from 'vue-currency-input'

import stakeMixin from '@/mixins/stake'

import { mapState, mapGetters } from 'vuex'

export default {
    name: 'StakeSelect',
    components: { vSelect, CurrencyInput },
    mixins: [stakeMixin],
    props: {
        currency: {
            type: String,
            default: 'GBP'
        },
        locale: {
            type: String,
            default: 'en-GB'
        },
        stake_id: {
            type: Number,
            default: 1,
        }
    },
    data() {
        return {
            chosenStake: {},
            createStake: false,
            newStake: {},
            errors: {}
        }
    },
    computed: {
        ...mapState(['stakes']),
        ...mapGetters(['getStake']),
        defaultNewStake() {
            return {
                small_blind: null,
                big_blind: null,
                straddle_1: null,
                straddle_2: null,
                straddle_3: null,
            }
        }
    },
    created() {
        this.newStake = this.defaultNewStake
        this.chosenStake = this.getStake(this.stake_id) ?? this.stakes[0]
    },
    methods: {
        stakeChanged() {
            this.$emit('stake-changed', this.chosenStake)
        },
        addNewStake() {
            axios.post(`api/stake`, this.newStake)
            .then(response => {
                // The stake was returned so we have its id
                this.chosenStake = response.data.stake
                this.$emit('stake-changed', this.chosenStake)
                // Repopulate store state stakes with response of all default stakes and user stakes
                this.$store.dispatch('repopulateStakes', response.data.stakes)
                this.createStake = false
            })
            .catch(error => {
				this.errors = error.response.data.errors
            })
        },
        cancelNewStake() {
            this.$emit('stake-changed', this.stakes[0])
            this.createStake = false
            this.newStake = this.defaultNewStake
        }
    },
}
</script>

<style lang="scss">
    .v-select .vs__search::placeholder,
    .v-select .vs__dropdown-toggle {
        @apply w-full bg-gray-450 text-white rounded shadow p-2;
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

    .v-select .vs__clear,
    .v-select .vs__actions,
    .v-select .vs__open-indicator {
        color: #FFFFFF;
    }
</style>