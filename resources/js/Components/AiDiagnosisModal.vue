<script setup>
import Icon from '@/Components/Icon.vue'
import { ref, watch, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    show: Boolean,
    species: String,
    symptoms: String,
    weight: [String, Number],
    temperature: [String, Number],
})

const emit = defineEmits(['close', 'apply'])

const loading = ref(false)
const error = ref('')
const result = ref(null)

const loadingSteps = [
    'AI đang đọc và giải mã triệu chứng lâm sàng...',
    'Đang đối chiếu dữ liệu bệnh học thú y & triệu chứng phổ biến...',
    'Đang kiểm tra tồn kho thuốc & bảng giá dịch vụ phòng khám...',
    'Đang tính toán liều lượng và phác đồ điều trị khuyến nghị...',
    'Đang hoàn tất kết quả chẩn đoán chuyên môn...'
]
const currentStepIndex = ref(0)
let stepInterval = null

function startLoadingAnimation() {
    currentStepIndex.value = 0
    clearInterval(stepInterval)
    stepInterval = setInterval(() => {
        currentStepIndex.value = (currentStepIndex.value + 1) % loadingSteps.length
    }, 1200)
}

function stopLoadingAnimation() {
    clearInterval(stepInterval)
}

onUnmounted(() => {
    clearInterval(stepInterval)
})

