<template>
    <div class="flex flex-col">
        <div v-text="title" class="text-2xl capitalize mb-5 border-b border-muted-light"></div>
        <div class="w-2/3 mx-auto">
            <input
                v-model="transaction.amount"
                type="number"
                min=0
                :class="{'error-input' : errors.amount}"
                @input="delete errors.amount"
            >
            <span v-if="errors.amount" class="error-message">{{ errors.amount[0] }}</span>
        </div>
        <div
            v-if="transaction.hasOwnProperty('comments')"
            class="w-2/3 mx-auto mt-3"
        >
            <textarea
                v-model="transaction.comments"
                row=4
                placeholder="Add comments"
                class="rounded border border-muted-dark p-2"
                :class="{'error-input' : errors.comments}"
                @input="delete errors.comments"
            >
            </textarea>
            <span v-if="errors.comments" class="error-message">{{ errors.comments[0] }}</span>
        </div>
        <div class="flex justify-between mt-3 p-2">
            <div>
                <button
                    v-if="transaction.id"
                    @click.prevent="deleteTransaction"
                    type="button"
                    class="bg-red-500 hover:bg-red-600 focus:bg-red-600 rounded text-white text-sm px-4 py-2"
                >
                    <i class="fas fa-trash mr-3"></i><span>Delete</span>
                </button>
            </div>
            <div>
                <button
                    @click.prevent="saveTransaction"
                    type="button"
                    class="bg-green-500 hover:bg-green-600 focus:bg-green-600 rounded text-white text-sm px-4 py-2"
                >
                    <i class="fas fa-check mr-3"></i><span v-text="saveButtonText"></span>
                </button>
            </div>
		</div>
    </div>
</template>

<script>
import { mapActions } from 'vuex'

export default {
    name: 'TransactionDetails',
    props: {
        transaction: Object,
        transactionType: String,
        gameId: Number,
        gameType: String,
    },
    data() {
        return {
            errors: {},
        }
    },
    computed: {
        title() {
            let mode = (this.transaction.id) ? 'Edit' : 'Add'
            return `${mode} ${this.transactionType}`
        },
        saveButtonText() {
            if (this.transaction.id) {
                return `Save Changes`
            } else {
                return `Add ${this.transactionType}`
            }
        }
    },
    methods: {
        ...mapActions('transactions', ['createTransaction', 'updateTransaction', 'deleteTransaction']),
        saveTransaction() {
            // If id is present then update, else create.
            let action = (this.transaction.id) ? 'transactions/updateTransaction' : 'transactions/createTransaction'

            this.$store.dispatch(action, {
                transaction: this.transaction,
                transactionType: this.transactionType,
                game_id: this.gameId,
                game_type: this.gameType
            })
            .then(response => {
                this.$modal.hide('dialog')
                this.$emit('close')
                this.$snotify.success(`Saved ${this.transactionType}.`)
            })
            .catch(error => {
                this.$snotify.error('Error: '+error.response.data.message)
                this.errors = error.response.data.errors
            })
        },
        deleteTransaction() {
			this.$modal.show('dialog', {
				title: 'Are you sure?',
				text: 'Are you sure you want to delete this transaction?  This action cannot be undone.',
				buttons: [
					{
						title: 'Cancel'
					},
					{
						title: 'Yes, delete.',
						handler: () => {
                            this.$store.dispatch('transactions/deleteTransaction', {
                                transaction: this.transaction,
                                transactionType: this.transactionType
                            })
                            .then(response => {
								this.$modal.hide('dialog')
                                this.$emit('close')
                                this.$snotify.warning('Successfully deleted transaction.')
                            })
                            .catch(error => {
                                this.$snotify.error('Error: '+error.response.data.message)
                            })
						},
						class: 'bg-red-500 text-white'
					},

				],
			})
        }
    }
}
</script>

<style>

</style>