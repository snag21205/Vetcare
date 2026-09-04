<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const props = defineProps({
    medicine: Object,
})

// Khởi tạo danh sách các lô thuốc
const initialBatches = (props.medicine.batches && props.medicine.batches.length > 0)
    ? props.medicine.batches.map(b => ({
        id: b.id,
        batch_number: b.batch_number || '',
        remaining_quantity: Number(b.remaining_quantity) || 0,
        quantity: Number(b.quantity) || Number(b.remaining_quantity) || 0,
        import_price: Number(b.import_price || props.medicine.import_price || 0),
        expiry_date: b.expiry_date ? String(b.expiry_date).substring(0, 10) : '',
        _delete: false,
    }))
    : [
        {
            id: null,
            batch_number: `LO-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-001`,
            remaining_quantity: Number(props.medicine.stock_quantity) || 0,
            quantity: Number(props.medicine.stock_quantity) || 0,
            import_price: Number(props.medicine.import_price) || 0,
            expiry_date: props.medicine.expiry_date ? String(props.medicine.expiry_date).substring(0, 10) : '',
            _delete: false,
        }
    ]

const form = useForm({
    name:                props.medicine.name,
    unit:                props.medicine.unit,
    stock_quantity:      Number(props.medicine.stock_quantity) || 0,
    import_price:        Number(props.medicine.import_price) || 0,
    sell_price:          Number(props.medicine.sell_price) || 0,
    expiry_date:         props.medicine.expiry_date ? String(props.medicine.expiry_date).substring(0, 10) : '',
    low_stock_threshold: props.medicine.low_stock_threshold ?? 10,
    batches:             initialBatches,
})

// Tự động tính tổng tồn kho từ các lô hợp lệ
const computedTotalStock = computed(() => {
    return form.batches
        .filter(b => !b._delete)
        .reduce((sum, b) => sum + (Number(b.remaining_quantity) || 0), 0)
})

// Xác định hạn sử dụng sớm nhất (FEFO) trong số các lô còn tồn
const activeBatchesSorted = computed(() => {
    return [...form.batches]
        .filter(b => !b._delete && (Number(b.remaining_quantity) || 0) > 0 && b.expiry_date)
        .sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date))
})

const earliestExpiryDate = computed(() => {
    return activeBatchesSorted.value.length > 0 ? activeBatchesSorted.value[0].expiry_date : ''
})

// Đồng bộ tự động giá trị tổng tồn kho và hạn sử dụng gần nhất vào form
watch(computedTotalStock, (newVal) => {
    form.stock_quantity = newVal
}, { immediate: true })

watch(earliestExpiryDate, (newVal) => {
    form.expiry_date = newVal
}, { immediate: true })

function addBatch() {
    form.batches.push({
        id: null,
        batch_number: `LO-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Math.random().toString(36).substring(2, 6).toUpperCase()}`,
        remaining_quantity: 10,
        quantity: 10,
        import_price: Number(form.import_price) || 0,
        expiry_date: '',
        _delete: false,
    })
}

function removeBatch(index) {
    const batch = form.batches[index]
    if (batch.id) {
        batch._delete = true
    } else {
        form.batches.splice(index, 1)
    }
}

function restoreBatch(index) {
    form.batches[index]._delete = false
}

function submit() {
    form.put(route('medicines.update', props.medicine.id))
}

