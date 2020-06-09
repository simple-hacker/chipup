<template>
    <div class="flex-col">
        <div class="flex justify-between items-center p-3 rounded text-lg :mdtext-xl font-medium font-bold bg-green-500">
			<div class="flex items-center">
				<h1 class="mr-4">Filters</h1>
				<i class="fas fa-filter"></i>
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
                            v-model="filterFromDate"
                            type="date"
                            input-class="w-full p-2"
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
                            v-model="filterToDate"
                            type="date"
                            input-class="w-full p-2"
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
                    <div
                        v-if="cash_games.length > 0"
                        class="mb-1"
                    >
                        <label class="mr-3 text-sm sm:text-base md:text-lg">Cash Games</label>
                        <toggle-button v-model="filterCashGames" :height="26" color="#38a169"/>
                    </div>
                    <div
                        v-if="tournaments.length > 0"
                        class="mb-1"
                    >
                        <label class="mr-3 text-sm sm:text-base md:text-lg">Tournaments</label>
                        <toggle-button v-model="filterTournaments" :height="26" color="#38a169"/>
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
                    v-model="profitRangeValue"
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
                v-if="filterCashGames"
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
                                <toggle-button :value="true" :height="26" color="#38a169"/>
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
                                <toggle-button :value="true" :height="26" color="#38a169"/>
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
                                <toggle-button :value="true" :height="26" color="#38a169"/>
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
                                <toggle-button :value="true" :height="26" color="#38a169"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--
                TOURNAMENT FILTERS
            -->
            <div
                v-if="filterTournaments"
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
                            v-model="tournamentsBuyInRangeValue"
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
                                <toggle-button :value="true" :height="26" color="#38a169"/>
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
                                <toggle-button :value="true" :height="26" color="#38a169"/>
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
                        <toggle-button :value="true" :height="26" color="#38a169"/>
                    </div>
                </div>
            </div>
            <div class="col-span-4 border border-muted-dark flex justify-center">
                <button @click.prevent="applyFilters" type="button" class="btn btn-green w-full">Apply Filters</button>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

import VueRangeSlider from 'vue-range-component'
import 'vue-range-component/dist/vue-range-slider.css'

export default {
    name: 'Filters',
    components: { VueRangeSlider },
    data() {
        return {
            showCashGameFilters: false,
            showTournamentFilters: false,
            filterCashGames: true,
            filterTournaments: true,
            profitRangeValue: [0, 0],
            tournamentsBuyInRangeValue: [0, 0],
            sliderStyle: {
                backgroundColor: '#38a169',
            },
            sliderToolTipStyle: {
                backgroundColor: '#38a169',
                borderColor: '#246844'
            },
            filterFromDate: '',
            filterToDate: '',
        }
    },
    computed: {
        ...mapState('cash_games', ['cash_games']),
        ...mapState('tournaments', ['tournaments']),
        ...mapGetters('filters', [
                'filteredSessions',
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
    },
    watch: {
        // If uncheck Cash Games, then minimize Cash Game Filters too
        filterCashGames: function(val) {
            if (!val) {
                this.showCashGameFilters = false
            }
        },
        // If uncheck Tournaments, then minimize Cash Game Filters too
        filterTournaments: function(val) {
            if (!val) {
                this.showTournamentFilters = false
            }
        },
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
            this.$emit('close')
        },
        formatCurrency(amount) {
			return this.$currency.format(amount)
		},
    },
    created() {
        this.profitRangeValue = this.profitRange
        this.tournamentsBuyInRangeValue = this.tournamentBuyInRange
        this.filterCashGames = (this.cash_games.length > 0)
        this.filterTournaments = (this.tournaments.length > 0)
    }
}
</script>

<style>

</style>