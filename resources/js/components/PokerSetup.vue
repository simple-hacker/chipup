<template>
    <form-wizard ref="formwizard" title="" subtitle="" color="#409858">
        <tab-content title="Bankroll" icon="fas fa-dollar-sign">
            <div class="flex flex-col">
                <p class="text-lg md:text-2xl text-gray-700 mb-5">What is your starting bankroll?</p>
                <div class="flex flex-col items-center">
                    <input v-model="bankroll" type="number" step="0.01" min="0" class="w-full md:w-3/4 border p-3 bg-white" :class="errors.bankroll ? 'border-red-700' : 'border-gray-400'"/>
                    <span v-if="errors.bankroll" class="error">{{ errors.bankroll[0] }}</span>
                </div>
            </div>
        </tab-content>
        <tab-content title="Stake" icon="fas fa-coins">
            <div class="flex flex-col">
                <p class="text-lg md:text-2xl text-gray-700 mb-5">What stakes do you usually play?</p>
                <div class="flex flex-col items-center">
                    <select v-model="default_stake" class="w-full md:w-3/4 bg-white border p-3 mb-2" :class="errors.default_stake_id ? 'border-red-700' : 'border-gray-400'">
                        <option v-for="stake in stakes" :key="stake.id" :value="stake.id">{{ stake.stake }}</option>
                    </select>
                    <span v-if="errors.default_stake_id" class="error">{{ errors.default_stake_id[0] }}</span>
                </div>
            </div>
        </tab-content>
        <tab-content title="Game Type" icon="fas fa-star">
            <div class="flex flex-col">
                <p class="text-lg md:text-2xl text-gray-700 mb-5">What game type do you usually play?</p>
                <div class="flex flex-col items-center">
                    <select v-model="default_limit" class="w-full md:w-3/4 bg-white border p-3 mb-2" :class="errors.default_limit_id ? 'border-red-700' : 'border-gray-400'">
                        <option v-for="limit in limits" :key="limit.id" :value="limit.id">{{ limit.limit }}</option>
                    </select>
                    <span v-if="errors.default_limit_id" class="error">{{ errors.default_limit_id[0] }}</span>
                </div>
                <div class="flex flex-col items-center">
                    <select v-model="default_variant" class="w-full md:w-3/4 bg-white border p-3 mb-2" :class="errors.default_variant_id ? 'border-red-700' : 'border-gray-400'">
                        <option v-for="variant in variants" :key="variant.id" :value="variant.id">{{ variant.variant }}</option>
                    </select>
                    <span v-if="errors.default_variant_id" class="error">{{ errors.default_variant_id[0] }}</span>
                </div>
                <div class="flex flex-col items-center">
                    <select v-model="default_table_size" class="w-full md:w-3/4 bg-white border p-3 mb-2" :class="errors.default_table_size_id ? 'border-red-700' : 'border-gray-400'">
                        <option v-for="table_size in table_sizes" :key="table_size.id" :value="table_size.id">{{ table_size.table_size }}</option>
                    </select>
                    <span v-if="errors.default_table_size_id" class="error">{{ errors.default_table_size_id[0] }}</span>
                </div>
            </div>
        </tab-content>
        <tab-content title="Location" icon="fas fa-map-marker-alt">
            <div class="flex flex-col">
                <p class="text-lg md:text-2xl text-gray-700 mb-5">Where do you usually play?</p>
                <div class="flex flex-col items-center">
                    <input v-model="location" type="text" placeholder="Enter location" class="w-full md:w-3/4 border p-3 mb-2 bg-white" :class="errors.default_location ? 'border-red-700' : 'border-gray-400'"/>
                    <span v-if="errors.default_location" class="error">{{ errors.default_location[0] }}</span>
                </div>
            </div>
        </tab-content>

        <template slot="footer" slot-scope="props">
            <div class="wizard-footer-left">
                <wizard-button  v-if="props.activeTabIndex > 0" @click.native="props.prevTab()" :style="props.fillButtonStyle">Previous</wizard-button>
            </div>
            <div class="wizard-footer-right">
                <wizard-button v-if="!props.isLastStep" @click.native="props.nextTab()" class="wizard-footer-right" :style="props.fillButtonStyle">Next</wizard-button>

                <wizard-button v-else @click.native="completeSetup" class="wizard-footer-right finish-button" :style="props.fillButtonStyle" v-html="finishText"></wizard-button>
            </div>
        </template>
    </form-wizard>
</template>

<script>
    import VueFormWizard from 'vue-form-wizard'
    import 'vue-form-wizard/dist/vue-form-wizard.min.css'
    import '@fortawesome/fontawesome-free/css/all.css'

    Vue.use(VueFormWizard)

    export default {
        name: 'PokerSetup',
        props: {
            stakes: Array,
            limits: Array,
            variants: Array,
            table_sizes: Array,
        },
        data() {
            return {
                bankroll: 1000,
                location: '',
                default_stake: 1,
                default_limit: 1,
                default_variant: 1,
                default_table_size: 1,
                finishText: 'Finish',
                errors: [],
            }
        },
        methods: {
            completeSetup: function(){
                this.finishText = '<i class="fas fa-lg fa-circle-notch fa-spin"></i>';

                axios.post('/setup', {
                    'bankroll': this.bankroll,
                    'default_stake_id': this.default_stake,
                    'default_limit_id': this.default_limit,
                    'default_variant_id': this.default_variant,
                    'default_table_size_id': this.default_table_size,
                    'default_location': this.location,
                })
                .then(response => {
                    if (response.status === 200) {
                        window.location = response.data.redirect
                    }
                })
                .catch(e => {
                    this.finishText = 'Finish';
                    this.errors = e.response.data.errors;
                    // Change tab index to first error.
                    if (this.errors.bankroll) {
                        this.$refs.formwizard.changeTab(this.$refs.formwizard.activeTabIndex, 0);
                    } else if (this.errors.default_stake_id) {
                        this.$refs.formwizard.changeTab(this.$refs.formwizard.activeTabIndex, 1);
                    } else if (this.errors.default_limit_id || this.errors.default_variant_id || this.errors.default_table_size_id) {
                        this.$refs.formwizard.changeTab(this.$refs.formwizard.activeTabIndex, 2);
                    }
                });
            },
        }
    }
</script>

<style scoped>

</style>
