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
        default: 'sky'
    },
    format: {
        type: String,
        default: 'number'
    },
})

const colorStyles = {
    sky:    'bg-sky-50 text-sky-600 border-sky-200/80',
    indigo: 'bg-indigo-50 text-indigo-600 border-indigo-200/80',
    blue:   'bg-blue-50 text-blue-600 border-blue-200/80',
    green:  'bg-emerald-50 text-emerald-600 border-emerald-200/80',
    yellow: 'bg-amber-50 text-amber-600 border-amber-200/80',
    red:    'bg-rose-50 text-rose-600 border-rose-200/80',
}
</script>

<template>
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 flex items-center gap-3.5 hover:border-slate-300 transition-colors">
        <div :class="colorStyles[color] ?? colorStyles.sky"
            class="w-11 h-11 rounded-xl flex items-center justify-center border shrink-0">
            <Icon :name="icon" size="20" />
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-semibold text-slate-500 truncate uppercase tracking-wider">{{ title }}</p>
            <p class="text-xl font-bold tracking-tight text-slate-900 mt-0.5 truncate tabular-nums">
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