<script setup>
defineProps({
    name: {
        type: String,
        required: true,
    },
    size: {
        type: [String, Number],
        default: 18,
    },
    class: {
        type: String,
        default: '',
    }
})
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" 
        :width="size" 
        :height="size" 
        viewBox="0 0 24 24" 
        fill="none" 
        stroke="currentColor" 
        stroke-width="1.75" 
        stroke-linecap="round" 
        stroke-linejoin="round" 
        :class="['inline-block shrink-0', $props.class]">
        
        <!-- Dashboard -->
        <g v-if="name === 'dashboard'">
            <rect width="7" height="9" x="3" y="3" rx="1"/>
            <rect width="7" height="5" x="14" y="3" rx="1"/>
            <rect width="7" height="9" x="14" y="12" rx="1"/>
            <rect width="7" height="5" x="3" y="16" rx="1"/>
        </g>

        <!-- Users / Khách hàng -->
        <g v-else-if="name === 'users'">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </g>

        <!-- User / Cá nhân -->
        <g v-else-if="name === 'user'">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
        </g>

        <!-- Pet / Paw -->
        <g v-else-if="name === 'pet'">
            <circle cx="11" cy="4" r="2"/>
            <circle cx="18" cy="8" r="2"/>
            <circle cx="4" cy="8" r="2"/>
            <path d="M12 20c3.5 0 6-2 6-5.5 0-3-2.5-4.5-6-4.5s-6 1.5-6 4.5c0 3.5 2.5 5.5 6 5.5Z"/>
        </g>

        <!-- Calendar / Lịch hẹn -->
        <g v-else-if="name === 'calendar'">
            <path d="M8 2v4"/>
            <path d="M16 2v4"/>
            <rect width="18" height="18" x="3" y="4" rx="2"/>
            <path d="M3 10h18"/>
        </g>

        <!-- Stethoscope / Khám bệnh -->
        <g v-else-if="name === 'stethoscope'">
            <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/>
            <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/>
            <circle cx="20" cy="10" r="2"/>
        </g>

        <!-- Medicine / Thuốc -->
        <g v-else-if="name === 'pill' || name === 'medicine'">
            <path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/>
            <path d="m8.5 8.5 7 7"/>
        </g>

        <!-- Service / Flask / Xét nghiệm -->
        <g v-else-if="name === 'service'">
            <path d="M10 2v7.31L4.2 18A2 2 0 0 0 5.86 21h12.28a2 2 0 0 0 1.66-3L14 9.31V2"/>
            <path d="M8.5 2h7"/>
            <path d="M14 9.3h-4"/>
        </g>

        <!-- Invoice / Receipt / Hóa đơn -->
        <g v-else-if="name === 'invoice' || name === 'receipt'">
            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/>
            <path d="M8 7h8"/>
            <path d="M8 11h8"/>
            <path d="M8 15h5"/>
        </g>

        <!-- Document / Phiếu -->
        <g v-else-if="name === 'document'">
            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" x2="8" y1="13" y2="13"/>
            <line x1="16" x2="8" y1="17" y2="17"/>
            <line x1="10" x2="8" y1="9" y2="9"/>
        </g>

        <!-- Chart / Báo cáo -->
        <g v-else-if="name === 'chart'">
            <path d="M3 3v18h18"/>
            <path d="m19 9-5 5-4-4-3 3"/>
        </g>

        <!-- Search -->
        <g v-else-if="name === 'search'">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.3-4.3"/>
        </g>

        <!-- Plus -->
        <g v-else-if="name === 'plus'">
            <path d="M5 12h14"/>
            <path d="M12 5v14"/>
        </g>

        <!-- Check -->
        <g v-else-if="name === 'check'">
            <polyline points="20 6 9 17 4 12"/>
        </g>

        <!-- Check Circle -->
        <g v-else-if="name === 'check-circle'">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </g>

        <!-- Edit / Pencil -->
        <g v-else-if="name === 'edit'">
            <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
            <path d="m15 5 4 4"/>
        </g>

        <!-- Trash / Delete -->
        <g v-else-if="name === 'trash'">
            <path d="M3 6h18"/>
            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
            <line x1="10" x2="10" y1="11" y2="17"/>
            <line x1="14" x2="14" y1="11" y2="17"/>
        </g>

        <!-- Printer -->
        <g v-else-if="name === 'printer'">
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/>
            <rect width="12" height="8" x="6" y="14" rx="1"/>
        </g>

        <!-- Eye / Chi tiết -->
        <g v-else-if="name === 'eye'">
            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
            <circle cx="12" cy="12" r="3"/>
        </g>

        <!-- Clock / Thời gian -->
        <g v-else-if="name === 'clock'">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </g>

        <!-- Arrow Left -->
        <g v-else-if="name === 'arrow-left'">
            <path d="m12 19-7-7 7-7"/>
            <path d="M19 12H5"/>
        </g>

        <!-- Arrow Right -->
        <g v-else-if="name === 'arrow-right'">
            <path d="M5 12h14"/>
            <path d="m12 5 7 7-7 7"/>
        </g>

        <!-- Chevron Left -->
        <g v-else-if="name === 'chevron-left'">
            <path d="m15 18-6-6 6-6"/>
        </g>

        <!-- Chevron Right -->
        <g v-else-if="name === 'chevron-right'">
            <path d="m9 18 6-6-6-6"/>
        </g>

        <!-- Alert -->
        <g v-else-if="name === 'alert'">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" x2="12" y1="8" y2="12"/>
            <line x1="12" x2="12.01" y1="16" y2="16"/>
        </g>

        <!-- Close / X -->
        <g v-else-if="name === 'x'">
            <path d="M18 6 6 18"/>
            <path d="m6 6 12 12"/>
        </g>

        <!-- Logout -->
        <g v-else-if="name === 'logout'">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" x2="9" y1="12" y2="12"/>
        </g>

        <!-- Bell / Notification -->
        <g v-else-if="name === 'bell'">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
        </g>

        <!-- Check Check / Mark all read -->
        <g v-else-if="name === 'check-check'">
            <path d="M18 6 7 17l-5-5"/>
            <path d="m22 10-7.5 7.5L13 16"/>
        </g>

        <!-- Sparkles / AI -->
        <g v-else-if="name === 'sparkles' || name === 'ai'">
            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
            <path d="M5 3v4"/>
            <path d="M19 17v4"/>
            <path d="M3 5h4"/>
            <path d="M17 19h4"/>
        </g>

        <!-- Bot / Robot -->
        <g v-else-if="name === 'bot'">
            <path d="M12 8V4H8"/>
            <rect width="16" height="12" x="4" y="8" rx="2"/>
            <path d="M2 14h2"/>
            <path d="M20 14h2"/>
            <path d="M15 13v2"/>
            <path d="M9 13v2"/>
        </g>
    </svg>
</template>
