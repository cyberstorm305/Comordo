import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { createSSRApp } from 'vue';
import { resolve, titleFor, withRoot } from './pages.js';

// Glide to in-page links such as #estimate. The attribute only has to last until the scroll starts.
document.addEventListener('click', (event) => {
    if (event.target.closest?.('a[href^="#"]:not([href="#"])')) {
        document.documentElement.setAttribute('data-smooth-scroll', '');
        setTimeout(() => document.documentElement.removeAttribute('data-smooth-scroll'), 100);
    }
}, true);

// The tab title: the first page's from its props (the same as the server's <title>), then each
// new page's as the visitor moves around the site.
let currentPage = JSON.parse(document.querySelector('script[data-page="app"]')?.textContent || '{}');
router.on('navigate', (event) => {
    currentPage = event.detail.page;
    document.title = titleFor(currentPage);
});

// createSSRApp takes over the server-rendered HTML when there is some (ssr.js), and renders
// from scratch when there isn't (SSR service down: Inertia falls back to client-side rendering).
createInertiaApp({
    title: title => title || titleFor(currentPage),
    resolve,
    setup({ el, App, props, plugin }) {
        withRoot(createSSRApp, App, props, plugin).mount(el);
    },
});
