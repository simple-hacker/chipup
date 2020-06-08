<template>
    <div class="w-full grid grid-cols-4 gap-4 pb-4 text-white">
        <div class="col-span-4 md:col-span-2 p-2 md:p-4 bg-card border border-muted-dark rounded shadow">
            <h2 class="w-full border-b border-muted-dark text-xl font-medium p-1 mb-4 md:mb-3">Account Settings</h2>
            <div class="flex flex-col mb-6">
                <form @submit.prevent="updateEmailAddress()">
                    <div class="flex justify-between items-center">
                        <h3 class="hidden md:block font-medium">Change Email</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <input
                                v-model="email"
                                type="text"
                                placeholder="Enter email"
                                :class="{'error-input' : errors.email}"
                                @input="delete errors.email"
                            />
                            <span v-if="errors.email" class="error-message">{{ errors.email[0] }}</span>
                        </div>
                    </div>
                </form>
                <button
                    @click="updateEmailAddress()"
                    type="button"
                    class="w-full md:w-1/2 btn btn-green self-end mt-3"
                >
                    <span v-if="updatingEmail"><i class="fas fa-circle-notch fa-spin"></i></span>
                    <span v-if="!updatingEmail">Update Email</span>
                </button>
            </div>
            <div class="flex flex-col">
                <form @submit.prevent="updatePassword()">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="hidden md:block font-medium">Current Password</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <input
                                v-model="password.current_password"
                                type="password"
                                placeholder="Current password"
                                :class="{'error-input' : errors.current_password}"
                                @input="delete errors.current_password"
                            />
                            <span v-if="errors.current_password" class="error-message">{{ errors.current_password[0] }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="hidden md:block font-medium">New Password</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <input
                                v-model="password.new_password"
                                type="password"
                                placeholder="New password"
                                :class="{'error-input' : errors.new_password}"
                                @input="delete errors.new_password"
                            />
                            <span v-if="errors.new_password" class="error-message">{{ errors.new_password[0] }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <h3 class="hidden md:block font-medium">Confirm New Password</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <input
                                v-model="password.new_password_confirmation"
                                type="password"
                                placeholder="Confirm new password"
                                :class="{'error-input' : errors.new_password_confirmation}"
                                @input="delete errors.new_password_confirmation"
                            />
                        <span v-if="errors.new_password_confirmation" class="error-message">{{ errors.new_password_confirmation[0] }}</span>
                        </div>
                    </div>
                </form>
                <button
                    @click="updatePassword()"
                    type="button"
                    class="w-full md:w-1/2 btn btn-green self-end mt-3"
                >
                    <span v-if="updatingPassword"><i class="fas fa-circle-notch fa-spin"></i></span>
                    <span v-if="!updatingPassword">Update Password</span>
                </button>
            </div>
        </div>
        <div class="col-span-4 md:col-span-2 flex flex-col p-2 md:p-4 bg-card border border-muted-dark rounded shadow">
            <form @submit.prevent="updateDefaultValues()">
                <h2 class="w-full border-b border-muted-dark text-xl font-medium p-1 mb-4 md:mb-3">Default Values</h2>
                <div class="flex-1">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="hidden md:block font-medium">Stake</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <select v-model="default_values.default_stake_id" :class="{ 'error-input' : errors.default_stake_id }">
                                <option
                                    v-for="stake in stakes"
                                    :key="stake.id"
                                    :value="stake.id"
                                    :selected="stake.id == user.default_stake_id"
                                    @input="delete errors.default_stake_id"
                                >
                                    {{ stake.stake }}
                                </option>
                            </select>
                        <span v-if="errors.default_stake_id" class="error-message">{{ errors.default_stake_id[0] }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="hidden md:block font-medium">Limit</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <select v-model="default_values.default_limit_id" :class="{ 'error-input' : errors.default_limit_id }">
                                <option
                                    v-for="limit in limits"
                                    :key="limit.id"
                                    :value="limit.id"
                                    :selected="limit.id == user.default_limit_id"
                                    @input="delete errors.default_limit_id"
                                >
                                    {{ limit.limit }}
                                </option>
                            </select>
                        <span v-if="errors.default_limit_id" class="error-message">{{ errors.default_limit_id[0] }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="hidden md:block font-medium">Variant</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <select v-model="default_values.default_variant_id" :class="{ 'error-input' : errors.default_variant_id }">
                                <option
                                    v-for="variant in variants"
                                    :key="variant.id"
                                    :value="variant.id"
                                    :selected="variant.id == user.default_variant_id"
                                    @input="delete errors.default_variant_id"
                                >
                                    {{ variant.variant }}
                                </option>
                            </select>
                        <span v-if="errors.default_variant_id" class="error-message">{{ errors.default_variant_id[0] }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="hidden md:block font-medium">Table Size</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <select v-model="default_values.default_table_size_id" :class="{ 'error-input' : errors.default_table_size_id }">
                                <option
                                    v-for="table_size in table_sizes"
                                    :key="table_size.id"
                                    :value="table_size.id"
                                    :selected="table_size.id == user.default_table_size_id"
                                    @input="delete errors.default_table_size_id"
                                >
                                    {{ table_size.table_size }}
                                </option>
                            </select>
                        <span v-if="errors.default_table_size_id" class="error-message">{{ errors.default_table_size_id[0] }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <h3 class="hidden md:block font-medium">Location</h3>
                        <div class="flex flex-col w-full md:w-1/2">
                            <input
                                v-model="default_values.default_location"
                                type="text"
                                placeholder="Location"
                                :class="{'error-input' : errors.default_location}"
                                @input="delete errors.default_location"
                            />
                            <span v-if="errors.default_location" class="error-message">{{ errors.default_location[0] }}</span>
                        </div>
                    </div>
                </div>
            </form>
            <button
                @click="updateDefaultValues()"
                type="button"
                class="w-full md:w-1/2 btn btn-green self-end mt-3"
            >
                <span v-if="updatingDefaultValues"><i class="fas fa-circle-notch fa-spin"></i></span>
                <span v-if="!updatingDefaultValues">Update Default Values</span>
            </button>
        </div>
        <div class="col-span-4 p-2 bg-card border border-muted-dark rounded shadow">
            <h2 class="w-full border-b border-muted-dark text-xl font-medium p-1 mb-1 md:mb-3">Bankroll</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 md:col-span-1 p-3 md:p-1">
                    <bankroll-transaction />
                </div>
                <div class="col-span-2 md:col-span-1 mt-4 md:mt-0 p-1 md:p-2 h-96 overflow-y-auto scrolling-touch border border-background">
                    <div v-for="bankrollTransaction in bankrollTransactions"
                        :key="bankrollTransaction.id"
                        @click.prevent="showTransactionDetails(bankrollTransaction)"
                        class="mb-2">
                        <bankroll-transaction-summary :bankrollTransaction="bankrollTransaction"></bankroll-transaction-summary>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import BankrollTransaction from '@components/Bankroll/BankrollTransaction'
import BankrollTransactionSummary from '@components/Bankroll/BankrollTransactionSummary'
import BankrollTransactionDetails from '@components/Bankroll/BankrollTransactionDetails'

export default {
    name: 'Settings',
    components: { BankrollTransaction, BankrollTransactionSummary, BankrollTransactionDetails },
    data() {
        return {
            email: '',
            password: {
                current_password: '',
                new_password: '',
                new_password_confirmation: '',
            },
            default_values: {},
            errors: {},
            updatingEmail: false,
            updatingPassword: false,
            updatingDefaultValues: false,
        }
    },
    computed: {
        ...mapState(['user', 'stakes', 'limits', 'variants', 'table_sizes']),
        ...mapState('bankroll', ['bankrollTransactions']),
    },
    methods: {
        showTransactionDetails: function (bankrollTransaction) {
            this.$modal.show(BankrollTransactionDetails, {
                // Modal props
                bankrollTransaction,
            }, {
                // Modal Options
                classes: 'modal',
                height: 'auto',
                width: '95%',
                maxWidth: 600,
            })
        },
        updateEmailAddress() {
            this.updatingEmail = true
            this.$store.dispatch('updateEmailAddress', this.email)
            .then(response => {
                this.$snotify.success('Saved email.')
                this.email = JSON.parse(JSON.stringify(this.user.email))
                this.errors = {}
                this.updatingEmail = false
            })
            .catch(error => {
                this.$snotify.error('Error: '+error.response.data.message)
                this.errors = error.response.data.errors
                this.updatingEmail = false
            })
        },
        updatePassword() {
            this.updatingPassword = true
            axios.post('/settings/password', this.password)
            .then(response => {
                this.$snotify.success('Saved new password.')
                this.errors = {}
                this.password = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                },
                this.updatingPassword = false
            })
            .catch(error => {
                this.$snotify.error('Error: '+error.response.data.message)
                this.errors = error.response.data.errors
                this.updatingPassword = false
            })
        },
        updateDefaultValues() {
            this.updatingDefaultValues = true
            this.$store.dispatch('updateDefaultValues', this.default_values)
            .then(response => {
                this.$snotify.success('Saved default values.')
                this.default_values = JSON.parse(JSON.stringify(this.user.default_values))
                this.errors = {}
                this.updatingDefaultValues = false
            })
            .catch(error => {
                this.$snotify.error('Error: '+error.response.data.message)
                this.errors = error.response.data.errors
                this.updatingDefaultValues = false
            })
        },
    },
    created() {
        this.email = JSON.parse(JSON.stringify(this.user.email)),
        this.default_values = JSON.parse(JSON.stringify(this.user.default_values))
    },
}
</script>

<style scoped>

</style>