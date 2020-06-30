<template>
    <div class="flex flex-col w-full xxl:w-3/5 xxl:mx-auto card text-white border-b-8 border-green-500 mt-3 p-0">
        <form-wizard ref="setupWizard" @on-complete="completeSetup" title="" subtitle="" finishButtonText="Complete Setup" color="#00AD71" errorColor="#F45757" class="text-white">
            <tab-content title="Bankroll" icon="fas fa-dollar-sign" :beforeChange="bankrollMustBePositive">
                <div class="flex flex-col min-h-56">
                    <div class="mb-3">
                        <p class="tracking-wide text-lg md:text-xl mb-2">Which currency would you like to use?</p>
                        <div class="flex flex-col items-center">
                            <div class="w-full md:w-3/4">
                                <locale-select @locale-selected="changeLocale"></locale-select>
                            </div>
                            <span v-if="errors.currency" class="error-message">{{ errors.currency[0] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p class="tracking-wide text-lg md:text-xl mb-2">What is your starting bankroll?</p>
                        <div class="flex flex-col items-center">
                            <currency-input
                                v-model="setup.bankroll"
                                class="w-full md:w-3/4 p-3 text-lg"
                                :class="{ 'error-input' : errors.bankroll }"
                                :currency="setup.currency"
                                :locale="setup.locale"
                                :distraction-free="false"
                                :allow-negative="false"
                            />
                            <span v-if="errors.bankroll" class="error-message">{{ errors.bankroll[0] }}</span>
                        </div>
                    </div>
                </div>
            </tab-content>
            <tab-content title="Stake" icon="fas fa-coins">
                <div class="flex flex-col min-h-56">
                    <p class="tracking-wide text-lg md:text-xl mb-2">What stakes do you usually play?</p>
                    <div class="flex flex-col items-center">
                        <div class="w-full md:w-3/4 mb-2">
                            <stake-select @stake-changed="changeStake" :currency="setup.currency" :locale="setup.locale"/>
                            <span v-if="errors.default_stake_id" class="error-message">{{ errors.default_stake_id[0] }}</span>
                        </div>
                    </div>
                </div>
            </tab-content>
            <tab-content title="Game Type" icon="fas fa-star">
                <div class="flex flex-col min-h-56">
                    <p class="tracking-wide text-lg md:text-xl mb-2">What game type do you usually play?</p>
                    <div class="flex flex-col items-center">
                        <select
                            v-model="setup.default_limit_id"
                            class="w-full md:w-3/4 p-3 mb-2"
                            :class="{ 'error-input' : errors.default_limit_id }"
                        >
                            <option
                                v-for="limit in limits"
                                :key="limit.id"
                                :value="limit.id"
                                v-text="limit.limit"
                            >
                            </option>
                        </select>
                        <span v-if="errors.default_limit_id" class="error-message">{{ errors.default_limit_id[0] }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <select
                            v-model="setup.default_variant_id"
                            class="w-full md:w-3/4 p-3 mb-2"
                            :class="{ 'error-input' : errors.default_variant_id }"
                        >
                            <option
                                v-for="variant in variants"
                                :key="variant.id"
                                :value="variant.id"
                                v-text="variant.variant"
                            >
                            </option>
                        </select>
                        <span v-if="errors.default_variant_id" class="error-message">{{ errors.default_variant_id[0] }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <select
                            v-model="setup.default_table_size_id"
                            class="w-full md:w-3/4 p-3 mb-2"
                            :class="{ 'error-input' : errors.default_table_size_id }"
                        >
                            <option
                                v-for="table_size in table_sizes"
                                :key="table_size.id"
                                :value="table_size.id"
                                v-text="table_size.table_size"
                            >
                            </option>
                        </select>
                        <span v-if="errors.default_table_size_id" class="error-message">{{ errors.default_table_size_id[0] }}</span>
                    </div>
                </div>
            </tab-content>
            <tab-content title="Location" icon="fas fa-map-marker-alt">
                <div class="flex flex-col min-h-56">
                    <p class="tracking-wide text-lg md:text-xl mb-2">Where do you usually play?</p>
                    <div class="flex flex-col items-center">
                        <input
                            v-model="setup.default_location"
                            type="text"
                            placeholder="Enter venue"
                            class="w-full md:w-3/4 p-3 mb-2"
                            :class="{ 'error-input' : errors.default_location }"
                        />
                        <span v-if="errors.default_location" class="error-message">{{ errors.default_location[0] }}</span>
                    </div>
                </div>
            </tab-content>
        </form-wizard>

        <vue-snotify/>

    </div>
</template>

<script>
import LocaleSelect from '@components/LocaleSelect'
import StakeSelect from '@components/StakeSelect'

import { CurrencyInput } from 'vue-currency-input'

export default {
    name: 'PokerSetup',
    components: { LocaleSelect, StakeSelect, CurrencyInput },
    props: {
        stakes: Array,
        limits: Array,
        variants: Array,
        table_sizes: Array,
    },
    data() {
        return {
            setup: {
                bankroll: 0,
                locale: 'en-GB',
                currency: 'GBP',
                default_location: '',
                default_stake_id: 1,
                default_limit_id: 1,
                default_variant_id: 1,
                default_table_size_id: 1,
            },
            errors: {},
        }
    },
    computed: {
        variables() {
            return {
                stakes: this.stakes,
                limits: this.limits,
                variants: this.variants,
                table_sizes: this.table_sizes,
            }
        }
    },
    created() {
        this.$store.dispatch('populateState', { user: this.user, variables: this.variables })
    },
    methods: {
        bankrollMustBePositive() {
            if (this.bankroll < 0) {
                this.errors = Object.assign({}, {bankroll: ['Bankroll amount must be zero or greater']})
                return false
            }
            
            return true
        },
        changeLocale(locale) {
            this.$i18n.locale = locale.code
            this.setup.locale = locale.code
            this.setup.currency = locale.currency.currency
        },
        changeStake(stake) {
            this.setup.default_stake_id = stake.id
        },
        completeSetup: function(){
            axios.post('/setup', this.setup)
            .then(response => {
                if (response.status === 200) {
                    window.location = response.data.redirect
                }
            })
            .catch(error => {
                console.log(error)
                this.errors = error.response.data.errors
                this.$snotify.error(error.response.data.message)
                // Change tab index to first error.
                if (this.errors.bankroll) {
                    this.$refs.setupWizard.changeTab(this.$refs.setupWizard.activeTabIndex, 0)
                } else if (this.errors.default_stake_id) {
                    this.$refs.setupWizard.changeTab(this.$refs.setupWizard.activeTabIndex, 1)
                } else if (this.errors.default_limit_id || this.errors.default_variant_id || this.errors.default_table_size_id) {
                    this.$refs.setupWizard.changeTab(this.$refs.setupWizard.activeTabIndex, 2)
                }
            });
        },
    },
}
</script>

<style lang="scss">
    .vue-form-wizard .wizard-icon-circle {
        background-color: #38393D;
    }

    .vue-form-wizard .wizard-nav-pills > li > a {
        color: white;

        &:hover {
            color: lighten(#505155, 20%);
        }
    }
</style>
