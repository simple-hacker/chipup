<template>
    <div class="flex-col">
        <div class="flex justify-between items-center p-3 rounded text-lg :mdtext-xl font-medium font-bold bg-green-500">
			<div class="flex items-center">
				<h1 class="mr-4">Filters</h1>
				<i class="fas fa-filter"></i>
			</div>
            <div
                @click="resetFilters"
                class="cursor-pointer rounded hover:bg-green-400 p-1"
            >
                <i class="fas fa-check-double"></i>
                <span class="text-sm">Set to default filters</span>
            </div>
		</div>
        <div class="grid cols-4 gap-2">
            <!--
                DATES
            -->
            <div class="col-span-4 lg:col-span-3 xxl:col-span-2 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Dates</h2>
                <div class="flex md:justify-around">
                    <div class="w-1/2 md:w-auto flex items-center mr-1">
                        <span class="hidden md:block"><i class="fas fa-calendar-alt fa-lg"></i></span>
                        <span class="mx-3 text-sm sm:text-base md:text-lg">From</span>
                        <datetime
                            input-id="filterFromDate"
                            v-model="filters.fromDate"
                            type="date"
                            input-class="w-full p-2"
                            :max-datetime="maxFromDateTime"
                            auto
                            title="Filter from"
                            class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
                        ></datetime>
                    </div>
                    <div class="w-1/2 md:w-auto flex items-center ml-1">
                        <span class="hidden md:block"><i class="fas fa-calendar-alt fa-lg"></i></span>
                        <span class="mx-3 text-sm sm:text-base md:text-lg">To</span>
                        <datetime
                            input-id="filterToDate"
                            v-model="filters.toDate"
                            type="date"
                            input-class="w-full p-2"
                            :min-datetime="filters.fromDate"
                            :max-datetime="maxDateTime"
                            auto
                            title="Filter to"
                            class="w-full bg-muted-light border border-muted-dark rounded border theme-green"
                        ></datetime>
                    </div>
                </div>
            </div>
            <!--
                GAME TYPE
            -->
            <div class="col-span-4 lg:col-span-1 xxl:col-span-2 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-3">Game Type</h2>
                <div class="flex flex-wrap justify-around">
                    <div class="mb-1">
                        <label class="mr-3 text-sm sm:text-base md:text-lg">Cash Games</label>
                        <toggle-button
                            @change="filterGameTypeVariable('cash_game', $event.value)"
                            :value="filters.gameTypes.includes('cash_game')"
                            :sync="true"
                            :height="26"
                            color="#38a169"
                        />
                    </div>
                    <div class="mb-1">
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
            <div class="col-span-4 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Profit Range</h2>
                <vue-range-slider
                    ref="profit-slider"
                    v-model="filters.profitRange"
                    :min="profitRange[0]"
                    :max="profitRange[1]"
                    :process-style="sliderStyle"
                    :tooltip-style="sliderToolTipStyle"
                    :formatter="value => formatCurrency(value)"
                ></vue-range-slider>
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
                    class="w-full flex justify-between items-center p-2 md:p-3 cursor-pointer text-base md:text-lg font-medium"
                    :class="showCashGameFilters ? 'bg-green-500 hover:bg-green-600' : 'bg-muted-dark hover:bg-muted-light'"
                >
                    <div class="flex items-center">
                        <h1 class="mr-4">Cash Game Filters</h1>
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <i v-show="!showCashGameFilters" class="fas fa-chevron-down fa-lg"></i>
                        <i v-show="showCashGameFilters" class="fas fa-chevron-up fa-lg"></i>
                    </div>
                </div>
                <div v-show-slide="showCashGameFilters" class="flex flex-wrap">
                    <!--
                        CASH GAME STAKES
                    -->
                    <div class="w-full sm:w-1/2 md:w-1/4 border border-muted-dark p-1 md:p-3">
                        <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Stakes</h2>
                        <div class="flex flex-wrap">
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
                    <div class="w-full sm:w-1/2 md:w-1/4 border border-muted-dark p-1 md:p-3">
                        <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Limits</h2>
                        <div class="flex flex-wrap">
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
                    <div class="w-full sm:w-1/2 md:w-1/4 border border-muted-dark p-1 md:p-3">
                        <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Variant</h2>
                        <div class="flex flex-wrap">
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
                    <div class="w-full sm:w-1/2 md:w-1/4 border border-muted-dark p-1 md:p-3">
                        <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Table Size</h2>
                        <div class="flex flex-wrap">
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
                    class="w-full flex justify-between items-center p-2 md:p-3 cursor-pointer text-base md:text-lg font-medium"
                    :class="showTournamentFilters ? 'bg-green-500 hover:bg-green-600' : 'bg-muted-dark hover:bg-muted-light'"
                >
                    <div class="flex items-center">
                        <h1 class="mr-4">Tournament Filters</h1>
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <i v-show="!showTournamentFilters" class="fas fa-chevron-down fa-lg"></i>
                        <i v-show="showTournamentFilters" class="fas fa-chevron-up fa-lg"></i>
                    </div>
                </div>
                <div v-show-slide="showTournamentFilters" class="flex flex-wrap">
                    <!--
                        TOURNAMENT BUY IN RANGE
                    -->
                    <div class="w-full border border-muted-dark p-1 md:p-3">
                        <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Buy In Range</h2>
                        <vue-range-slider
                            ref="buy-in-slider"
                            v-model="filters.tournamentFilters.buyInRange"
                            :min="tournamentBuyInRange[0]"
                            :max="tournamentBuyInRange[1]"
                            :process-style="sliderStyle"
                            :tooltip-style="sliderToolTipStyle"
                            :formatter="value => formatCurrency(value)"
                        ></vue-range-slider>
                    </div>
                    <!--
                        TOURNAMENT LIMITS
                    -->
                    <div class="w-full sm:w-1/2 border border-muted-dark p-1 md:p-3">
                        <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Limits</h2>
                        <div class="flex flex-wrap">
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
                    <div class="w-full sm:w-1/2 border border-muted-dark p-1 md:p-3">
                        <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-1 md:mb-3">Variant</h2>
                        <div class="flex flex-wrap">
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
            <div class="col-span-4 border border-muted-dark p-1 md:p-3">
                <h2 class="w-full border-b border-muted-dark text-lg md:text-xl font-medium p-1 mb-3">Locations</h2>
                <div class="flex flex-wrap justify-around">
                    <div v-for="location in locationFilters" :key="location" class="mb-1">
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
            <div class="col-span-4 border border-muted-dark flex justify-center">
                <button
                    @click.prevent="applyFilters"
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
import { mapState, mapGetters } from 'vuex'

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
        maxFromDateTime() {
            return moment(this.filters.toDate).format() < moment().format() ? moment(this.filters.toDate).format() : moment().format()
        },
        noGameTypesSelected() {
            return !(this.filters.gameTypes.length > 0)
        },
        invalidDates() {
            return this.filters.toDate < this.filters.fromDate
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
        }
    },
    methods: {
        applyFilters() {
            // Apply button disables if no game types are selected, but user can still inspect element and remove disabled attribute
            // Include the validation here before applying filters
            if (!this.invalidDates && !this.noGameTypesSelected) {
                this.$emit('close')
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
        this.filters = JSON.parse(JSON.stringify(this.unfilteredFilters))
        this.filterCashGames = this.filters.gameTypes.includes('cash_game')
        this.filterTournaments = this.filters.gameTypes.includes('tournament')
    }
}
</script>

<style>

</style>