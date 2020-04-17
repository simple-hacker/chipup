<template>
    <div class="w-full grid grid-cols-4 gap-4 text-white">
        <div class="col-span-4 md:col-span-2 p-2 md:p-4 bg-card border border-muted-dark rounded shadow">
            <h2 class="w-full border-b border-muted-dark text-xl font-medium p-1 mb-4 md:mb-3">Account Settings</h2>
            <div class="flex flex-col mb-6">
                <div class="flex justify-between items-center">
                    <h3 class="hidden md:block font-medium">Change Email</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <input v-model="email" type="text" placeholder="Enter email" :class="{'border-red-700' : errors.email}"/>
                        <span v-if="errors.email" class="error">{{ errors.email }}</span>
                    </div>
                </div>
                <button type="button" class="btn-green self-end mt-3">Change email</button>
            </div>
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="hidden md:block font-medium">Current Password</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <input v-model="current_password" type="password" placeholder="Current password" :class="{'border-red-700' : errors.current_password}"/>
                        <span v-if="errors.current_password" class="error">{{ errors.current_password }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="hidden md:block font-medium">New Password</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <input v-model="new_password" type="password" placeholder="New password" :class="{'border-red-700' : errors.new_password}"/>
                        <span v-if="errors.new_password" class="error">{{ errors.new_password }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <h3 class="hidden md:block font-medium">Confirm New Password</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <input v-model="new_password_confirmation" type="password" placeholder="Confirm new password" :class="{'border-red-700' : errors.new_password_confirmation}"/>
                    <span v-if="errors.new_password_confirmation" class="error">{{ errors.new_password_confirmation }}</span>
                    </div>
                </div>
                <button type="button" class="btn-green self-end mt-3">Change Password</button>
            </div>
        </div>
        <div class="col-span-4 md:col-span-2 flex flex-col p-2 md:p-4 bg-card border border-muted-dark rounded shadow">
            <h2 class="w-full border-b border-muted-dark text-xl font-medium p-1 mb-4 md:mb-3">Default Values</h2>
            <div class="flex-1">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="hidden md:block font-medium">Stake</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <select v-model="stake" :class="{ 'border-red-700' : errors.stake_id }">
                            <option v-for="stake in stakes" :key="stake.id" :value="stake.id">{{ stake.stake }}</option>
                        </select>
                    <span v-if="errors.stake" class="error">{{ errors.stake }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="hidden md:block font-medium">Limit</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <select v-model="limit" :class="{ 'border-red-700' : errors.limit_id }">
                            <option v-for="limit in limits" :key="limit.id" :value="limit.id">{{ limit.limit }}</option>
                        </select>
                    <span v-if="errors.limit" class="error">{{ errors.limit }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="hidden md:block font-medium">Variant</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <select v-model="variant" :class="{ 'border-red-700' : errors.variant_id }">
                            <option v-for="variant in variants" :key="variant.id" :value="variant.id">{{ variant.variant }}</option>
                        </select>
                    <span v-if="errors.variant" class="error">{{ errors.variant }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="hidden md:block font-medium">Table Size</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <select v-model="table_size" :class="{ 'border-red-700' : errors.table_size_id }">
                            <option v-for="table_size in table_sizes" :key="table_size.id" :value="table_size.id">{{ table_size.table_size }}</option>
                        </select>
                    <span v-if="errors.table_size" class="error">{{ errors.table_size }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <h3 class="hidden md:block font-medium">Location</h3>
                    <div class="flex flex-col w-full md:w-1/2">
                        <input v-model="location" type="password" placeholder="Location" :class="{'border-red-700' : errors.location}"/>
                    <span v-if="errors.location" class="error">{{ errors.location }}</span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-green self-end mt-3">Save default values</button>
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
import { mapState, mapGetters } from 'vuex'
import BankrollTransaction from '@components/Bankroll/BankrollTransaction'
import BankrollTransactionSummary from '@components/Bankroll/BankrollTransactionSummary'
import BankrollTransactionDetails from '@components/Bankroll/BankrollTransactionDetails'

export default {
    name: 'Settings',
    components: { BankrollTransaction, BankrollTransactionSummary, BankrollTransactionDetails },
    data() {
        return {
            email: '',
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
            errors: {},
			stake: 1,
			limit: 1,
			variant: 1,
			table_size: 1,
			location: '',
			stakes: [
				{id: 1, stake: '1/1'},
				{id: 2, stake: '1/2'},
				{id: 3, stake: '2/4'},
			],
			limits: [
				{id: 1, limit: 'No Limit'},
				{id: 2, limit: 'Pot Limit'},
				{id: 3, limit: 'Limit'},
			],
			variants: [
				{id: 1, variant: 'Holdem'},
				{id: 2, variant: 'Omaha'},
				{id: 3, variant: 'Stud8'},
			],
			table_sizes: [
				{id: 1, table_size: 'Full-Ring'},
				{id: 2, table_size: 'Mixed'},
				{id: 3, table_size: 'Heads Up'},
            ],
        }
    },
    methods: {
        showTransactionDetails: function (bankrollTransaction) {
            this.$modal.show(BankrollTransactionDetails, {
                // Modal props
                bankrollTransaction: bankrollTransaction,
            }, {
                // Modal Options
                classes: 'bg-background text-white p-1 md:p-3 rounded-lg border border-muted-dark',
                height: 'auto',
                width: '95%',
                maxWidth: 600,
            })
        }
    },
    computed: {
        ...mapState(['bankroll', 'bankrollTransactions']),
        ...mapGetters(['deposits', 'withdrawals'])
    }
}
</script>

<style scoped>

</style>