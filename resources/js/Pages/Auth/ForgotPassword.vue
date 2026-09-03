<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout>
        <Head title="Quên mật khẩu - VetCare" />

        <div class="space-y-4">
            <div class="space-y-1 text-center">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quên mật khẩu?</h1>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Nhập địa chỉ email tài khoản của bạn để nhận liên kết khôi phục mật khẩu.
                </p>
            </div>

            <div v-if="status" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl font-medium">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <InputLabel for="email" value="Địa chỉ Email" class="text-xs font-semibold text-slate-700" />

                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full text-xs"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@vetcare.local"
                    />

                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <div class="pt-2 flex flex-col gap-2">
                    <PrimaryButton
                        class="w-full justify-center text-xs py-2.5 bg-sky-600 hover:bg-sky-700"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Đang gửi...' : 'Gửi liên kết đặt lại mật khẩu' }}
                    </PrimaryButton>

                    <Link
                        :href="route('login')"
                        class="text-xs text-center text-slate-500 hover:text-slate-800 transition-colors pt-2"
                    >
                        ← Quay lại trang Đăng nhập
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
