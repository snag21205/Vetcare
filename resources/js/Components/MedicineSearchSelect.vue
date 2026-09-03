<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    medicines: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Gõ tên thuốc để tìm nhanh trong kho (vd: Amox, Dexa, Bio-Rumi...)',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:modelValue', 'select', 'clear'])

const containerRef = ref(null)
const inputRef = ref(null)
const searchQuery = ref('')
const isOpen = ref(false)
const highlightedIndex = ref(-1)

const selectedMedicine = computed(() => {
    if (!props.modelValue) return null
    return props.medicines.find(m => String(m.id) === String(props.modelValue)) || null
})

// Chuẩn hóa chuỗi tiếng Việt để tìm kiếm không dấu
function removeVietnameseTones(str) {
    if (!str) return ''
    return str
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D')
        .toLowerCase()
}

const filteredMedicines = computed(() => {
    const q = removeVietnameseTones(searchQuery.value.trim())
    if (!q) {
        return props.medicines.slice(0, 20)
    }
    return props.medicines.filter(m => {
        const nameNorm = removeVietnameseTones(m.name)
        const unitNorm = removeVietnameseTones(m.unit)
        return nameNorm.includes(q) || unitNorm.includes(q)
    }).slice(0, 25)
})

function formatCurrency(val) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}

function formatDate(dateStr) {
    if (!dateStr) return null
    const d = new Date(dateStr)
    return `${d.getMonth() + 1}/${d.getFullYear()}`
}

function openDropdown() {
    if (props.disabled) return
    isOpen.value = true
    highlightedIndex.value = -1
}

function closeDropdown() {
    isOpen.value = false
    highlightedIndex.value = -1
}

function selectMedicine(med) {
    emit('update:modelValue', med.id)
    emit('select', med)
    searchQuery.value = ''
    closeDropdown()
}

function clearSelection() {
    emit('update:modelValue', '')
    emit('clear')
    searchQuery.value = ''
    isOpen.value = true
    nextTick(() => {
        inputRef.value?.focus()
    })
}

function onKeyDown(e) {
    if (!isOpen.value) {
        if (e.key === 'ArrowDown' || e.key === 'Enter') {
            openDropdown()
            e.preventDefault()
        }
        return
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault()
        if (highlightedIndex.value < filteredMedicines.value.length - 1) {
            highlightedIndex.value++
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        if (highlightedIndex.value > 0) {
            highlightedIndex.value--
        }
    } else if (e.key === 'Enter') {
        e.preventDefault()
        if (highlightedIndex.value >= 0 && highlightedIndex.value < filteredMedicines.value.length) {
            selectMedicine(filteredMedicines.value[highlightedIndex.value])
        }
    } else if (e.key === 'Escape') {
        closeDropdown()
    }
}

function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        closeDropdown()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <div ref="containerRef" class="relative w-full text-xs">

        <!-- TRƯỜNG HỢP 1: ĐÃ CHỌN THUỐC -> DẠNG RỘNG RÃI FULL ROW RÕ RÀNG TÊN VÀ THÔNG TIN -->
        <div v-if="selectedMedicine"
            class="min-h-[42px] py-1.5 px-3.5 bg-white border border-slate-200 rounded-xl flex items-center justify-between gap-3 shadow-2xs hover:border-slate-300 transition-colors">
            <div class="flex items-center gap-2.5 min-w-0 flex-1 flex-wrap sm:flex-nowrap">
                <div class="w-6 h-6 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                    <Icon name="pill" size="13" />
                </div>
                <span class="font-bold text-slate-900 truncate text-xs sm:text-sm">{{ selectedMedicine.name }}</span>
                <span class="text-xs text-slate-500 font-medium shrink-0">
                    (Còn: {{ selectedMedicine.stock_quantity }} {{ selectedMedicine.unit }})
                </span>
                <span class="text-xs text-slate-700 font-semibold shrink-0">
                    • {{ formatCurrency(selectedMedicine.sell_price) }}/{{ selectedMedicine.unit }}
                </span>
            </div>

            <!-- Nút đổi thuốc: Chỉ giữ biểu tượng x -->
            <button v-if="!disabled" type="button" @click="clearSelection"
                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors shrink-0 flex items-center justify-center cursor-pointer"
                title="Chọn lại loại thuốc khác">
                <Icon name="x" size="15" />
            </button>
        </div>

        <!-- TRƯỜNG HỢP 2: CHƯA CHỌN THUỐC -> KHUNG TÌM KIẾM AUTOCOMPLETE FULL ROW -->
        <div v-else class="relative">
            <div class="relative">
                <input ref="inputRef" type="text" v-model="searchQuery" @focus="openDropdown" @click="openDropdown"
                    @keydown="onKeyDown" :disabled="disabled" :placeholder="placeholder"
                    class="w-full h-[42px] pl-10 pr-8 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition-all font-medium" />

                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <Icon name="search" size="15" />
                </div>

                <button v-if="searchQuery" type="button" @click="searchQuery = ''"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                    <Icon name="x" size="13" />
                </button>
            </div>

            <!-- FLOATING POPUP DANH SÁCH THUỐC PHÙ HỢP -->
            <div v-if="isOpen"
                class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden max-h-64 overflow-y-auto custom-scrollbar animate-in fade-in zoom-in-95 duration-150">
                <!-- Tiêu đề / Bộ đếm kết quả -->
                <div
                    class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-semibold sticky top-0 z-10">
                    <span>Kết quả tìm kiếm thuốc trong kho ({{ filteredMedicines.length }})</span>
                    <span class="text-[10px] text-slate-400 font-normal">Dùng phím ↑ ↓ Enter để chọn nhanh</span>
                </div>

                <!-- Danh sách thuốc -->
                <div class="p-1 space-y-0.5">
                    <div v-for="(med, idx) in filteredMedicines" :key="med.id" @click="selectMedicine(med)"
                        @mouseenter="highlightedIndex = idx" :class="[
                            'px-3 py-2 rounded-xl cursor-pointer transition-all flex items-center justify-between gap-2',
                            highlightedIndex === idx ? 'bg-sky-50 text-sky-950' : 'hover:bg-slate-50 text-slate-800'
                        ]">
                        <div class="min-w-0 flex items-center gap-2.5">
                            <div
                                class="w-6 h-6 rounded-lg bg-sky-100/70 text-sky-600 flex items-center justify-center shrink-0">
                                <Icon name="pill" size="12" />
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-slate-900 block truncate">{{ med.name }}</span>
                                <div class="text-[11px] text-slate-400 flex items-center gap-2">
                                    <span class="font-semibold text-sky-700">{{ formatCurrency(med.sell_price) }}/{{
                                        med.unit }}</span>
                                    <span v-if="med.expiry_date">• HSD: {{ formatDate(med.expiry_date) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cột tồn kho -->
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                                :class="med.stock_quantity <= (med.low_stock_threshold || 10)
                                    ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                    : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
                                Còn {{ med.stock_quantity }} {{ med.unit }}
                            </span>
                        </div>
                    </div>

                    <!-- Không tìm thấy -->
                    <div v-if="filteredMedicines.length === 0" class="py-6 text-center text-slate-400">
                        <Icon name="search" size="20" class="mx-auto text-slate-300 mb-1" />
                        <p class="text-xs font-semibold">Không tìm thấy thuốc nào khớp với "{{ searchQuery }}"</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Vui lòng kiểm tra lại tên thuốc hoặc vào kho kiểm
                            tra tồn</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
