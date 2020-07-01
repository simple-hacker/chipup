<template>
    <div
        @click="showTransactionDetails(transaction)"
        @mouseover="showEdit = true"
        @mouseleave="showEdit = false"
        class="relative flex justify-center items-center p-3 md:p-4 bg-gray-450 hover:bg-gray-400 rounded border-r-4 shadow hover:shadow-2xl cursor-pointer text-white text-lg"
        :class="this.transactionType === 'cashout' ? 'border-green-500 hover:border-green-400' : 'border-red-500 hover:border-red-400'"
    >
        <div
            v-text="$n(transaction.amount, { style: 'currency', currency: transactionCurrency })">
        </div>
        <div
            v-if="showEdit"
            class="absolute right-20"
        >
            <i class="fas fa-edit"></i>
        </div>
    </div>
</template>

<script>
import TransactionDetails from '@components/Transaction/TransactionDetails'

export default {
    name: 'TransactionSummary',
    components: { TransactionDetails },
    props: {
        transaction: Object,
        transactionType: String,
        gameId: Number,
    },
    data() {
        return {
            showEdit: false,
        }
    },
    computed: {
        transactionCurrency() {
            // Need to refer to this for transaction currency because when we delete a session, it tries to rerender
            // the TransactionSummary.vue DOM even though we redirect to Sessions.vue
            // This results in trying to display all currency values using transaction.currency which is undefined and so
            // get lots of errors in Chrome Dev Tools.
            return this.transaction?.currency ?? this.$store.state?.user?.currency ?? 'GBP'
        }
    },
    methods: {
        showTransactionDetails(transaction) {
            const modalClass = (this.transactionType === 'cashout') ? 'modal-green' : 'modal-red'

            this.$modal.show(TransactionDetails, {
                // Modal props
                transaction: JSON.parse(JSON.stringify(this.transaction)),
                transactionType: this.transactionType,
                gameId: this.gameId,
            }, {
                // Modal Options
                classes: ['modal', modalClass],
                style: 'overflow: visible;',
                height: 'auto',
                width: '95%',
                maxWidth: 600,
            })
        },
    },
}
</script>

<style>

</style>