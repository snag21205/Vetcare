<script setup>
// defineProps: khai báo props component này nhận
// Mỗi prop có thể khai báo kiểu (String, Number, Boolean...)
defineProps({
    title: String,
    value: [Number, String],  // Chấp nhận cả Number lẫn String
    icon: String,
    color: {
        type: String,
        default: 'blue'        // Giá trị mặc định nếu không truyền vào
    },
    format: {
        type: String,
        default: 'number'      // 'number' hoặc 'currency'
    },
})
</script>

<template>
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4">
        <!-- Icon với màu nền theo prop color -->
        <div :class="`bg-${color}-100`"
            class="w-14 h-14 rounded-full flex items-center justify-center text-2xl flex-shrink-0">
            {{ icon }}
        </div>
        <div>
            <p class="text-sm text-gray-500">{{ title }}</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">
                <!-- Hiển thị theo format: số bình thường hoặc tiền tệ -->
                <template v-if="format === 'currency'">
                    {{ value ? new Intl.NumberFormat('vi-VN').format(value) + '₫' : '0₫' }}
                </template>
                <template v-else>
                    {{ value ?? 0 }}
                </template>
            </p>
        </div>
    </div>
</template>