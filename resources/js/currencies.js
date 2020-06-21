export const locales = [
        {
            code: 'en-GB',
            currency: {
                style: 'currency', currency: 'GBP'
            }
        },
        {
            code: 'en-US',
            currency: {
                style: 'currency', currency: 'USD'
            }
        },
        {
            code: 'en-IE',
            currency: {
                style: 'currency', currency: 'EUR'
            }
        },
        {
            code: 'fr-FR',
            currency: {
                style: 'currency', currency: 'EUR'
            }
        },
        {
            code: 'de-DE',
            currency: {
                style: 'currency', currency: 'EUR'
            }
        },
        {
            code: 'pl-PL',
            currency: {
                style: 'currency', currency: 'PLN'
            }
        },
        {
            code: 'fr-CA',
            currency: {
                style: 'currency', currency: 'CAD'
            }
        },
        {
            code: 'en-CA',
            currency: {
                style: 'currency', currency: 'CAD'
            }
        },
        {
            code: 'en-AU',
            currency: {
                style: 'currency', currency: 'AUD'
            }
        },
]

// Available currencies
let availableCurrencies = locales.reduce((currencies, locale) => {
    currencies.push(locale.currency.currency)
    return currencies
}, [])

// Convert to Set for unique values, but convert Set back to array
export const currencies = Array.from(new Set([...availableCurrencies]))

