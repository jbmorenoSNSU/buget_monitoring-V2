import './bootstrap';
import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import { createPinia } from 'pinia';

const appName = import.meta.env.VITE_APP_NAME || 'Budget Monitor';

import { Chart } from 'chart.js';
Chart.defaults.color = '#94A3B8';
Chart.defaults.borderColor = '#232936';

import AppLayout from './Components/Layout/AppLayout.vue';

createInertiaApp({
    title: (title) => title ? `${title} — ${appName}` : appName,
    resolve: async name => {
        const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue');
        const page = await pages[`./Pages/${name}.vue`]();
        page.default.layout = page.default.layout || AppLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();
        
        app.use(plugin);
        app.use(ZiggyVue);
        app.use(pinia);
        app.use(Toast, {
            position: 'top-center',
            timeout: 3000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            showCloseButtonOnHover: false,
            hideProgressBar: false,
            closeButton: 'button',
            icon: true,
            rtl: false,
            transition: 'Vue-Toastification__fade',
            maxToasts: 5,
            newestOnTop: true,
        });
        app.mount(el);
    },
    progress: {
        color: '#6366F1',
    },
});
