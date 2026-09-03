<script setup>
import { Link } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 sm:p-6 select-none">
        <div class="mb-6 flex flex-col items-center gap-2 text-center">
            <Link href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-600 flex items-center justify-center text-white shadow-xs">
                    <Icon name="pet" size="22" />
                </div>
                <div class="text-left">
                    <span class="text-lg font-bold text-slate-900 tracking-tight leading-tight block">VetCare</span>
                    <span class="text-[10px] text-sky-600 font-bold uppercase tracking-wider block">Clinic OS</span>
                </div>
            </Link>
        </div>

        <div class="w-full sm:max-w-md bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-sm">
            <slot />
        </div>

        <div class="mt-6 text-center text-xs text-slate-400">
            Hệ thống Quản lý Phòng khám Thú y VetCare © {{ new Date().getFullYear() }}
        </div>
    </div>
</template>
