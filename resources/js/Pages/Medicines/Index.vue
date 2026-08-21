<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import StatCard from '@/Components/StatCard.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    medicines: Object,
    stats: Object,
    filters: Object,
})

const search = ref(props.filters.search ?? '')
const filter = ref(props.filters.filter ?? 'all')

let searchTimeout = null
watch([search, filter], ([newSearch, newFilter]) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('medicines.index'), {
            search: newSearch,
            filter: newFilter,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['medicines', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })

function deleteMedicine(id, name) {
    if (!confirm(`Bạn chắc chắn muốn xóa thuốc "${name}"?`)) return
    router.delete(route('medicines.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['medicines', 'stats', 'flash'],
    })
}

// Modal Nhập kho
const showImportModal = ref(false)
const selectedMedicine = ref(null)

const importForm = useForm({
    quantity: 10,
    import_price: 0,
    expiry_date: '',
    create_voucher: true,
    note: '',
})

function openImportModal(med) {
    selectedMedicine.value = med
    importForm.import_price = Number(med.import_price)
    importForm.expiry_date = med.expiry_date ? med.expiry_date.substring(0, 10) : ''
    importForm.quantity = 10
    importForm.create_voucher = true
    importForm.note = ''
    showImportModal.value = true
}

function submitImport() {
    if (!selectedMedicine.value) return
    importForm.post(route('medicines.import', selectedMedicine.value.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showImportModal.value = false
        }
    })
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}

const isExpiringSoon = (dateStr) => {
    if (!dateStr) return false
    const diff = (new Date(dateStr) - new Date()) / (1000 * 60 * 60 * 24)
    return diff <= 30
}
</script>

