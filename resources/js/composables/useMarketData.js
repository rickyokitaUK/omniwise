import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { API_BASE } from '../config/config.js'

export function useMarketData(serverProfile, showVWAPData) {
  const marketinfo = ref({})
  const sseConnection = ref(null)
  const retries = ref(3)

  const fetchMarketData = async () => {
    try {
      const { data } = await axios.get(`${API_BASE}/api/fetchMarketData.php`, {
        params: { userid: serverProfile.userid },
        withCredentials: true,
      })
      if (data.ok === 1) marketinfo.value = data.marketinfo
    } catch (error) {
      console.error('fetchMarketData error:', error)
    }
  }

  const connect = () => {
    if (!window.EventSource) return fetchMarketData()
    const url = `${serverProfile.sseMaster}/${serverProfile.marketController}?userid=${serverProfile.userid}`
    sseConnection.value = new EventSource(url)
    sseConnection.value.addEventListener('patch', (e) => {
      try {
        const result = JSON.parse(e.data)
        if (showVWAPData.value) marketinfo.value = result.marketinfo
      } catch (err) {
        console.error('SSE parse error:', err)
      }
    })
  }

  const disconnect = () => {
    if (sseConnection.value) {
      sseConnection.value.close()
      sseConnection.value = null
    }
  }

  onMounted(() => {
    fetchMarketData()
    connect()
  })

  onUnmounted(() => disconnect())

  return { marketinfo, fetchMarketData }
}
