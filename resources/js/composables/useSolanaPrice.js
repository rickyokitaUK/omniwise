// src/composables/useSolanaPrice.js
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

export function useSolanaPrice() {
    const latest = ref(null)
    const loading = ref(false)
    let timerId = null

    const fetchLatest = async () => {
        loading.value = true
        try {
            const { data } = await axios.get('/api/solana/latest')
            latest.value = data
        } finally {
            loading.value = false
        }
    }

    onMounted(() => {
        fetchLatest()
        timerId = setInterval(fetchLatest, 5_000) // 5 秒拉一次
    })

    onUnmounted(() => {
        if (timerId) clearInterval(timerId)
    })

    return {
        latest,
        loading,
    }
}
