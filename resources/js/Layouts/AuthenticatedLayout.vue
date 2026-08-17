<script setup>
import Sidebar from '@/Components/Sidebar.vue'
// Flash messages (thông báo success/error sau action)
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
// Lấy flash message từ server (nếu có)
const flash = computed(() => page.props.flash ?? {})
</script>

<template>
    <!-- flex: chia layout thành sidebar bên trái + main content bên phải -->
    <div class="flex min-h-screen bg-gray-100">

        <!-- Sidebar bên trái (cố định) -->
        <Sidebar />

        <!-- Main content bên phải -->
        <div class="flex-1 flex flex-col">

            <!-- Flash message (thông báo thành công/lỗi) -->
            <div v-if="flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 m-4 rounded">
                ✅ {{ flash.success }}
            </div>
            <div v-if="flash.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 m-4 rounded">
                ❌ {{ flash.error }}
            </div>

            <!-- slot: đây là nơi nội dung trang (Dashboard.vue, v.v.) được "nhét" vào -->
            <!-- Giống như {{ children }} trong React -->
            <main class="flex-1 p-6">
                <slot />
            </main>

        </div>
    </div>
</template>