export const locales = {
        'en-GB': {
            currency: { style: 'currency', currency: 'GBP' },
            currencyNoFraction: { style: 'currency', currency: 'GBP', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'en-US': {
            currency: { style: 'currency', currency: 'USD' },
            currencyNoFraction: { style: 'currency', currency: 'USD', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'en-IE': {
            currency: { style: 'currency', currency: 'EUR' },
            currencyNoFraction: { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'fr-FR': {
            currency: { style: 'currency', currency: 'EUR' },
            currencyNoFraction: { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'de-DE': {
            currency: { style: 'currency', currency: 'EUR' },
            currencyNoFraction: { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'pl-PL': {
            currency: { style: 'currency', currency: 'PLN' },
            currencyNoFraction: { style: 'currency', currency: 'PLN', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'fr-CA': {
            currency: { style: 'currency', currency: 'CAD' },
            currencyNoFraction: { style: 'currency', currency: 'CAD', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'en-CA': {
            currency: { style: 'currency', currency: 'CAD' },
            currencyNoFraction: { style: 'currency', currency: 'CAD', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
        'en-AU': {
            currency: { style: 'currency', currency: 'AUD' },
            currencyNoFraction: { style: 'currency', currency: 'AUD', minimumFractionDigits: 0, maximumFractionDigits: 0 }
        },
}

// Available currencies
let availableCurrencies = Object.values(locales).reduce((currencies, locale) => {
    currencies.push(locale.currency.currency)
    return currencies
}, [])

// Convert to Set for unique values, but convert Set back to array
export const currencies = Array.from(new Set([...availableCurrencies]))

