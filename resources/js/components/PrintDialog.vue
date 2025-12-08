<template>
  <div v-if="modelValue" class="fixed inset-0 bg-gray-600/50 h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative mx-auto px-6 py-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-medium text-gray-900">列印未列印憑證</h3>
        <button @click="close" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="space-y-4">
        <div v-if="loading" class="text-gray-500 flex items-center gap-2">
          <i class="fas fa-spinner fa-spin"></i>
          讀取統計中...
        </div>
        <div v-else class="text-sm text-gray-700 space-y-1">
          <div>總張數：<span class="font-semibold">{{ totals?.total ?? 0 }} 張</span></div>
          <div>總米數：<span class="font-semibold">{{ totals?.total_meters ?? 0 }} 米</span></div>
          <div>已印數量：<span class="font-semibold">{{ totals?.verified ?? 0 }}</span></div>
          <div>未印數量：<span class="font-semibold">{{ pending }}</span></div>
        </div>

        <div>
          <label class="label-base">本次列印數量</label>
          <input type="number"
                 :max="pending"
                 min="1"
                 v-model.number="localCount"
                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
          <p v-if="Number(localCount) > pending" class="text-red-600 text-xs mt-1">不可超過未印數量（{{ pending }}）</p>
        </div>
      </div>

      <div class="mt-5 flex justify-end gap-3">
        <button @click="close" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">取消</button>
        <button @click="confirm" :disabled="disabled" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-md hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed">
          確認列印
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  totals: { type: Object, default: () => ({ total: 0, total_meters: 0, verified: 0, pending: 0 }) },
  loading: { type: Boolean, default: false },
  count: { type: Number, default: 1 },
})
const emit = defineEmits(['update:modelValue', 'update:count', 'submit'])

const pending = computed(() => Math.max(0, Number(props.totals?.total ?? 0) - Number(props.totals?.verified ?? 0)))

const localCount = ref(props.count || 1)
watch(() => props.count, (v) => { localCount.value = v || 1 })
watch(localCount, (v) => emit('update:count', Number(v) || 1))

const disabled = computed(() => {
  const v = Number(localCount.value)
  return !Number.isFinite(v) || v <= 0 || v > pending.value
})

const close = () => emit('update:modelValue', false)
const confirm = () => { if (!disabled.value) emit('submit') }
</script>

<style scoped>
/* 所有通用樣式已移至 resources/css/app.css */
</style>