const formatDate = (dateStr) => {
    if (!dateStr) return 'Chưa đặt'
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
    <Head :title="`Sửa thuốc: ${medicine.name}`" />

    <div class="space-y-5 pb-12">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
            <Link :href="route('medicines.index')" class="hover:text-sky-600 transition-colors">Kho thuốc</Link>
            <Icon name="chevron-right" size="12" class="text-slate-400" />
            <span class="text-slate-600 truncate max-w-xs">{{ medicine.name }}</span>
            <Icon name="chevron-right" size="12" class="text-slate-400" />
            <span class="text-slate-900 font-semibold">Chỉnh sửa thông tin & Các lô thuốc</span>
        </nav>

        <form @submit.prevent="submit">
            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Thẻ 1: Thông tin danh mục thuốc -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5 text-xs">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Thông tin cơ bản thuốc</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Tên thuốc, đơn vị tính, giá bán và ngưỡng cảnh báo tồn kho</p>
                            </div>
                            <span class="bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-1 rounded-lg font-bold text-[11px] font-mono">
                                #MED-{{ medicine.id.toString().padStart(4, '0') }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- Tên thuốc -->
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Tên thuốc / Biệt dược <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.name" type="text"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                            </div>

                            <!-- Đơn vị tính & Ngưỡng cảnh báo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">
                                        Đơn vị tính <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="form.unit" type="text"
                                        class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
                                        :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.unit }"
                                        placeholder="viên, lọ, tuýp, vỉ..." />
                                    <p v-if="form.errors.unit" class="text-rose-500 text-[11px] mt-1">{{ form.errors.unit }}</p>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">
                                        Ngưỡng cảnh báo tồn ít <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="form.low_stock_threshold" type="number" min="0"
                                        class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold" />
                                    <p v-if="form.errors.low_stock_threshold" class="text-rose-500 text-[11px] mt-1">{{ form.errors.low_stock_threshold }}</p>
                                </div>
                            </div>

                            <!-- Giá nhập mặc định & Giá bán -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">
                                        Giá nhập mặc định (VNĐ) <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="form.import_price" type="number" min="0" step="100"
                                        class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold" />
                                    <p v-if="form.errors.import_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.import_price }}</p>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">
                                        Giá bán ra cho khách (VNĐ) <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="form.sell_price" type="number" min="0" step="100"
                                        class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold text-sky-700" />
                                    <p v-if="form.errors.sell_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.sell_price }}</p>
                                </div>
                            </div>

                            <!-- Tóm tắt tồn kho & Hạn FEFO -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-slate-50/80 rounded-xl border border-slate-200/70">
                                <div>
                                    <span class="text-slate-500 text-[11px] block">Tổng tồn kho tự động:</span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <strong class="text-sm font-bold text-slate-900 tabular-nums">{{ computedTotalStock }}</strong>
                                        <span class="text-slate-600 font-medium">{{ form.unit }}</span>
                                        <span class="text-[10px] text-sky-700 bg-sky-50 border border-sky-200 px-1.5 py-0.5 rounded font-medium ml-1">
                                            {{ form.batches.filter(b => !b._delete).length }} lô
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Tự động cộng từ số lượng các lô bên dưới</p>
                                </div>
                                <div>
                                    <span class="text-slate-500 text-[11px] block">Hạn dùng xuất trước (FEFO):</span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <strong :class="isExpiringSoon(earliestExpiryDate) ? 'text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded' : 'text-slate-900'"
                                            class="text-xs font-bold tabular-nums">
                                            {{ formatDate(earliestExpiryDate) }}
                                        </strong>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Hạn dùng của lô sẽ được ưu tiên trừ kho trước</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thẻ 2: Quản lý danh sách các Lô thuốc & Hạn sử dụng riêng -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4 text-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <Icon name="pill" size="16" class="text-sky-600" />
                                    <h2 class="text-base font-bold text-slate-900">Danh sách các Lô thuốc & Hạn sử dụng riêng</h2>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Mỗi lô thuốc có mã số, hạn sử dụng và số lượng riêng. Khi kê đơn, hệ thống sẽ tự động trừ lô hết hạn sớm nhất (FEFO).
                                </p>
                            </div>
                            <button type="button" @click="addBatch"
                                class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold px-3 py-1.5 rounded-xl transition-colors cursor-pointer shrink-0 self-start sm:self-auto">
                                <Icon name="plus" size="14" />
                                <span>Thêm lô thuốc mới</span>
                            </button>
                        </div>

                        <!-- Danh sách các lô -->
                        <div class="space-y-3">
                            <div v-for="(batch, idx) in form.batches" :key="idx"
                                class="p-4 rounded-xl border transition-all"
                                :class="batch._delete 
                                    ? 'bg-rose-50/40 border-rose-200 opacity-60' 
                                    : (batch.expiry_date === earliestExpiryDate && Number(batch.remaining_quantity) > 0
                                        ? 'bg-amber-50/30 border-amber-200/80 shadow-2xs' 
                                        : 'bg-slate-50/60 border-slate-200')">
                                
                                <!-- Hàng tiêu đề thẻ lô -->
                                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-200/70 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800 text-xs">Lô #{{ idx + 1 }}</span>
                                        <span v-if="batch.expiry_date === earliestExpiryDate && Number(batch.remaining_quantity) > 0 && !batch._delete"
                                            class="bg-amber-100 text-amber-800 border border-amber-300/80 px-2 py-0.5 rounded-md text-[10px] font-bold inline-flex items-center gap-1">
                                            <Icon name="clock" size="11" />
                                            <span>FEFO: Lô xuất trước</span>
                                        </span>
                                        <span v-if="Number(batch.remaining_quantity) === 0 && !batch._delete"
                                            class="bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md text-[10px] font-medium">
                                            Đã hết hàng
                                        </span>
                                        <span v-if="batch._delete"
                                            class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-md text-[10px] font-bold">
                                            Đánh dấu xóa
                                        </span>
                                    </div>

                                    <!-- Nút xóa hoặc hoàn tác -->
                                    <div>
                                        <button v-if="!batch._delete" type="button" @click="removeBatch(idx)"
                                            class="inline-flex items-center gap-1 text-[11px] text-rose-600 hover:text-rose-800 hover:bg-rose-100/60 px-2 py-1 rounded-lg transition-colors cursor-pointer"
                                            title="Xóa lô thuốc này">
                                            <Icon name="trash" size="12" />
                                            <span>Xóa lô</span>
                                        </button>
                                        <button v-else type="button" @click="restoreBatch(idx)"
                                            class="inline-flex items-center gap-1 text-[11px] text-sky-600 hover:text-sky-800 hover:bg-sky-100/60 px-2 py-1 rounded-lg transition-colors cursor-pointer font-medium"
                                            title="Hoàn tác xóa lô thuốc này">
                                            <Icon name="check" size="12" />
                                            <span>Hoàn tác</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Form nhập chi tiết của lô -->
                                <div v-if="!batch._delete" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <!-- Mã lô -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1 text-[11px]">
                                            Mã số lô
                                        </label>
                                        <input v-model="batch.batch_number" type="text"
                                            class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 font-mono"
                                            placeholder="LO-2026-001" />
                                    </div>

                                    <!-- Hạn sử dụng của riêng lô này -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1 text-[11px]">
                                            Hạn sử dụng của lô <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="batch.expiry_date" type="date"
                                            class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 font-medium"
                                            :class="isExpiringSoon(batch.expiry_date) ? 'border-amber-400 text-amber-900' : ''" />
                                        <span v-if="isExpiringSoon(batch.expiry_date)" class="text-[10px] text-amber-600 font-semibold block mt-0.5">
                                            Sắp hết hạn trong 30 ngày
                                        </span>
                                    </div>

                                    <!-- Số lượng còn tồn -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1 text-[11px]">
                                            Tồn kho lô ({{ form.unit }}) <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="batch.remaining_quantity" type="number" min="0"
                                            class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 tabular-nums font-bold" />
                                    </div>

                                    <!-- Giá nhập lô -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1 text-[11px]">
                                            Giá nhập lô (VNĐ)
                                        </label>
                                        <input v-model="batch.import_price" type="number" min="0" step="100"
                                            class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 tabular-nums" />
                                    </div>
                                </div>

                                <div v-else class="text-[11px] text-rose-700 py-1 font-medium">
                                    Lô này sẽ bị xóa khỏi kho thuốc khi bạn bấm "Lưu thay đổi".
                                </div>
                            </div>

                            <div v-if="form.batches.length === 0" class="text-center py-6 border-2 border-dashed border-slate-200 rounded-xl">
                                <p class="text-slate-500 text-xs">Chưa có lô thuốc nào được khai báo.</p>
                                <button type="button" @click="addBatch"
                                    class="mt-2 text-sky-600 hover:text-sky-700 font-semibold text-xs cursor-pointer">
                                    + Bấm vào đây để thêm lô thuốc đầu tiên
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Nút hành động chính -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi toàn bộ' }}</span>
                        </button>
                        <Link :href="route('medicines.index')"
                            class="px-5 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                            Hủy bỏ
                        </Link>
                    </div>
                </div>

                <!-- Side Information Card (1 col) -->
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
                        <div class="flex items-center gap-2 text-slate-900 font-bold border-b border-slate-100 pb-2.5">
                            <Icon name="medicine" size="16" class="text-sky-600" />
                            <span>Quản lý kho đa lô (FEFO)</span>
                        </div>

                        <div class="space-y-3 text-slate-600 leading-relaxed">
                            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-[11px] text-amber-900 space-y-1">
                                <div class="font-bold flex items-center gap-1 text-amber-800">
                                    <Icon name="clock" size="12" />
                                    <span>Hạn dùng riêng từng lô:</span>
                                </div>
                                <p>
                                    Khi nhập thuốc theo từng đợt, mỗi đợt sẽ có hạn sử dụng khác nhau. Hệ thống VetCare cho phép bạn quản lý và sửa ngày hết hạn cho từng lô một cách độc lập.
                                </p>
                            </div>

                            <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-[11px] text-emerald-900 space-y-1">
                                <div class="font-bold flex items-center gap-1 text-emerald-800">
                                    <Icon name="check" size="12" />
                                    <span>Quy tắc xuất kho FEFO:</span>
                                </div>
                                <p>
                                    Khi bác sĩ lập phiếu khám và kê đơn, phần mềm sẽ tự động lấy thuốc từ lô có <strong>hạn sử dụng sớm nhất</strong> trừ trước, tránh để thuốc quá hạn gây thất thoát.
                                </p>
                            </div>

                            <div class="space-y-2 pt-2 border-t border-slate-100 text-[11px]">
                                <p><strong>Mã thuốc:</strong> #MED-{{ medicine.id.toString().padStart(4, '0') }}</p>
                                <p><strong>Số lô đang lưu:</strong> {{ form.batches.filter(b => !b._delete).length }} lô</p>
                                <p><strong>Lần cập nhật cuối:</strong> {{ new Date(medicine.updated_at).toLocaleDateString('vi-VN') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</template>
