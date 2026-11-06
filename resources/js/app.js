import '../css/app.css';

import { createInertiaApp, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';

// Glide to in-page links such as #estimate. The attribute only has to last until the scroll starts.
document.addEventListener('click', (event) => {
    if (event.target.closest?.('a[href^="#"]:not([href="#"])')) {
        document.documentElement.setAttribute('data-smooth-scroll', '');
        setTimeout(() => document.documentElement.removeAttribute('data-smooth-scroll'), 100);
    }
}, true);

createInertiaApp({
    title: title => (title ? `${title} · Comordo` : 'Comordo'),
    resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('Link', Link)
            .mount(el);
    },
});