<template>
    <Head title="Kho thuốc & Vật tư" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Quản lý Kho thuốc & Vật tư</h1>
                <p class="text-xs text-gray-500 mt-0.5">Theo dõi tồn kho, giá nhập bán, hạn sử dụng và nhập kho</p>
            </div>
            <Link :href="route('medicines.create')"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors">
                <Icon name="plus" size="16" />
                <span>Thêm thuốc mới</span>
            </Link>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <StatCard title="Tổng danh mục thuốc" :value="stats.total" icon="pill" color="indigo" />
            <StatCard title="Thuốc sắp hết kho" :value="stats.low_stock" icon="alert" color="red" />
            <StatCard title="Sắp hết hạn (≤ 30 ngày)" :value="stats.expiring" icon="clock" color="yellow" />
        </div>

        <!-- Filter & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <!-- Search -->
            <div class="relative w-full sm:max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <Icon name="search" size="15" />
                </div>
                <input v-model="search" type="text" placeholder="Tìm theo tên thuốc, quy cách..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-sm" />
            </div>

            <!-- Filter tabs -->
            <div class="flex items-center bg-gray-100 p-1 rounded-lg border border-gray-200 text-xs">
                <button @click="filter = 'all'"
                    class="px-3 py-1.5 rounded-md font-medium transition-colors"
                    :class="filter === 'all' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                    Tất cả ({{ stats.total }})
                </button>
                <button @click="filter = 'low_stock'"
                    class="px-3 py-1.5 rounded-md font-medium transition-colors"
                    :class="filter === 'low_stock' ? 'bg-white text-rose-700 shadow-xs' : 'text-gray-600 hover:text-rose-700'">
                    Sắp hết kho ({{ stats.low_stock }})
                </button>
                <button @click="filter = 'expiring'"
                    class="px-3 py-1.5 rounded-md font-medium transition-colors"
                    :class="filter === 'expiring' ? 'bg-white text-amber-700 shadow-xs' : 'text-gray-600 hover:text-amber-700'">
                    Sắp hết hạn ({{ stats.expiring }})
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50/75 text-gray-500 font-mono uppercase text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Tên thuốc</th>
                            <th class="px-5 py-3 font-semibold text-center">Tồn kho</th>
                            <th class="px-5 py-3 font-semibold text-right">Giá nhập</th>
                            <th class="px-5 py-3 font-semibold text-right">Giá bán</th>
                            <th class="px-5 py-3 font-semibold">Hạn sử dụng</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="med in medicines.data" :key="med.id" class="hover:bg-gray-50/80 transition-colors">
                            <!-- Tên thuốc -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shrink-0">
                                        <Icon name="pill" size="14" />
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-900">{{ med.name }}</span>
                                        <p class="text-[11px] text-gray-400 font-mono">Đơn vị: {{ med.unit }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Tồn kho -->
                            <td class="px-5 py-3.5 text-center font-mono">
                                <span v-if="med.stock_quantity <= med.low_stock_threshold"
                                    class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-bold">
                                    <Icon name="alert" size="12" />
                                    {{ med.stock_quantity }} {{ med.unit }}
                                </span>
                                <span v-else class="text-gray-900 font-semibold">
                                    {{ med.stock_quantity }} {{ med.unit }}
                                </span>
                                <p class="text-[10px] text-gray-400 mt-0.5">Ngưỡng: {{ med.low_stock_threshold }}</p>
                            </td>

                            <!-- Giá nhập -->
                            <td class="px-5 py-3.5 text-right font-mono text-gray-600">
                                {{ formatCurrency(med.import_price) }}
                            </td>

                            <!-- Giá bán -->
                            <td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-900">
                                {{ formatCurrency(med.sell_price) }}
                            </td>

                            <!-- Hạn sử dụng -->
                            <td class="px-5 py-3.5 font-mono">
                                <span v-if="isExpiringSoon(med.expiry_date)"
                                    class="text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded font-semibold text-[11px]">
                                    {{ formatDate(med.expiry_date) }}
                                </span>
                                <span v-else class="text-gray-600">
                                    {{ formatDate(med.expiry_date) }}
                                </span>
                            </td>

                            <!-- Thao tác -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Nút Nhập kho -->
                                    <button @click="openImportModal(med)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded font-medium transition-colors">
                                        <Icon name="plus" size="12" />
                                        <span>Nhập kho</span>
                                    </button>

                                    <!-- Nút Sửa -->
                                    <Link :href="route('medicines.edit', med.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="edit" size="13" />
                                        <span>Sửa</span>
                                    </Link>

                                    <!-- Nút Xóa -->
                                    <button @click="deleteMedicine(med.id, med.name)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="trash" size="13" />
                                        <span>Xóa</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="medicines.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="pill" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Không tìm thấy thuốc nào phù hợp</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="medicines.last_page > 1" class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ medicines.current_page }} / {{ medicines.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="medicines.prev_page_url" :href="medicines.prev_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="medicines.next_page_url" :href="medicines.next_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <span>Sau</span>
                        <Icon name="chevron-right" size="14" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modal Nhập kho thuốc -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-lg border border-gray-200 shadow-xl max-w-md w-full p-6 space-y-4 text-xs animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                            <Icon name="plus" size="15" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Nhập kho thuốc</h3>
                    </div>
                    <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600">
                        <Icon name="x" size="16" />
                    </button>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <p class="font-bold text-gray-900 text-sm">{{ selectedMedicine?.name }}</p>
                    <p class="text-[11px] text-gray-500 font-mono mt-0.5">
                        Tồn kho hiện tại: <strong class="text-gray-800">{{ selectedMedicine?.stock_quantity }} {{ selectedMedicine?.unit }}</strong>
                    </p>
                </div>

                <form @submit.prevent="submitImport" class="space-y-3.5">
                    <!-- Số lượng nhập -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Số lượng nhập thêm ({{ selectedMedicine?.unit }}) <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="importForm.quantity" type="number" min="1"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono" />
                        <p v-if="importForm.errors.quantity" class="text-rose-500 text-[11px] mt-1">{{ importForm.errors.quantity }}</p>
                    </div>

                    <!-- Giá nhập đợt này -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Đơn giá nhập đợt này (VNĐ) <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="importForm.import_price" type="number" min="0" step="100"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono" />
                        <p class="text-[11px] text-gray-500 font-mono mt-1">
                            Thành tiền lô nhập: <strong>{{ formatCurrency((Number(importForm.quantity) || 0) * (Number(importForm.import_price) || 0)) }}</strong>
                        </p>
                    </div>

                    <!-- Hạn sử dụng lô mới -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Hạn sử dụng lô mới</label>
                        <input v-model="importForm.expiry_date" type="date"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono" />
                    </div>

                    <!-- Tự động sinh phiếu chi -->
                    <div class="p-3 bg-emerald-50/60 border border-emerald-200 rounded-lg">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" v-model="importForm.create_voucher"
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mt-0.5" />
                            <div>
                                <span class="font-semibold text-emerald-900">Tự động tạo Phiếu chi tương ứng</span>
                                <p class="text-[11px] text-emerald-700 mt-0.5">
                                    Hệ thống sẽ ghi nhận phiếu chi <strong>{{ formatCurrency((Number(importForm.quantity) || 0) * (Number(importForm.import_price) || 0)) }}</strong> vào mục "Nhập kho thuốc".
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="showImportModal = false"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                            Hủy
                        </button>
                        <button type="submit" :disabled="importForm.processing"
                            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold px-4 py-2 rounded-lg shadow-sm transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ importForm.processing ? 'Đang nhập...' : 'Xác nhận nhập kho' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
