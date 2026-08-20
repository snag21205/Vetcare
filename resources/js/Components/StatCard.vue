<script setup>
import Icon from '@/Components/Icon.vue'

const props = defineProps({
    title: String,
    value: [Number, String],
    icon: {
        type: String,
        default: 'chart'
    },
    color: {
        type: String,
        default: 'indigo'
    },
    format: {
        type: String,
        default: 'number'
    },
})

const colorStyles = {
    indigo: 'bg-indigo-50 text-indigo-600 border-indigo-100',
    blue:   'bg-sky-50 text-sky-600 border-sky-100',
    green:  'bg-emerald-50 text-emerald-600 border-emerald-100',
    yellow: 'bg-amber-50 text-amber-600 border-amber-100',
    red:    'bg-rose-50 text-rose-600 border-rose-100',
}
</script>

<template>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 flex items-center gap-3.5">
        <div :class="colorStyles[color] ?? colorStyles.indigo"
            class="w-10 h-10 rounded-lg flex items-center justify-center border shrink-0">
            <Icon :name="icon" size="18" />
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-medium text-gray-500 truncate uppercase font-mono">{{ title }}</p>
            <p class="text-lg font-bold font-mono text-gray-900 mt-0.5 truncate">
                <template v-if="format === 'currency'">
                    {{ value ? new Intl.NumberFormat('vi-VN').format(value) + ' ₫' : '0 ₫' }}
                </template>
                <template v-else>
                    {{ value ?? 0 }}
                </template>
            </p>
        </div>
    </div>
</template>