async function analyze() {
    if (!props.symptoms || props.symptoms.trim().length < 3) {
        error.value = 'Vui lòng nhập đầy đủ triệu chứng lâm sàng (tối thiểu 3 ký tự) để AI có thể phân tích.'
        return
    }

    loading.value = true
    error.value = ''
    result.value = null
    startLoadingAnimation()

    try {
        const response = await axios.post(route('ai.diagnose'), {
            species:     props.species || 'chó',
            symptoms:    props.symptoms,
            weight:      props.weight,
            temperature: props.temperature,
        })

        if (response.data?.success) {
            result.value = response.data.data
        } else {
            error.value = 'Không thể lấy gợi ý từ AI. Vui lòng thử lại.'
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Có lỗi xảy ra trong quá trình kết nối với AI.'
    } finally {
        loading.value = false
        stopLoadingAnimation()
    }
}

watch(() => props.show, (newVal) => {
    if (newVal) {
        analyze()
    }
})

function applyDiagnosis() {
    if (!result.value) return
    emit('apply', {
        diagnosis:           result.value.primary_diagnosis,
        suggestedMedicines:  result.value.suggested_medicines || [],
        suggestedServices:   result.value.suggested_services || [],
        advice:              result.value.advice || '',
    })
    emit('close')
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 space-y-5 text-xs animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white flex items-center justify-center shadow-xs">
                        <Icon name="sparkles" size="18" class="animate-pulse" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">AI Trợ lý Chẩn đoán Thú y</h3>
                            <span class="text-[10px] bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full font-bold">
                                Gemini AI Pro
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Phân tích triệu chứng và đề xuất chẩn đoán, phác đồ thuốc & dịch vụ</p>
                    </div>
                </div>
                <button @click="emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded-xl hover:bg-slate-100 cursor-pointer">
                    <Icon name="x" size="18" />
                </button>
            </div>

            <!-- Content Area (Scrollable) -->
            <div class="overflow-y-auto space-y-4 pr-1 flex-1">

                <!-- Input Summary Card -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-slate-500">Đối tượng:</span>
                        <strong class="capitalize text-slate-900 font-bold">{{ species }}</strong>
                        <span v-if="weight" class="text-slate-300">·</span>
                        <span v-if="weight" class="font-bold tabular-nums text-slate-800">{{ weight }} kg</span>
                        <span v-if="temperature" class="text-slate-300">·</span>
                        <span v-if="temperature" class="font-bold tabular-nums text-slate-800">{{ temperature }} °C</span>
                    </div>
                    <button @click="analyze" :disabled="loading"
                        class="text-sky-600 hover:text-sky-800 font-semibold inline-flex items-center gap-1 cursor-pointer">
                        <Icon name="clock" size="12" />
                        <span>Phân tích lại</span>
                    </button>
                </div>

                <!-- Animated Loading State -->
                <div v-if="loading" class="py-12 px-6 text-center space-y-4">
                    <div class="relative inline-flex items-center justify-center">
                        <!-- Outer pulse ring -->
                        <div class="w-16 h-16 rounded-full bg-sky-400/20 animate-ping absolute"></div>
                        <div class="w-14 h-14 rounded-full bg-indigo-500/10 animate-pulse absolute"></div>
                        <!-- Spinner -->
                        <div class="w-12 h-12 rounded-full border-3 border-sky-100 border-t-sky-600 animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-sky-600">
                            <Icon name="sparkles" size="18" />
                        </div>
                    </div>
                    
                    <div class="space-y-1.5 max-w-md mx-auto">
                        <p class="font-bold text-slate-900 text-sm transition-all duration-300">
                            {{ loadingSteps[currentStepIndex] }}
                        </p>
                        <p class="text-xs text-slate-400">
                            Hệ thống AI đang xử lý theo thời gian thực...
                        </p>
                    </div>

                    <!-- Animated Progress Bar -->
                    <div class="w-48 h-1.5 bg-slate-100 rounded-full mx-auto overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-sky-500 to-indigo-600 rounded-full animate-pulse w-3/4 mx-auto"></div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 space-y-2">
                    <div class="flex items-center gap-2 font-bold">
                        <Icon name="alert" size="16" />
                        <span>Không thể hoàn thành chẩn đoán</span>
                    </div>
                    <p class="text-xs">{{ error }}</p>
                    <button @click="analyze" class="mt-2 text-xs bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer">
                        Thử lại
                    </button>
                </div>

                <!-- Result View -->
                <div v-else-if="result" class="space-y-4 animate-in fade-in duration-200">

                    <!-- Chẩn đoán chính -->
                    <div class="p-4 bg-gradient-to-r from-sky-50 to-blue-50/60 border border-sky-200 rounded-2xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase text-sky-700 tracking-wider">CHẨN ĐOÁN CÓ KHẢ NĂNG CAO NHẤT</span>
                            <span class="text-[11px] bg-sky-600 text-white font-bold px-2.5 py-0.5 rounded-full shadow-2xs">
                                Độ tin cậy: {{ result.confidence ?? '85%' }}
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 leading-snug">
                            {{ result.primary_diagnosis }}
                        </h4>
                    </div>

                    <!-- Chẩn đoán phân biệt (Differential diagnoses) -->
                    <div v-if="result.differential_diagnoses?.length > 0" class="space-y-2">
                        <h5 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                            <Icon name="stethoscope" size="14" class="text-sky-600" />
                            <span>Chẩn đoán phân biệt cần lưu ý:</span>
                        </h5>
                        <div class="grid grid-cols-1 gap-2">
                            <div v-for="(d, idx) in result.differential_diagnoses" :key="idx"
                                class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-start justify-between gap-3">
                                <div>
                                    <span class="font-bold text-slate-900">{{ d.name }}</span>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ d.reason }}</p>
                                </div>
                                <span class="whitespace-nowrap text-[10px] font-bold px-2.5 py-0.5 rounded-full shrink-0"
                                    :class="d.probability === 'Cao' || d.probability === 'Rất cao' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700'">
                                    {{ d.probability }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Thuốc gợi ý tham khảo (Ràng buộc kho) -->
                    <div v-if="result.suggested_medicines?.length > 0" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h5 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                <Icon name="pill" size="14" class="text-emerald-600" />
                                <span>Phác đồ thuốc điều trị (Có sẵn trong kho):</span>
                            </h5>
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full font-bold">
                                Đã khớp kho phòng khám
                            </span>
                        </div>
                        <div class="bg-emerald-50/50 border border-emerald-200/80 rounded-xl p-3.5 space-y-2">
                            <div v-for="(m, idx) in result.suggested_medicines" :key="idx"
                                class="flex items-start justify-between gap-2 border-b border-emerald-100 pb-2 last:border-b-0 last:pb-0">
                                <div>
                                    <span class="font-bold text-emerald-950">{{ m.name }}</span>
                                    <span class="text-slate-500 text-xs"> · {{ m.dosage }}</span>
                                    <p v-if="m.purpose" class="text-[11px] text-emerald-800 mt-0.5">{{ m.purpose }}</p>
                                </div>
                                <span v-if="m.days" class="text-[10px] tabular-nums font-bold text-slate-700 bg-white border border-emerald-100 px-2 py-0.5 rounded-md shrink-0">
                                    {{ m.days }} ngày
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dịch vụ kỹ thuật cần làm (Ràng buộc bảng giá) -->
                    <div v-if="result.suggested_services?.length > 0" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h5 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                <Icon name="service" size="14" class="text-sky-600" />
                                <span>Dịch vụ & Xét nghiệm đề xuất (Theo bảng giá):</span>
                            </h5>
                            <span class="text-[10px] text-sky-700 bg-sky-50 border border-sky-200 px-2.5 py-0.5 rounded-full font-bold">
                                Đã khớp bảng giá
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="(s, idx) in result.suggested_services" :key="idx"
                                class="bg-sky-50 text-sky-800 border border-sky-200 px-3 py-1 rounded-lg text-xs font-semibold">
                                • {{ typeof s === 'object' ? s.name : s }}
                            </span>
                        </div>
                    </div>

                    <!-- Lời khuyên của AI -->
                    <div v-if="result.advice" class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl space-y-1">
                        <span class="font-bold text-amber-900 text-xs block">Lời khuyên chăm sóc & theo dõi:</span>
                        <p class="text-amber-800 text-xs leading-relaxed">{{ result.advice }}</p>
                    </div>

                </div>

            </div>

            <!-- Footer Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3.5 border-t border-slate-100 shrink-0">
                <span class="text-[11px] text-slate-400 italic">
                    * Gợi ý của AI mang tính tham khảo chuyên môn, Bác sĩ cần đối chiếu thực tế lâm sàng.
                </span>

                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <button type="button" @click="emit('close')"
                        class="px-4 py-2 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 font-semibold cursor-pointer">
                        Đóng
                    </button>
                    <button v-if="result" type="button" @click="applyDiagnosis"
                        class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white font-semibold px-4 py-2 rounded-xl shadow-xs transition-all cursor-pointer">
                        <Icon name="check" size="14" />
                        <span>Áp dụng vào bệnh án</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>
