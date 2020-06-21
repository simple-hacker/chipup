export const locales = {
    'en-GB': {
        currency: {
            style: 'currency', currency: 'GBP'
        }
    },
    'en-US': {
        currency: {
            style: 'currency', currency: 'USD'
        }
    },
    'en-IE': {
        currency: {
            style: 'currency', currency: 'EUR'
        }
    },
    'fr-FR': {
        currency: {
            style: 'currency', currency: 'EUR'
        }
    },
    'de-DE': {
        currency: {
            style: 'currency', currency: 'EUR'
        }
    },
    'pl-PL': {
        currency: {
            style: 'currency', currency: 'PLN'
        }
    },
    'fr-CA': {
        currency: {
            style: 'currency', currency: 'CAD'
        }
    },
    'en-CA': {
        currency: {
            style: 'currency', currency: 'CAD'
        }
    },
    'en-AU': {
        currency: {
            style: 'currency', currency: 'AUD'
        }
    },
}

// Available currencies
let availableCurrencies = Object.values(locales).reduce((currencies, locale) => {
    currencies.push(locale.currency.currency)
    return currencies
}, [])

export const currencies = new Set([...availableCurrencies])

