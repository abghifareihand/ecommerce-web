import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'EcoStore';

const updateFavicon = (url) => {
    if (!url) return;
    const links = document.querySelectorAll("link[rel*='icon']");
    if (links.length > 0) {
        links.forEach((link) => {
            if (link.href !== url) {
                link.href = url;
            }
        });
    } else {
        const link = document.createElement('link');
        link.rel = 'icon';
        link.type = 'image/png';
        link.href = url;
        document.head.appendChild(link);
    }
};

router.on('navigate', (event) => {
    const logoUrl = event.detail?.page?.props?.store?.logo_url;
    if (logoUrl) {
        updateFavicon(logoUrl);
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];
        if (!page) {
            throw new Error(`Page "./Pages/${name}.vue" not found.`);
        }
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#16a34a',
        showSpinner: false,
        delay: 300,
    },
});
