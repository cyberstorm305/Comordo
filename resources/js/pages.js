import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import StaticLink from './Components/StaticLink.vue';
import { Link as InertiaLink } from '@inertiajs/vue3';
import CookieBanner from './Components/CookieBanner.vue';
import { isStaticExport } from './site.js';
import { h } from 'vue';

// Shared by the browser (app.js) and the server renderer (ssr.js), so both build the same tree
// and the browser can take over the server's HTML (hydration) instead of redrawing it.
export const resolve = name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));

export function withRoot(createVueApp, App, props, plugin) {
    return createVueApp({ render: () => [h(App, props), h(CookieBanner)] })
        .use(plugin)
        .component('Link', isStaticExport ? StaticLink : InertiaLink);
}

// A page's title: from config/seo.php or seo-comordo.php (props.seo, the same place as the
// description and link-preview tags), else the site name. Pages don't set their own.
export const titleFor = page => page?.props?.seo?.title || (page?.props?.site === 'comordo' ? 'Comordo' : 'ImageSafe');
