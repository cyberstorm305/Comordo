<!--
  AppShell.vue — the ImageSafe app's two-tier header, copied from the design
  handoff (Designs/Imagesafe/ImageSafeApp.vue) and the real app's
  AuthenticatedLayout + DashboardHeader. In a preview every header control is
  disabled (shown, muted, out of the tab order) except "Start your 14-day free
  trial", which links to Identity's sign-up.
-->
<script setup>
import { identityBase } from '@/identity.js'

defineProps({ host: { type: String, default: '' } })

const registerHref = `${identityBase}/register`
const tabs = ['Dashboard', 'Inventory', 'Scans', 'People', 'Reports', 'Settings']
</script>

<template>
  <div class="isp flex min-h-screen flex-col">
    <p class="m-0 bg-navy px-4 py-[7px] text-center text-[12px] font-medium text-cream/85" data-testid="preview-strip">Preview of {{ host || 'your website' }}</p>

    <!-- HEADER (two-tier) -->
    <header class="sticky top-0 z-40 border-b border-line-soft bg-cream/[.94] backdrop-blur-[8px]">
      <div class="flex h-14 w-full items-center gap-3.5 px-4 sm:px-[26px]">
        <a href="/" class="flex flex-none items-center gap-2.5 text-navy no-underline" aria-label="ImageSafe by Comordo — back to the homepage">
          <svg class="h-7 w-7 shrink-0" viewBox="0 0 148 133" aria-hidden="true"><path d="M122.99,31.45c-.67.64-1.35,1.27-2.01,1.92-8.48,8.27-16.4,17.08-23.83,26.32-10.72,13.33-20.43,27.29-29.31,41.94-1.13,1.87-3.78,2.06-5.14.34l-15.16-19.02-7.93-9.93-8.68-10.88c-1.06-1.33-.02-2.66.57-4.21l2.17-4.67c1.07-2.07,2.79-.86,4.14.07l9.85,7,12.56,8.68c.76.7,1.76.93,2.69.75,3.91-7.63,8.18-15.03,12.94-22.25,3.73-5.48,7.57-10.78,11.75-15.96,5.45-6.74,11.37-13.09,17.72-19.04C89.65,1.24,68.92-3.42,47.17,2.73,25.89,8.75,9.05,25.38,2.88,46.62c-13.34,45.95,21.53,87.71,65.71,86.35,34.13-1.05,62.24-28.3,64.29-62.39.87-14.41-2.89-27.89-9.89-39.13Z" fill="#1a2436"/><path d="M144.22,5.81c-.07.02-.13.05-.2.07-6.38,2.48-12.56,5.24-18.56,8.56-9.2,5.08-17.88,10.95-26,17.6-4.8,3.93-9.31,8.05-13.77,12.37-6.29,6.27-12.11,12.84-17.59,19.8l-3.25,4.36c-.05.07-.12.13-.18.19-1.15,1.3-3.16,1.44-4.47.24l-12.56-8.68-9.85-7c-1.35-.93-3.06-2.14-4.14-.07l-2.17,4.67c-.6,1.55-1.63,2.88-.57,4.21l8.68,10.88,7.93,9.93,15.16,19.02c1.36,1.7,4,1.54,5.13-.32,8.88-14.66,18.6-28.63,29.32-41.96,7.43-9.24,15.35-18.05,23.83-26.32.67-.65,1.34-1.28,2.01-1.92,7.66-7.26,15.65-14.01,24.12-20.14,2.94-2.12.53-6.72-2.88-5.5Z" fill="#b4702e"/></svg>
          <div class="hidden items-baseline whitespace-nowrap lg:flex">
            <span class="text-[13.5px] font-bold tracking-[-0.02em]">ImageSafe</span>
            <span class="ml-2 text-[10.5px] text-mist">by Comordo</span>
          </div>
        </a>

        <nav class="hidden min-w-0 flex-1 items-center gap-0.5 overflow-x-auto lg:flex" aria-label="ImageSafe">
          <button v-for="(t, i) in tabs" :key="t" type="button" disabled
                  class="cursor-default whitespace-nowrap rounded-[7px] border-0 px-2.5 py-2 text-[13px]"
                  :class="i === 0 ? 'bg-tint font-semibold text-navy' : 'cursor-not-allowed bg-transparent font-medium text-slate opacity-50'"
                  :aria-current="i === 0 ? 'page' : undefined"
                  data-testid="header-tab">{{ t }}</button>
        </nav>

        <div class="ml-auto flex items-center gap-2.5">
          <button type="button" disabled aria-label="Account menu" class="flex h-[30px] w-[30px] cursor-not-allowed items-center justify-center rounded-full border-0 bg-copper-pale p-0 text-copper opacity-60" data-testid="header-avatar">
            <svg width="13" height="14" viewBox="0 0 13 14" fill="none"><circle cx="6.5" cy="4" r="2.9" stroke="currentColor" stroke-width="1.6"/><path d="M1.2 13c.5-2.9 2.6-4.4 5.3-4.4s4.8 1.5 5.3 4.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          </button>
          <button type="button" disabled aria-label="Menu"
                  class="inline-flex h-[34px] w-[34px] cursor-not-allowed items-center justify-center rounded-[7px] border border-line bg-white text-[15px] text-slate opacity-50 lg:hidden"
                  data-testid="header-menu">☰</button>
        </div>
      </div>

      <div class="border-t border-hair">
        <div class="flex min-h-[52px] w-full items-center justify-between gap-3 px-4 sm:px-[26px]">
          <div class="flex min-w-0 items-center gap-2.5">
            <span class="hidden text-xs text-mist lg:inline">Viewing</span>
            <button type="button" disabled aria-label="School" class="min-w-0 max-w-[22rem] cursor-not-allowed truncate rounded-lg border border-line bg-white py-2 pl-3 pr-3 text-left text-[13px] font-semibold text-navy opacity-60" data-testid="viewing-host">{{ host || 'Your website' }}</button>
            <span class="whitespace-nowrap rounded-[5px] bg-hair px-2 py-[3px] text-[11.5px] text-mist">Preview</span>
          </div>
          <div class="flex flex-none items-center gap-2">
            <button type="button" disabled class="hidden cursor-not-allowed items-center rounded-[7px] border border-line bg-white px-3.5 py-2 text-[13px] font-medium text-slate opacity-50 lg:inline-flex" data-testid="header-import">Import permissions</button>
            <a :href="registerHref" class="inline-flex items-center whitespace-nowrap rounded-[7px] bg-navy px-3.5 py-2 text-[13px] font-semibold text-white no-underline hover:bg-navy-light lg:px-4" data-testid="shell-trial-link">
              <span class="hidden sm:inline">Start your 14-day free trial</span><span class="sm:hidden">Start free trial</span>
            </a>
          </div>
        </div>
      </div>
    </header>

    <main class="w-full flex-1 px-4 pb-[60px] pt-[26px] sm:px-[26px]">
      <slot />
    </main>
  </div>
</template>
