export default {
    methods: {
        stakeLabel(stake, currency = 'GBP', locale = 'en-GB') {
            let currencyFormat = new Intl.NumberFormat(locale, { style: 'currency', currency: currency, minimumFractionDigits: 0, maximumFractionDigits: 2 })

            let label = `${currencyFormat.format(stake.small_blind)}/${currencyFormat.format(stake.big_blind)}`

            if (stake.straddle_1) {
                label += `/${currencyFormat.format(stake.straddle_1)}`
            }

            if (stake.straddle_2) {
                label += `/${currencyFormat.format(stake.straddle_2)}`
            }

            if (stake.straddle_3) {
                label += `/${currencyFormat.format(stake.straddle_3)}`
            }

            return label
        },
    }
}