import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Các trang không dùng AuthenticatedLayout
const guestPages = ['Auth/Login', 'Auth/Register', 'Auth/ForgotPassword', 'Auth/ResetPassword', 'Auth/VerifyEmail', 'Auth/ConfirmPassword', 'Welcome', 'Error'];

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        );

        // Gán persistent layout cho tất cả các trang authenticated
        // Persistent layout nghĩa là component Layout giữ nguyên trong DOM khi chuyển trang
        // → Sidebar sẽ không bị unmount/remount → animation chỉ chạy 1 lần
        const isGuestPage = guestPages.some(g => name.startsWith(g))
        if (!isGuestPage && !page.default.layout) {
            page.default.layout = AuthenticatedLayout
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#0284c7',
    },
});

