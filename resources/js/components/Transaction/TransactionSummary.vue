<template>
    <div
        @click="showTransactionDetails(transaction)"
        @mouseover="showEdit = true"
        @mouseleave="showEdit = false"
        class="flex relative p-3 border border-muted-dark shadow bg-card hover:bg-muted-dark cursor-pointer text-white mb-1 justify-center text-lg group"
    >
        <div
            v-text="formatCurrency(transaction.amount)">
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
            this.$modal.show(TransactionDetails, {
                // Modal props
                transaction: JSON.parse(JSON.stringify(this.transaction)),
                transactionType: this.transactionType,
                gameId: this.gameId,
                gameType: 'cash_game'
            }, {
                // Modal Options
                classes: 'bg-background text-white p-1 md:p-3 rounded-lg border border-muted-dark',
                height: 'auto',
                width: '95%',
                maxWidth: 600,
            })
        },
        formatCurrency(amount) {
			return Vue.prototype.currency.format(amount)
		},
    }
}
</script>

<style>

</style>