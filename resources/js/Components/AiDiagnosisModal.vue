<script setup>
import Icon from '@/Components/Icon.vue'
import { ref, watch } from 'vue'
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

async function analyze() {
    if (!props.symptoms || props.symptoms.trim().length < 3) {
        error.value = 'Vui lòng nhập đầy đủ triệu chứng lâm sàng (tối thiểu 3 ký tự) để AI có thể phân tích.'
        return
    }

    loading.value = true
    error.value = ''
    result.value = null

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
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-xl border border-gray-200 shadow-2xl max-w-2xl w-full p-6 space-y-5 text-xs animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center shadow-xs">
                        <Icon name="sparkles" size="16" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-gray-900">AI Trợ lý Chẩn đoán Thú y</h3>
                            <span class="text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded-full font-mono font-medium">
                                Gemini AI Powered
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Phân tích triệu chứng và đề xuất chẩn đoán, thuốc & dịch vụ tham khảo</p>
                    </div>
                </div>
                <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg">
                    <Icon name="x" size="16" />
                </button>
            </div>

            <!-- Content Area (Scrollable) -->
            <div class="overflow-y-auto space-y-4 pr-1 flex-1">

                <!-- Input Summary Card -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">Đối tượng:</span>
                        <strong class="capitalize text-gray-900">{{ species }}</strong>
                        <span v-if="weight" class="text-gray-400">·</span>
                        <span v-if="weight" class="font-mono">{{ weight }} kg</span>
                        <span v-if="temperature" class="text-gray-400">·</span>
                        <span v-if="temperature" class="font-mono">{{ temperature }} °C</span>
                    </div>
                    <button @click="analyze" :disabled="loading"
                        class="text-indigo-600 hover:text-indigo-800 font-semibold inline-flex items-center gap-1">
                        <Icon name="clock" size="11" />
                        <span>Phân tích lại</span>
                    </button>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="py-12 text-center space-y-3">
                    <div class="inline-block relative">
                        <div class="w-12 h-12 rounded-full border-3 border-indigo-100 border-t-indigo-600 animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-indigo-600">
                            <Icon name="sparkles" size="16" />
                        </div>
                    </div>
                    <p class="font-semibold text-gray-800 text-xs">AI đang phân tích triệu chứng lâm sàng...</p>
                    <p class="text-[11px] text-gray-400">Đang đối chiếu dữ liệu bệnh học thú y và tiền sử liên quan</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 space-y-2">
                    <div class="flex items-center gap-2 font-bold">
                        <Icon name="alert" size="15" />
                        <span>Không thể hoàn thành chẩn đoán</span>
                    </div>
                    <p class="text-[11px]">{{ error }}</p>
                    <button @click="analyze" class="mt-2 text-xs bg-rose-600 text-white px-3 py-1.5 rounded-md font-medium">
                        Thử lại
                    </button>
                </div>

                <!-- Result View -->
                <div v-else-if="result" class="space-y-4 animate-in fade-in duration-200">

                    <!-- Chẩn đoán chính -->
                    <div class="p-4 bg-gradient-to-r from-indigo-50/80 to-purple-50/50 border border-indigo-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono font-bold uppercase text-indigo-700 tracking-wider">CHẨN ĐOÁN CÓ KHẢ NĂNG CAO NHẤT</span>
                            <span class="text-[11px] bg-indigo-600 text-white font-mono font-bold px-2 py-0.5 rounded-full shadow-xs">
                                Độ tin cậy: {{ result.confidence ?? '85%' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 leading-snug">
                            {{ result.primary_diagnosis }}
                        </h4>
                    </div>

                    <!-- Chẩn đoán phân biệt (Differential diagnoses) -->
                    <div v-if="result.differential_diagnoses?.length > 0" class="space-y-2">
                        <h5 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                            <Icon name="stethoscope" size="13" class="text-indigo-600" />
                            <span>Chẩn đoán phân biệt cần lưu ý:</span>
                        </h5>
                        <div class="grid grid-cols-1 gap-2">
                            <div v-for="(d, idx) in result.differential_diagnoses" :key="idx"
                                class="p-2.5 bg-gray-50 border border-gray-100 rounded-lg flex items-start justify-between gap-3">
                                <div>
                                    <span class="font-semibold text-gray-900">{{ d.name }}</span>
                                    <p class="text-[11px] text-gray-500 mt-0.5">{{ d.reason }}</p>
                                </div>
                                <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded-full shrink-0"
                                    :class="d.probability === 'Cao' || d.probability === 'Rất cao' ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-700'">
                                    {{ d.probability }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Thuốc gợi ý tham khảo (Ràng buộc kho) -->
                    <div v-if="result.suggested_medicines?.length > 0" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h5 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <Icon name="pill" size="13" class="text-emerald-600" />
                                <span>Phác đồ thuốc điều trị (Có sẵn trong kho):</span>
                            </h5>
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded font-mono">
                                Đã khớp kho phòng khám
                            </span>
                        </div>
                        <div class="bg-emerald-50/40 border border-emerald-200/70 rounded-lg p-3 space-y-2">
                            <div v-for="(m, idx) in result.suggested_medicines" :key="idx"
                                class="flex items-start justify-between gap-2 border-b border-emerald-100/60 pb-1.5 last:border-b-0 last:pb-0">
                                <div>
                                    <span class="font-semibold text-emerald-950">{{ m.name }}</span>
                                    <span class="text-gray-500 font-mono text-[11px]"> · {{ m.dosage }}</span>
                                    <p v-if="m.purpose" class="text-[10px] text-emerald-800">{{ m.purpose }}</p>
                                </div>
                                <span v-if="m.days" class="text-[10px] font-mono text-gray-600 bg-white border border-emerald-100 px-1.5 py-0.5 rounded">
                                    {{ m.days }} ngày
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dịch vụ kỹ thuật cần làm (Ràng buộc bảng giá) -->
                    <div v-if="result.suggested_services?.length > 0" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h5 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <Icon name="service" size="13" class="text-sky-600" />
                                <span>Dịch vụ & Xét nghiệm đề xuất (Theo bảng giá):</span>
                            </h5>
                            <span class="text-[10px] text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded font-mono">
                                Đã khớp bảng giá
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-for="(s, idx) in result.suggested_services" :key="idx"
                                class="bg-sky-50 text-sky-800 border border-sky-200 px-2.5 py-1 rounded-md text-[11px] font-medium">
                                • {{ typeof s === 'object' ? s.name : s }}
                            </span>
                        </div>
                    </div>

                    <!-- Lời khuyên của AI -->
                    <div v-if="result.advice" class="p-3 bg-amber-50/60 border border-amber-200 rounded-lg space-y-1">
                        <span class="font-bold text-amber-900 text-[11px] block">Lời khuyên chăm sóc & theo dõi:</span>
                        <p class="text-amber-800 text-[11px] leading-relaxed">{{ result.advice }}</p>
                    </div>

                </div>

            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-100 shrink-0">
                <span class="text-[10px] text-gray-400 italic">
                    * Gợi ý của AI mang tính tham khảo chuyên môn, Bác sĩ cần đối chiếu thực tế lâm sàng.
                </span>

                <div class="flex items-center gap-2">
                    <button type="button" @click="emit('close')"
                        class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                        Đóng
                    </button>
                    <button v-if="result" type="button" @click="applyDiagnosis"
                        class="inline-flex items-center gap-1.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold px-4 py-2 rounded-lg shadow-sm transition-all">
                        <Icon name="check" size="14" />
                        <span>Áp dụng vào bệnh án</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>
