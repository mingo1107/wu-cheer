/**
 * 日期格式化工具函數
 */

/**
 * 格式化日期（僅日期部分）
 * @param {string|Date|null|undefined} dateString - 日期字串或 Date 物件
 * @returns {string} 格式化後的日期字串，格式：YYYY/MM/DD，如果無效則返回 '-'
 */
export const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('zh-TW', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

/**
 * 格式化日期時間（包含日期和時間）
 * @param {string|Date|null|undefined} dateString - 日期字串或 Date 物件
 * @returns {string} 格式化後的日期時間字串，格式：YYYY/MM/DD HH:mm:ss，如果無效則返回 '-'
 */
export const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '-'
  return date.toLocaleString('zh-TW')
}

/**
 * 格式化日期時間（自訂格式，僅日期和時間，不含秒，24小時制）
 * @param {string|Date|null|undefined} dateString - 日期字串或 Date 物件
 * @returns {string} 格式化後的日期時間字串，格式：YYYY/MM/DD HH:mm，如果無效則返回 '-'
 */
export const formatDateTimeShort = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '-'
  
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  return `${year}/${month}/${day} ${hours}:${minutes}`
}

