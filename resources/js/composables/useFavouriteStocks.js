import { ref } from 'vue'
import axios from 'axios'
import { API_BASE } from '../config/config.js'

export function useFavoriteStocks(user) {
  const isFavStock = ref(false)

  const checkFavStockStatus = async (stockCode) => {
    try {
      const { data } = await axios.get(`${API_BASE}/api/checkFavStockStatus.php`, {
        params: { user_id: user.id, stock_code: stockCode },
      })
      isFavStock.value = !!data.isFavorite
    } catch (err) {
      console.error(err)
    }
  }

  const toggleFavorite = async (stock) => {
    if (isFavStock.value) {
      await axios.post(`${API_BASE}/api/removeStockWatchList.php`, {
        user_id: user.id,
        stock_code: stock.code,
      })
      isFavStock.value = false
    } else {
      await axios.post(`${API_BASE}/api/addStockWatchList.php`, {
        user_id: user.id,
        stock_code: stock.code,
        stock_name: stock.name,
      })
      isFavStock.value = true
    }
  }

  return { isFavStock, checkFavStockStatus, toggleFavorite }
}
