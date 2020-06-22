<template>
    <div
        @click="showTransactionDetails(transaction)"
        @mouseover="showEdit = true"
        @mouseleave="showEdit = false"
        class="relative flex justify-center items-center p-3 md:p-4 bg-gray-500 hover:bg-gray-450 rounded border-r-4 shadow hover:shadow-2xl cursor-pointer text-white text-lg"
        :class="this.transactionType === 'cashout' ? 'border-green-500 hover:border-green-400' : 'border-red-500 hover:border-red-400'"
    >
        <div
            v-text="$n(transaction.amount, { style: 'currency', currency: transaction.currency })">
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