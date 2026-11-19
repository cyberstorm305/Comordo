import '../css/app.css';

import { createInertiaApp, Link, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import CookieBanner from './Components/CookieBanner.vue';
import StaticLink from './Components/StaticLink.vue';
import { isStaticExport } from './site.js';

// Titles come from config/seo.php or seo-comordo.php (shared as props.seo), the same place as the server-rendered
// title and link-preview tags, so pages don't set their own.
router.on('navigate', (event) => {
    const title = event.detail.page.props.seo?.title;
    if (title) {
        document.title = title;
    }
});

// Glide to in-page links such as #estimate. The attribute only has to last until the scroll starts.
document.addEventListener('click', (event) => {
    if (event.target.closest?.('a[href^="#"]:not([href="#"])')) {
        document.documentElement.setAttribute('data-smooth-scroll', '');
        setTimeout(() => document.documentElement.removeAttribute('data-smooth-scroll'), 100);
    }
}, true);

// The site name for pages with no title, from the initial page props (set server-side from SITE).
const initialPage = document.querySelector('script[data-page="app"]');
const fallbackTitle = JSON.parse(initialPage?.textContent || '{}').props?.site === 'comordo' ? 'Comordo' : 'ImageSafe';

createInertiaApp({
    title: title => title || fallbackTitle,
    resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => [h(App, props), h(CookieBanner)] })
            .use(plugin)
            .component('Link', isStaticExport ? StaticLink : Link)
            .mount(el);
    },
});
