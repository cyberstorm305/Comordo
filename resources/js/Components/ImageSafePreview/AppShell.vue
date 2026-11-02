<!--
  AppShell.vue — the ImageSafe app's header, matching the real app's
  AuthenticatedLayout: logo and nav, the school menu centred, then the
  notification bell and account menu (Settings lives in it). In a preview the
  header controls look as they do in the app but open the trial prompt;
  "Start your 7-day free trial" links to Identity's sign-up.
-->
<script setup>
const logoSrc = new URL('../../../../public/imagesafe.svg', import.meta.url).href
import { inject } from 'vue'
import { identityStartHref } from '@/identity.js'

defineProps({ host: { type: String, default: '' } })

const openTrial = inject('openTrial')
const registerHref = identityStartHref
const tabs = [
  { label: 'Dashboard' },
  { label: 'Inventory', feature: 'Inventory' },
  { label: 'People', feature: 'People & Permissions' },
]
</script>

<template>
  <div class="isp flex min-h-screen flex-col">
    <p class="m-0 bg-navy px-4 py-[7px] text-center text-[12px] font-medium text-cream/85" data-testid="preview-strip">Preview of {{ host || 'your website' }}</p>

    <!-- HEADER: logo and nav, the school menu (centred from wide, 1400px; beside the trial link below), then the trial link and account menu -->
    <header class="sticky top-0 z-40 border-b border-sand bg-paper/[.94] backdrop-blur-[8px]">
      <div class="flex h-14 w-full items-center gap-3.5 px-4 sm:px-7 wide:grid wide:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)]">
        <div class="flex min-w-0 flex-1 items-center gap-3.5">
          <a href="/" class="flex flex-none items-center gap-2.5 text-navy no-underline" aria-label="ImageSafe by Comordo — back to the homepage">
            <img :src="logoSrc" alt="ImageSafe" class="block h-7 w-auto">
          </a>

          <nav class="hidden min-w-0 items-center gap-0.5 overflow-x-auto lg:flex" aria-label="ImageSafe">
            <button v-for="t in tabs" :key="t.label" type="button"
                    class="whitespace-nowrap rounded-[7px] border-0 px-2.5 py-2 text-[13px] leading-5 transition"
                    :class="t.feature ? 'cursor-pointer bg-transparent font-medium text-slate hover:bg-shell hover:text-navy' : 'cursor-default bg-tint font-semibold text-navy'"
                    :aria-current="t.feature ? undefined : 'page'"
                    data-testid="header-tab"
                    @click="t.feature && openTrial(t.feature, $event.currentTarget)">{{ t.label }}</button>
          </nav>
        </div>

        <button type="button" aria-label="School"
                class="hidden w-[16rem] cursor-pointer items-center gap-2 rounded-[7px] border border-line bg-white py-2 pl-3 pr-2.5 text-left text-[13px] font-semibold leading-5 text-navy hover:border-edge md:inline-flex xl:w-[22rem]"
                data-testid="viewing-host"
                @click="openTrial('Multiple schools', $event.currentTarget)">
          <span class="truncate">{{ host || 'Your website' }}</span>
          <svg class="ml-auto h-3.5 w-3.5 flex-none text-slate" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M5 7.5l5 5 5-5" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </button>

        <div class="flex items-center justify-end gap-2.5 wide:col-start-3">
          <a :href="registerHref" class="inline-flex items-center whitespace-nowrap rounded-[7px] bg-copper px-3.5 py-2 text-[13px] font-semibold text-white no-underline hover:bg-copper-dark lg:px-4" data-testid="shell-trial-link">
            <span class="hidden sm:inline">Start your 7-day free trial</span><span class="sm:hidden">Start free trial</span>
          </a>
          <button type="button" aria-label="Notifications" class="relative inline-flex h-[34px] w-[34px] cursor-pointer items-center justify-center border-0 bg-transparent p-0 text-slate hover:text-navy" data-testid="header-bell"
                  @click="openTrial('Notifications', $event.currentTarget)">
            <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path d="M10 2.5a5 5 0 0 0-5 5v3.2L3.5 13.5h13L15 10.7V7.5a5 5 0 0 0-5-5z" stroke-linejoin="round" />
              <path d="M8 16a2 2 0 0 0 4 0" stroke-linecap="round" />
            </svg>
          </button>
          <button type="button" aria-label="Account menu" class="inline-flex cursor-pointer items-center gap-2.5 rounded-[7px] border-0 bg-transparent p-0.5 text-navy" data-testid="header-avatar"
                  @click="openTrial('Settings & users', $event.currentTarget)">
            <span class="flex h-[30px] w-[30px] items-center justify-center rounded-full bg-teal text-white">
              <svg width="13" height="14" viewBox="0 0 13 14" fill="none" aria-hidden="true"><circle cx="6.5" cy="4" r="2.9" stroke="currentColor" stroke-width="1.6"/><path d="M1.2 13c.5-2.9 2.6-4.4 5.3-4.4s4.8 1.5 5.3 4.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
            <svg class="h-3.5 w-3.5 text-slate" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M5 7.5l5 5 5-5" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </button>
          <button type="button" aria-label="Menu"
                  class="inline-flex h-[34px] w-[34px] cursor-pointer items-center justify-center rounded-[7px] border border-line bg-white text-[15px] text-slate hover:bg-shell lg:hidden"
                  data-testid="header-menu"
                  @click="openTrial('Inventory', $event.currentTarget)">☰</button>
        </div>
      </div>
    </header>

    <main class="w-full flex-1 px-4 pb-4 pt-4 sm:px-7 sm:pb-7 sm:pt-7">
      <slot />
    </main>
  </div>
</template>
