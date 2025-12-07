import { computed } from 'vue'

/**
 * 分頁邏輯 Composable
 * @param {Ref} pagination - 分頁資料的 ref
 * @returns {Object} - 包含 visiblePages 計算屬性和 goToPage 方法
 */
export function usePagination(pagination, loadFunction) {
  /**
   * 計算可見的分頁頁碼
   * 邏輯：
   * - 總頁數 <= 7：顯示所有頁碼
   * - 當前頁在前4頁：顯示 1-5 ... 最後一頁
   * - 當前頁在後4頁：顯示 1 ... 倒數5頁
   * - 其他情況：顯示 1 ... 當前-1 當前 當前+1 ... 最後一頁
   */
  const visiblePages = computed(() => {
    if (!pagination.value) return []

    const current = pagination.value.current_page
    const last = pagination.value.last_page
    const pages = []

    if (last <= 7) {
      // 總頁數少於等於7頁，顯示所有頁碼
      for (let i = 1; i <= last; i++) {
        pages.push(i)
      }
    } else if (current <= 4) {
      // 當前頁在前4頁
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 3) {
      // 當前頁在後4頁
      pages.push(1)
      pages.push('...')
      for (let i = last - 4; i <= last; i++) {
        pages.push(i)
      }
    } else {
      // 當前頁在中間
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    }

    return pages
  })

  /**
   * 跳轉到指定頁碼
   * @param {number} page - 目標頁碼
   */
  const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
      loadFunction(page)
    }
  }

  return {
    visiblePages,
    goToPage
  }
}
