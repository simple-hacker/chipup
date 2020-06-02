export default {
    namespace: true,
    state: {
        sortByProfit: (a, b) => {
            return b.profit - a.profit
        },
        sortByProfitDesc: (a, b) => {
            return a.profit - b.profit
        },
        sortByDate: (a , b) => {
            let startTimeA = new Date(a.start_time)
            let startTimeB = new Date(b.start_time)
            return startTimeA < startTimeB ? 1 : -1
        },
        sortByDateDesc: (a , b) => {
            let startTimeA = new Date(a.start_time)
            let startTimeB = new Date(b.start_time)
            return startTimeA > startTimeB ? 1 : -1
        },
    }
}