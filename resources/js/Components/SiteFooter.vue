<script setup>
import { identityLoginHref, identityDashboardHref } from '@/identity.js'
import { comordoSiteUrl, imagesafeSiteUrl, useSite } from '@/site.js'
import NewsletterForm from './NewsletterForm.vue'
const logoSrc = '/comordo.svg'
const { isComordo } = useSite()
const openCookieSettings = () => window.dispatchEvent(new Event('imagesafe:cookie-settings'))
</script>

<template>
  <footer class="border-t border-sand">
    <div v-if="isComordo" class="border-b border-sand">
      <div class="container-site py-10">
        <NewsletterForm />
      </div>
    </div>
    <div class="container-site py-10 pb-7 flex flex-wrap items-center justify-between gap-6">
      <div class="flex flex-wrap items-center gap-2.5">
        <img :src="logoSrc" width="633" height="133" alt="Comordo" class="h-[30px] w-auto block">
        <div v-if="isComordo" class="text-base text-stone ml-2">Comordo Ltd &middot; Digital assurance for schools and trusts</div>
        <div v-else class="text-base text-stone ml-2">ImageSafe is a <a :href="comordoSiteUrl" class="text-stone underline decoration-sand underline-offset-4 hover:text-navy">Comordo</a> product.</div>
      </div>
      <nav v-if="isComordo" class="grid w-full grid-cols-2 gap-x-6 gap-y-4 text-[13px] sm:flex sm:w-auto sm:flex-wrap">
        <Link href="/imagesafe" class="text-stone hover:text-navy">ImageSafe</Link>
        <a :href="imagesafeSiteUrl" class="text-stone hover:text-navy">imagesafe.co.uk</a>
        <Link href="/contact" class="text-stone hover:text-navy">Contact</Link>
        <Link href="/privacy" class="text-stone hover:text-navy">Privacy</Link>
        <Link href="/terms" class="text-stone hover:text-navy">Terms</Link>
        <button type="button" class="text-left text-stone hover:text-navy" @click="openCookieSettings">Cookie settings</button>
        <a :href="identityDashboardHref" class="text-stone hover:text-navy">Sign in</a>
      </nav>
      <nav v-else class="grid w-full grid-cols-2 gap-x-6 gap-y-4 text-[13px] sm:flex sm:w-auto sm:flex-wrap">
        <Link href="/" class="text-stone hover:text-navy">ImageSafe</Link>
        <Link href="/pricing" class="text-stone hover:text-navy">Pricing</Link>
        <Link href="/privacy" class="text-stone hover:text-navy">Privacy</Link>
        <Link href="/terms" class="text-stone hover:text-navy">Terms</Link>
        <Link href="/contact" class="text-stone hover:text-navy">Contact</Link>
        <button type="button" class="text-left text-stone hover:text-navy" @click="openCookieSettings">Cookie settings</button>
        <a :href="identityLoginHref" class="text-stone hover:text-navy">Sign in</a>
      </nav>
    </div>
    <div class="border-t border-sand">
      <div class="container-site py-4 flex flex-col gap-3 font-mono text-[11.5px] text-mist sm:flex-row sm:flex-wrap sm:justify-between">
        <div>
          <span class="block sm:inline">© 2026 Comordo Ltd</span><span class="hidden sm:inline"> · </span><span class="block sm:inline">Registered in England and Wales</span><span class="hidden sm:inline"> · </span><span class="block sm:inline">Company no. 1729746</span>
        </div>
        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:gap-5">
          <div class="flex items-center gap-3.5">
            <a href="https://www.linkedin.com/company/comordoapp" target="_blank" rel="noopener" aria-label="Comordo on LinkedIn" class="inline-flex text-mist hover:text-navy">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0z"/></svg>
            </a>
            <a href="https://www.facebook.com/comordoapp" target="_blank" rel="noopener" aria-label="Comordo on Facebook" class="inline-flex text-mist hover:text-navy">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10.02 10.02 0 0 0 8.44-9.9c0-5.53-4.5-10.02-10-10.02Z"/></svg>
            </a>
            <a href="https://www.instagram.com/comordoapp" target="_blank" rel="noopener" aria-label="Comordo on Instagram" class="inline-flex text-mist hover:text-navy">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/></svg>
            </a>
            <a href="https://x.com/comordoapp" target="_blank" rel="noopener" aria-label="Comordo on X" class="inline-flex text-mist hover:text-navy">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.2 2.5h3.3l-7.2 8.2L23 21.5h-6.6l-5.2-6.8-6 6.8H1.9l7.7-8.8L1.3 2.5H8l4.7 6.2 5.5-6.2Zm-1.2 17h1.8L7.1 4.4H5.2L17 19.5Z"/></svg>
            </a>
          </div>
          <div>
            <span class="block sm:inline">UK English</span><span class="hidden sm:inline"> · </span><span class="block sm:inline">UK statutory requirements</span>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
