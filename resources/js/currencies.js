// const numberFormats = {
//     en_US      = $1,205.34
//     en_GB      = £1,205.34
//     en_IE      = €1,205.34
//     de_DE      = 1.205,34 €
//     fr_FR      = 1 205,34 €
//     br_FR      = € 1 205,34
//     ja_JP      = ￥1,205
//     pl_PL      = 1 205,34 zł
//     pt_TL      = 1 205,34 US$
//     fr_CA      = 1 205,34 $
//     en_CA      = $1,205.34
// }

// TODO:  Needs to have a list of formats like this
// const numberFormats = {
//     'en-US': {
//         currency: {
//         style: 'currency', currency: 'USD'
//         }
//     },
//     'ja-JP': {
//         currency: {
//         style: 'currency', currency: 'JPY', currencyDisplay: 'symbol'
//         }
//     }
// }

// NOTE: Get unique currencies supported.  Get single EUR, don't care about Italian or French or anything

// // Available currencies
// let currencies = numberFormats.reduce((currencies, locale) => {
//     currencies.push(locale.currency.currency)
// }, [])

// NOTE: Loop through locales for currency set up
// NOTE: Loop through currencies for transactions amounts

// export const currencies = new Set(...currencies)

export default [
    {
        currency: "GBP",
        country: "GB"
    },
    {
        currency: "EUR",
        country: "EU"
    },
    {
        currency: "USD",
        country: "US"
    },
    {
        currency: "AUD",
        country: "AU"
    },
    {
        currency: "BGN",
        country: "BG"
    },
    {
        currency: "BRL",
        country: "BR"
    },
    {
        currency: "CAD",
        country: "CA"
    },
    {
        currency: "CHF",
        country: "CH"
    },
    {
        currency: "CNY",
        country: "CN"
    },
    {
        currency: "CZK",
        country: "CZ"
    },
    {
        currency: "DKK",
        country: "DK"
    },
    {
        currency: "GEL",
        country: "GE"
    },
    {
        currency: "HKD",
        country: "HK"
    },
    {
        currency: "HUF",
        country: "HU"
    },
    {
        currency: "INR",
        country: "IN"
    },
    {
        currency: "MYR",
        country: "MY"
    },
    {
        currency: "MXN",
        country: "MX"
    },
    {
        currency: "NOK",
        country: "NO"
    },
    {
        currency: "NZD",
        country: "NZ"
    },
    {
        currency: "PLN",
        country: "PL"
    },
    {
        currency: "RON",
        country: "RO"
    },
    {
        currency: "SEK",
        country: "SE"
    },
    {
        currency: "SGD",
        country: "SG"
    },
    {
        currency: "THB",
        country: "TH"
    },
    {
        currency: "NGN",
        country: "NG"
    },
    {
        currency: "PKR",
        country: "PK"
    },
    {
        currency: "TRY",
        country: "TR"
    },
    {
        currency: "ZAR",
        country: "ZA"
    },
    {
        currency: "JPY",
        country: "JP"
    },
    {
        currency: "PHP",
        country: "PH"
    },
    {
        currency: "MAD",
        country: "MA"
    },
    {
        currency: "COP",
        country: "CO"
    },
    {
        currency: "AED",
        country: "AE"
    },
    {
        currency: "IDR",
        country: "ID"
    },
    {
        currency: "CLP",
        country: "CL"
    },
    {
        currency: "UAH",
        country: "UA"
    },
    {
        currency: "GHS",
        country: "GH"
    },
    {
        currency: "RUB",
        country: "RU"
    },
    {
        currency: "LKR",
        country: "LK"
    },
    {
        currency: "KRW",
        country: "KR"
    },
    {
        currency: "VND",
        country: "VN"
    },
    {
        currency: "BDT",
        country: "BD"
    },
    {
        currency: "NPR",
        country: "NP"
    }
]
  