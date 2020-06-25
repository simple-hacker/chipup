<template>
    <div class="flex-col">
        <div class="flex justify-between items-center p-2 md:p-3 rounded-t text-base md:text-lg font-bold bg-green-500">
			<div class="flex items-center">
				<h1 class="uppercase text-white font-extrabold tracking-wider mr-4">Filters</h1>
				<i class="fas fa-filter"></i>
			</div>
            <div
                v-if="showRevert"
                @click="resetFilters"
                class="flex items-center cursor-pointer rounded hover:bg-green-400 p-2"
            >
                <i class="fas fa-check-double mr-3"></i>
                <span class="text-sm">Set to default filters</span>
            </div>
		</div>
        <div class="grid cols-4 gap-2">
            <!--
                DATES
            -->
            <div class="col-span-4 lg:col-span-3 xxl:col-span-2 card">
                <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Dates</h2>
                <div class="flex md:justify-around bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2">
                    <div class="w-1/2 md:w-auto flex items-center mr-1">
                        <span class="hidden md:block"><i class="fas fa-calendar-alt fa-lg"></i></span>
                        <span class="mx-3 text-sm sm:text-base md:text-lg">From</span>
                        <datetime
                            input-id="filterFromDate"
                            v-model="filters.fromDate"
                            type="date"
                            input-class="w-full p-2 bg-gray-600"
                            :max-datetime="maxFromDateTime"
                            auto
                            title="Filter from"
                            class="theme-green"
                        ></datetime>
                    </div>
                    <div class="w-1/2 md:w-auto flex items-center ml-1">
                        <span class="hidden md:block"><i class="fas fa-calendar-alt fa-lg"></i></span>
                        <span class="mx-3 text-sm sm:text-base md:text-lg">To</span>
                        <datetime
                            input-id="filterToDate"
                            v-model="filters.toDate"
                            type="date"
                            input-class="w-full p-2 bg-gray-600"
                            :min-datetime="filters.fromDate"
                            :max-datetime="maxDateTime"
                            auto
                            title="Filter to"
                            class="theme-green"
                        ></datetime>
                    </div>
                </div>
            </div>
            <!--
                GAME TYPE
            -->
            <div class="col-span-4 lg:col-span-1 xxl:col-span-2 card">
                <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Game Type</h2>
                <div class="flex flex-wrap justify-around bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2">
                    <div
                        v-if="gameTypes.includes('cash_game')"
                        class="mb-1"
                    >
                        <label class="mr-3 text-sm sm:text-base md:text-lg">Cash Games</label>
                        <toggle-button
                            @change="filterGameTypeVariable('cash_game', $event.value)"
                            :value="filters.gameTypes.includes('cash_game')"
                            :sync="true"
                            :height="26"
                            color="#38a169"
                        />
                    </div>
                    <div
                        v-if="gameTypes.includes('tournament')"
                        class="mb-1"
                    >
                        <label class="mr-3 text-sm sm:text-base md:text-lg">Tournaments</label>
                        <toggle-button
                            @change="filterGameTypeVariable('tournament', $event.value)"
                            :value="filters.gameTypes.includes('tournament')"
                            :sync="true"
                            :height="26"
                            color="#38a169"
                        />
                    </div>
                </div>
            </div>
            <!--
                PROFIT RANGE
            -->
            <div class="col-span-4 card">
                <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Profit Range</h2>
                <div class="bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2">
                    <vue-range-slider
                        ref="profit-slider"
                        v-model="filters.profitRange"
                        :min="profitRange[0]"
                        :max="profitRange[1]"
                        :process-style="sliderStyle"
                        :tooltip-style="sliderToolTipStyle"
                        :formatter="value => $n(value, 'currency')"
                    ></vue-range-slider>
                </div>
            </div>
            <!--
                CASH GAME FILTERS
            -->
            <div
                v-if="filters.gameTypes.includes('cash_game')"
                class="col-span-4 flex flex-col"
            >
                <div
                    @click.prevent="showCashGameFilters = ! showCashGameFilters"
                    class="w-full flex justify-between items-center p-2 md:p-3 cursor-pointer text-sm md:text-base font-medium shadow-2xl"
                    :class="showCashGameFilters ? 'bg-green-500 hover:bg-green-600 rounded-t' : 'bg-gray-450 hover:bg-gray-500 rounded'"
                >
                    <div class="flex items-center">
                        <h1 class="uppercase text-white font-extrabold tracking-wider mr-4">Cash Game Filters</h1>
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <i v-show="!showCashGameFilters" class="fas fa-chevron-down fa-lg"></i>
                        <i v-show="showCashGameFilters" class="fas fa-chevron-up fa-lg"></i>
                    </div>
                </div>
                <div v-show-slide="showCashGameFilters" class="grid grid-cols-4 gap-2">
                    <!--
                        CASH GAME STAKES
                    -->
                    <div class="col-span-4 sm:col-span-2 md:col-span-1 card">
                        <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Stakes</h2>
                        <div class="flex flex-wrap bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2 flex-1 content-start">
                            <div v-for="(stake, index) in cashGameStakeFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between items-center py-1 px-3">
                                <label class="mr-3 text-sm sm:text-base md:text-lg">{{ stake }}</label>
                                <toggle-button
                                    @change="filterCashGameVariable('stakes', stake, $event.value)"
                                    :value="filters.cashGameFilters.stakes.includes(stake)"
                                    :sync="true"
                                    :height="26"
                                    color="#38a169"
                                />
                            </div>
                        </div>
                    </div>
                    <!--
                        CASH GAME LIMITS
                    -->
                    <div class="col-span-4 sm:col-span-2 md:col-span-1 card">
                        <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Limits</h2>
                        <div class="flex flex-wrap bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2 flex-1 content-start">
                            <div v-for="(limit, index) in cashGameLimitFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between items-center py-1 px-3">
                                <label class="mr-3 text-sm sm:text-base md:text-lg">{{ limit }}</label>
                                <toggle-button
                                    @change="filterCashGameVariable('limits', limit, $event.value)"
                                    :value="filters.cashGameFilters.limits.includes(limit)"
                                    :sync="true"
                                    :height="26"
                                    color="#38a169"
                                />
                            </div>
                        </div>
                    </div>
                    <!--
                        CASH GAME VARIANTS
                    -->
                    <div class="col-span-4 sm:col-span-2 md:col-span-1 card">
                        <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Variant</h2>
                        <div class="flex flex-wrap bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2 flex-1 content-start">
                            <div v-for="(variant, index) in cashGameVariantFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between items-center py-1 px-3">
                                <label class="mr-3 text-sm sm:text-base md:text-lg">{{ variant }}</label>
                                <toggle-button
                                    @change="filterCashGameVariable('variants', variant, $event.value)"
                                    :value="filters.cashGameFilters.variants.includes(variant)"
                                    :sync="true"
                                    :height="26"
                                    color="#38a169"
                                />
                            </div>
                        </div>
                    </div>
                    <!--
                        CASH GAME TABLE SIZE
                    -->
                    <div class="col-span-4 sm:col-span-2 md:col-span-1 card">
                        <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Table Size</h2>
                        <div class="flex flex-wrap bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2 flex-1 content-start">
                            <div v-for="(table_size, index) in cashGameTableSizeFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between items-center py-1 px-3">
                                <label class="mr-3 text-sm sm:text-base md:text-lg">{{ table_size }}</label>
                                <toggle-button
                                    @change="filterCashGameVariable('table_sizes', table_size, $event.value)"
                                    :value="filters.cashGameFilters.table_sizes.includes(table_size)"
                                    :sync="true"
                                    :height="26"
                                    color="#38a169"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--
                TOURNAMENT FILTERS
            -->
            <div
                v-if="filters.gameTypes.includes('tournament')"
                class="col-span-4 flex flex-col"
            >
                <div
                    @click.prevent="showTournamentFilters = ! showTournamentFilters"
                    class="w-full flex justify-between items-center p-2 md:p-3 cursor-pointer text-sm md:text-base font-medium rounded shadow-2xl"
                    :class="showTournamentFilters ? 'bg-green-500 hover:bg-green-600 rounded-t' : 'bg-gray-450 hover:bg-gray-500 rounded'"
                >
                    <div class="flex items-center">
                        <h1 class="uppercase text-white font-extrabold tracking-wider mr-4">Tournament Filters</h1>
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <i v-show="!showTournamentFilters" class="fas fa-chevron-down fa-lg"></i>
                        <i v-show="showTournamentFilters" class="fas fa-chevron-up fa-lg"></i>
                    </div>
                </div>
                <div v-show-slide="showTournamentFilters" class="grid grid-cols-4 gap-2">
                    <!--
                        TOURNAMENT BUY IN RANGE
                    -->
                    <div class="col-span-4 card">
                        <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Buy In Range</h2>
                        <div class="bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2">
                            <vue-range-slider
                                ref="buy-in-slider"
                                v-model="filters.tournamentFilters.buyInRange"
                                :min="tournamentBuyInRange[0]"
                                :max="tournamentBuyInRange[1]"
                                :process-style="sliderStyle"
                                :tooltip-style="sliderToolTipStyle"
                                :formatter="value => $n(value, 'currency')"
                            ></vue-range-slider>
                        </div>
                    </div>
                    <!--
                        TOURNAMENT LIMITS
                    -->
                    <div class="col-span-4 md:col-span-2 card">
                        <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Limits</h2>
                        <div class="flex flex-wrap bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2 flex-1 content-start">
                            <div v-for="(limit, index) in tournamentLimitFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between items-center py-1 px-3">
                                <label class="mr-3 text-sm sm:text-base md:text-lg">{{ limit }}</label>
                                <toggle-button
                                    @change="filterTournamentVariable('limits', limit, $event.value)"
                                    :value="filters.tournamentFilters.limits.includes(limit)"
                                    :sync="true"
                                    :height="26"
                                    color="#38a169"
                                />
                            </div>
                        </div>
                    </div>
                    <!--
                        TOURNAMENT VARIANTS
                    -->
                    <div class="col-span-4 md:col-span-2 card">
                        <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Variant</h2>
                        <div class="flex flex-wrap bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2 flex-1 content-start">
                            <div v-for="(variant, index) in tournamentVariantFilters" :key="index" class="w-1/2 md:w-full xl:w-1/2 xxl:w-1/3 flex justify-between items-center py-1 px-3">
                                <label class="mr-3 text-sm sm:text-base md:text-lg">{{ variant }}</label>
                                <toggle-button
                                    @change="filterTournamentVariable('variants', variant, $event.value)"
                                    :value="filters.tournamentFilters.variants.includes(variant)"
                                    :sync="true"
                                    :height="26"
                                    color="#38a169"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--
                LOCATIONS
            -->
            <div class="col-span-4 card">
                <h2 class="text-sm md:text-base uppercase text-gray-200 font-extrabold tracking-wider mb-1">Locations</h2>
                <div class="flex flex-wrap md:justify-around bg-gray-500 rounded shadow-2xl px-1 py-2 md:p-2">
                    <div v-for="location in locationFilters" :key="location" class="w-full md:w-auto flex justify-between items-center py-1 px-3">
                        <label class="mr-3 text-sm sm:text-base md:text-lg">{{ location }}</label>
                        <toggle-button
                            @change="filterLocations(location)"
                            :value="filters.locations.includes(location)"
                            :sync="true"
                            :height="26"
                            color="#38a169"
                        />
                    </div>
                </div>
            </div>
            <div class="col-span-4 flex justify-center">
                <button
                    @click.prevent="submitFilters"
                    :disabled="noGameTypesSelected"
                    type="button"
                    class="btn btn-green w-full disabled:bg-gray-500 disabled:border-gray-700 disabled:opacity-75"
                >
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment'
import { mapState, mapGetters, mapActions } from 'vuex'

