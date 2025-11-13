import { defineStore } from 'pinia'

export const useStockStore = defineStore('stock', {
  state: () => ({
    selectedStock: null,
    watchlist: [],
  }),

  actions: {
    setStock(stock) {
      this.selectedStock = stock
    },
    addToWatchlist(stock) {
      if (!this.watchlist.some(s => s.code === stock.code)) {
        this.watchlist.push(stock)
      }
    }
  },
})
