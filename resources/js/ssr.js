import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { createSSRApp } from 'vue';
import { renderToString } from 'vue/server-renderer';
import { resolve, titleFor, withRoot } from './pages.js';

// Server-side rendering: Laravel posts each page here (Inertia's SSR gateway) and gets back
// finished HTML, so search engines and link previews see the content without running JavaScript.
// Runs as `node bootstrap/ssr/ssr.js` (systemd unit comordo-marketing-ssr in production).
createServer(page =>
    createInertiaApp({
        page,
        render: renderToString,
        title: title => title || titleFor(page),
        resolve,
        setup: ({ App, props, plugin }) => withRoot(createSSRApp, App, props, plugin),
    }),
    // site:export --ssr-bundle runs its own copy on a free port.
    { port: Number(process.env.INERTIA_SSR_PORT) || 13714 },
);