import VueRangeSlider from 'vue-range-component'
import 'vue-range-component/dist/vue-range-slider.css'

export default {
    name: 'Filters',
    components: { VueRangeSlider },
    data() {
        return {
            maxDateTime: moment().format(),
            showCashGameFilters: false,
            showTournamentFilters: false,
            sliderStyle: {
                backgroundColor: '#38a169',
            },
            sliderToolTipStyle: {
                backgroundColor: '#38a169',
                borderColor: '#246844'
            },
            filters: {},
            errors: {},
        }
    },
    computed: {
        ...mapState('filtered_sessions', ['currentFilters']),
        ...mapGetters('filters', [
            'unfilteredFilters',
            'gameTypes',
            'profitRange',
            'cashGameStakeFilters',
            'cashGameLimitFilters',
            'cashGameVariantFilters',
            'cashGameTableSizeFilters',
            'tournamentBuyInRange',
            'tournamentLimitFilters',
            'tournamentVariantFilters',
            'locationFilters'
        ]),
        showRevert() {
            return ! _.isEqual(this.filters, this.unfilteredFilters)
        },
        maxFromDateTime() {
            return moment(this.filters.toDate).format() < moment().format() ? moment(this.filters.toDate).format() : moment().format()
        },
        noGameTypesSelected() {
            return !(this.filters.gameTypes.length > 0)
        },
        invalidDates() {
            if (this.filters.fromDate && this.filters.toDate) {
                return this.filters.toDate < this.filters.fromDate
            }

            return false
        }
    },
    watch: {
        // If showCashGameFilters sets to true and showTournamentFilters = true
        // then set showTournamentFilters to false so we only have one filter dropdown showing at a time.
        showCashGameFilters: function(val) {
            if (val && this.showTournamentFilters) {
                this.showTournamentFilters = false
            }
        },
        // If showTournamentFilters sets to true and showCashGameFilters = true
        // then, set showCashGameFilters to false so we only have one filter dropdown showing at a time.
        showTournamentFilters: function(val) {
            if (val && this.showCashGameFilters) {
                this.showCashGameFilters = false
            }
        },
    },
    methods: {
        ...mapActions('filtered_sessions', ['applyFilters']),
        submitFilters() {
            // Apply button disables if no game types are selected, but user can still inspect element and remove disabled attribute
            // Include the validation here before applying filters
            if (!this.invalidDates && !this.noGameTypesSelected) {
                this.applyFilters(this.filters)
                .then(response => {
                    this.$emit('close')
                })
                .catch(error => {
                    this.$snotify.error(error)
                })
            }
        },
        resetFilters() {
            this.filters = JSON.parse(JSON.stringify(this.unfilteredFilters))
        },
        formatCurrency(amount) {
			return this.$currency.format(amount)
        },
        filterGameTypeVariable(gameType, toggleValue) {
            if (toggleValue && !this.filters.gameTypes.includes(gameType)) {
                this.filters.gameTypes.push(gameType)
            } else {
                this.filters.gameTypes = this.filters.gameTypes.filter(v => v !== gameType)
                // Minimize filters so they appear minimized if user left them open and then reselects gametype.
                if (gameType === 'cash_game') {
                    this.showCashGameFilters = false
                    this.filters.cashGameFilters = JSON.parse(JSON.stringify(this.unfilteredFilters.cashGameFilters))
                }
                if (gameType === 'tournament') {
                    this.showTournamentFilters = false
                    this.filters.tournamentFilters = JSON.parse(JSON.stringify(this.unfilteredFilters.tournamentFilters))
                }
            }
        },
        filterCashGameVariable(variable, variableValue, toggleValue) {
            // If toggleValue is true then button is true, else false
            // Make sure object has the variable property and that it is an array.
            if (this.filters.cashGameFilters.hasOwnProperty(variable) && Array.isArray(this.filters.cashGameFilters[variable])) {
                // If toggle is true and does not already have variable in array, then add
                if (toggleValue && !this.filters.cashGameFilters[variable].includes(variableValue)) {
                    this.filters.cashGameFilters[variable].push(variableValue)
                }
                // Else toggle is false, check if variable is in array and filter out.
                else {
                    this.filters.cashGameFilters[variable] = this.filters.cashGameFilters[variable].filter(v => v !== variableValue)
                }
            }
        },
        filterTournamentVariable(variable, variableValue, toggleValue) {
            // If toggleValue is true then button is true, else false
            // Make sure object has the variable property and that it is an array.
            if (this.filters.tournamentFilters.hasOwnProperty(variable) && Array.isArray(this.filters.tournamentFilters[variable])) {
                // If toggle is true and does not already have variable in array, then add
                if (toggleValue && !this.filters.tournamentFilters[variable].includes(variableValue)) {
                    this.filters.tournamentFilters[variable].push(variableValue)
                }
                // Else toggle is false, check if variable is in array and filter out.
                else {
                    this.filters.tournamentFilters[variable] = this.filters.tournamentFilters[variable].filter(v => v !== variableValue)
                }
            }
        },
        filterLocations(location) {
            if (this.filters.locations.includes(location)) {
                this.filters.locations = this.filters.locations.filter(l => l !== location)
            } else {
                this.filters.locations.push(location)
            }
        },
    },
    created() {
        this.filters = JSON.parse(JSON.stringify(this.currentFilters))
        this.filterCashGames = this.filters.gameTypes.includes('cash_game')
        this.filterTournaments = this.filters.gameTypes.includes('tournament')
    }
}
</script>

<style>

</style>