<script setup>
import Sidebar from '@/Components/Sidebar.vue'
import Icon from '@/Components/Icon.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
</script>

<template>
    <div class="flex min-h-screen bg-[#F9FAFB] text-zinc-900 font-sans antialiased selection:bg-indigo-600 selection:text-white">

        <!-- Sidebar bên trái -->
        <Sidebar />

        <!-- Khu vực nội dung chính bên phải -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">

            <!-- Flash messages thông báo -->
            <div v-if="flash.success" class="mx-6 mt-6 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center justify-between text-xs font-medium shadow-sm animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="flex items-center gap-2">
                    <Icon name="check-circle" size="16" class="text-emerald-600" />
                    <span>{{ flash.success }}</span>
                </div>
            </div>

            <div v-if="flash.error" class="mx-6 mt-6 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg flex items-center justify-between text-xs font-medium shadow-sm animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="flex items-center gap-2">
                    <Icon name="alert" size="16" class="text-rose-600" />
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <!-- Main Content slot -->
            <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>

        </div>
    </div>
</template>