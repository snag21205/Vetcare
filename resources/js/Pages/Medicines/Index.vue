<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
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
            only: ['medicines', 'stats', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })

// Modal Nhập kho
const showImportModal = ref(false)
const selectedMedicine = ref(null)

const importForm = useForm({
    quantity:       10,
    import_price:   0,
    batch_number:   '',
    expiry_date:    '',
    create_voucher: true,
    note:           '',
})

function openImportModal(med) {
    selectedMedicine.value = med
    importForm.quantity = 10
    importForm.import_price = Number(med.import_price)
    importForm.batch_number = `LO-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Math.random().toString(36).substring(2, 6).toUpperCase()}`
    importForm.expiry_date = ''
    importForm.create_voucher = true
    importForm.note = ''
    showImportModal.value = true
}

function submitImport() {
    if (!selectedMedicine.value) return
    importForm.post(route('medicines.import', selectedMedicine.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showImportModal.value = false
        },
    })
}

// Modal Xem danh sách các lô thuốc
const showBatchesModal = ref(false)
const batchViewingMedicine = ref(null)

function openBatchesModal(med) {
    batchViewingMedicine.value = med
    showBatchesModal.value = true
}

function deleteMedicine(id, name) {
    if (!confirm(`Bạn chắc chắn muốn xóa thuốc "${name}" khỏi danh mục kho?`)) return
    router.delete(route('medicines.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['medicines', 'stats', 'flash'],
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
        <div class="space-y-4">
            <!-- Page Header (Cố định ở đầu) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quản lý Kho thuốc & Vật tư</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Theo dõi tồn kho theo từng lô (FEFO), giá nhập/bán, hạn sử dụng và nhập kho</p>
                </div>
                <Link :href="route('medicines.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Thêm thuốc mới</span>
                </Link>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <StatCard title="Tổng danh mục thuốc" :value="stats.total" icon="pill" color="sky" />
                <StatCard title="Thuốc sắp hết kho" :value="stats.low_stock" icon="alert" color="red" />
                <StatCard title="Sắp hết hạn (≤ 30 ngày)" :value="stats.expiring" icon="clock" color="yellow" />
            </div>

            <!-- Filter & Search Bar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon name="search" size="15" />
                    </div>
                    <input v-model="search" type="text" placeholder="Tìm theo tên thuốc, quy cách..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50/60 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                </div>

                <!-- Filter tabs -->
                <div class="flex items-center bg-slate-100 p-1 rounded-lg border border-slate-200 text-xs shrink-0 self-start sm:self-auto">
                    <button @click="filter = 'all'"
                        class="px-3 py-1.5 rounded-md font-medium transition-colors cursor-pointer"
                        :class="filter === 'all' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900'">
                        Tất cả ({{ stats.total }})
                    </button>
                    <button @click="filter = 'low_stock'"
                        class="px-3 py-1.5 rounded-md font-medium transition-colors cursor-pointer"
                        :class="filter === 'low_stock' ? 'bg-white text-rose-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-rose-700'">
                        Sắp hết kho ({{ stats.low_stock }})
                    </button>
                    <button @click="filter = 'expiring'"
                        class="px-3 py-1.5 rounded-md font-medium transition-colors cursor-pointer"
                        :class="filter === 'expiring' ? 'bg-white text-amber-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-amber-700'">
                        Sắp hết hạn ({{ stats.expiring }})
                    </button>
                </div>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong, cố định chiều cao bảng) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-360px)] min-h-[380px]">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs text-left min-w-[700px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Tên thuốc</th>
                                <th class="px-5 py-3.5 text-center">Tồn kho & Lô</th>
                                <th class="px-5 py-3.5 text-right">Giá nhập</th>
                                <th class="px-5 py-3.5 text-right">Giá bán</th>
                                <th class="px-5 py-3.5">Hạn dùng (FEFO)</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="med in medicines.data" :key="med.id" class="hover:bg-slate-50/80 transition-colors">
                                <!-- Tên thuốc -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100 shrink-0">
                                            <Icon name="pill" size="15" />
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 block truncate">{{ med.name }}</span>
                                            <p class="text-[11px] text-slate-400">Đơn vị: {{ med.unit }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tồn kho & Lô -->
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span v-if="med.stock_quantity <= med.low_stock_threshold"
                                            class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-bold text-[11px] tabular-nums">
                                            <Icon name="alert" size="11" />
                                            {{ med.stock_quantity }} {{ med.unit }}
                                        </span>
                                        <span v-else class="text-slate-900 font-bold tabular-nums">
                                            {{ med.stock_quantity }} {{ med.unit }}
                                        </span>

                                        <!-- Nút xem các lô -->
                                        <button v-if="med.batches && med.batches.length > 0"
                                            @click="openBatchesModal(med)"
                                            class="text-[10px] bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 px-2 py-0.5 rounded-md transition-colors font-semibold cursor-pointer"
                                            title="Bấm để xem chi tiết các lô thuốc">
                                            {{ med.batches.length }} lô
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5 tabular-nums">Ngưỡng: {{ med.low_stock_threshold }}</p>
                                </td>

                                <!-- Giá nhập -->
                                <td class="px-5 py-3.5 text-right font-medium text-slate-600 tabular-nums">
                                    {{ formatCurrency(med.import_price) }}
                                </td>

                                <!-- Giá bán -->
                                <td class="px-5 py-3.5 text-right font-bold text-slate-900 tabular-nums">
                                    {{ formatCurrency(med.sell_price) }}
                                </td>

                                <!-- Hạn sử dụng -->
                                <td class="px-5 py-3.5">
                                    <div class="space-y-0.5">
                                        <span v-if="isExpiringSoon(med.expiry_date)"
                                            class="inline-block text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded font-semibold text-[11px] tabular-nums">
                                            {{ formatDate(med.expiry_date) }}
                                        </span>
                                        <span v-else class="text-slate-700 font-medium tabular-nums">
                                            {{ formatDate(med.expiry_date) }}
                                        </span>
                                        <span v-if="med.batches && med.batches.length > 1" class="block text-[10px] text-slate-400 italic">
                                            (Lô hết hạn sớm nhất)
                                        </span>
                                    </div>
                                </td>

                                <!-- Thao tác -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Nút Nhập kho -->
                                        <button @click="openImportModal(med)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-lg font-semibold transition-colors cursor-pointer">
                                            <Icon name="plus" size="12" />
                                            <span>Nhập kho</span>
                                        </button>

                                        <!-- Nút Sửa -->
                                        <Link :href="route('medicines.edit', med.id)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                            <Icon name="edit" size="13" />
                                            <span>Sửa</span>
                                        </Link>

                                        <!-- Nút Xóa -->
                                        <button @click="deleteMedicine(med.id, med.name)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition-colors font-semibold cursor-pointer">
                                            <Icon name="trash" size="13" />
                                            <span>Xóa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="medicines.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="pill" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Không tìm thấy thuốc nào phù hợp</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (Cố định ở đáy thẻ bảng) -->
                <div v-if="medicines.last_page > 1" class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ medicines.current_page }}</span> / {{ medicines.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="medicines.prev_page_url" :href="medicines.prev_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="medicines.next_page_url" :href="medicines.next_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <span>Sau</span>
                            <Icon name="chevron-right" size="14" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Nhập kho thuốc theo Lô mới -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4 text-xs animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                            <Icon name="plus" size="16" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Nhập lô thuốc mới</h3>
                            <p class="text-[11px] text-slate-500">Bổ sung hàng vào kho dược</p>
                        </div>
                    </div>
                    <button @click="showImportModal = false" class="text-slate-400 hover:text-slate-600">
                        <Icon name="x" size="18" />
                    </button>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                    <p class="font-bold text-slate-900 text-sm">{{ selectedMedicine?.name }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Tồn kho hiện tại: <strong class="text-slate-900 font-bold tabular-nums">{{ selectedMedicine?.stock_quantity }} {{ selectedMedicine?.unit }}</strong>
                    </p>
                </div>

                <form @submit.prevent="submitImport" class="space-y-3.5">
                    <!-- Mã số lô nhập -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Mã số lô nhập <span class="text-slate-400 font-normal">(tự động sinh nếu để trống)</span>
                        </label>
                        <input v-model="importForm.batch_number" type="text"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 font-mono"
                            placeholder="LO-202608-001" />
                    </div>

                    <!-- Số lượng nhập -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Số lượng nhập đợt này ({{ selectedMedicine?.unit }}) <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="importForm.quantity" type="number" min="1"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 tabular-nums font-semibold" />
                        <p v-if="importForm.errors.quantity" class="text-rose-500 text-[11px] mt-1">{{ importForm.errors.quantity }}</p>
                    </div>

                    <!-- Giá nhập đợt này -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Đơn giá nhập đợt này (VNĐ) <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="importForm.import_price" type="number" min="0" step="100"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 tabular-nums font-semibold" />
                        <p class="text-[11px] text-slate-500 mt-1">
                            Thành tiền lô nhập: <strong class="text-slate-900 font-bold tabular-nums">{{ formatCurrency((Number(importForm.quantity) || 0) * (Number(importForm.import_price) || 0)) }}</strong>
                        </p>
                    </div>

                    <!-- Hạn sử dụng lô mới -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Hạn sử dụng của riêng lô này
                        </label>
                        <input v-model="importForm.expiry_date" type="date"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500" />
                        <p class="text-[10px] text-slate-400 mt-1 italic">
                            * Hạn sử dụng này được lưu riêng cho lô này, không ghi đè hạn dùng của các lô cũ.
                        </p>
                    </div>

                    <!-- Tự động sinh phiếu chi -->
                    <div class="p-3 bg-emerald-50/60 border border-emerald-200 rounded-xl">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" v-model="importForm.create_voucher"
                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mt-0.5" />
                            <div>
                                <span class="font-semibold text-emerald-900">Tự động tạo Phiếu chi tương ứng</span>
                                <p class="text-[11px] text-emerald-700 mt-0.5">
                                    Hệ thống sẽ ghi nhận phiếu chi <strong>{{ formatCurrency((Number(importForm.quantity) || 0) * (Number(importForm.import_price) || 0)) }}</strong> vào sổ quỹ.
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="showImportModal = false"
                            class="px-4 py-2 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 font-semibold transition-colors">
                            Hủy
                        </button>
                        <button type="submit" :disabled="importForm.processing"
                            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold px-4 py-2 rounded-xl shadow-xs transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ importForm.processing ? 'Đang nhập...' : 'Xác nhận nhập lô' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Xem chi tiết danh sách các Lô thuốc (FEFO) -->
        <div v-if="showBatchesModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 text-xs animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                            <Icon name="pill" size="16" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Chi tiết các Lô thuốc đang còn tồn</h3>
                            <p class="text-[11px] text-slate-500">{{ batchViewingMedicine?.name }} (Đơn vị: {{ batchViewingMedicine?.unit }})</p>
                        </div>
                    </div>
                    <button @click="showBatchesModal = false" class="text-slate-400 hover:text-slate-600">
                        <Icon name="x" size="18" />
                    </button>
                </div>

                <!-- Batches list -->
                <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                    <div v-for="b in batchViewingMedicine?.batches" :key="b.id"
                        class="p-3 bg-slate-50/80 rounded-xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-slate-900 text-xs">
                                {{ b.batch_number || ('Lô #' + b.id) }}
                            </span>
                            <span class="text-[11px] font-bold text-sky-700 bg-white border border-slate-200 px-2.5 py-0.5 rounded-full tabular-nums shadow-2xs">
                                Còn: {{ b.remaining_quantity }} / {{ b.quantity }} {{ batchViewingMedicine?.unit }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                            <div>
                                <span class="text-slate-400">Hạn sử dụng: </span>
                                <strong :class="isExpiringSoon(b.expiry_date) ? 'text-rose-600' : 'text-slate-900'" class="tabular-nums">
                                    {{ formatDate(b.expiry_date) }}
                                </strong>
                            </div>
                            <div>
                                <span class="text-slate-400">Giá nhập: </span>
                                <span class="tabular-nums font-semibold">{{ formatCurrency(b.import_price) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="!batchViewingMedicine?.batches || batchViewingMedicine?.batches.length === 0" class="py-8 text-center text-slate-400">
                        Chưa có lô thuốc nào còn tồn kho.
                    </div>
                </div>

                <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-100 text-[11px] text-sky-900">
                    💡 <strong>Cơ chế xuất kho FEFO:</strong> Khi Bác sĩ kê đơn thuốc, hệ thống sẽ tự động trừ số lượng từ lô có hạn dùng gần nhất trước để tránh thuốc bị quá hạn.
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="showBatchesModal = false"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-semibold transition-colors">
                        Đóng
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
