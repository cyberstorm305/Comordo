<script setup>
import { ref } from 'vue'
import { identityStartHref, identityDashboardHref } from '@/identity.js'
defineProps({
  active: { type: String, default: '' },
  ctaLabel: { type: String, default: 'Start free' },
  ctaHref: { type: String, default: () => identityStartHref },
})
const open = ref(false)
const productsOpen = ref(false)
const logoSrc = new URL('../../../public/comordo.svg', import.meta.url).href
</script>

<template>
  <header class="sticky top-0 z-50 border-b border-sand bg-cream/95 backdrop-blur-sm">
    <div class="container-site flex items-center justify-between py-3.5">
      <Link href="/" class="flex items-center gap-2.5">
        <img :src="logoSrc" alt="Comordo" class="h-7 w-auto block">
      </Link>
      <nav class="hidden lg:flex items-center gap-7 text-sm font-medium">
        <div class="relative" @mouseenter="productsOpen = true" @mouseleave="productsOpen = false" @focusout="$event.currentTarget.contains($event.relatedTarget) || (productsOpen = false)">
          <button type="button" class="flex items-center gap-1.5 text-slate hover:text-navy" :class="active === 'products' && 'text-navy font-bold'" aria-haspopup="true" :aria-expanded="productsOpen" @click="productsOpen = !productsOpen" @keydown.esc="productsOpen = false">Products
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <div v-if="productsOpen" class="absolute left-0 top-full pt-2">
            <div class="w-56 rounded-md border border-sand bg-white p-2 shadow-lg">
              <Link href="/imagesafe" class="block rounded px-3 py-2.5 hover:bg-cream">
                <span class="block text-sm font-semibold text-navy">ImageSafe</span>
                <span class="block text-xs text-stone">by Comordo</span>
              </Link>
            </div>
          </div>
        </div>
        <Link href="/contact" :class="active === 'contact' ? 'text-navy font-bold' : 'text-slate hover:text-navy'">Contact</Link>
      </nav>
      <div class="hidden lg:flex items-center gap-3">
        <a :href="identityDashboardHref" class="text-sm font-semibold text-navy px-4.5 py-2.5 border border-edge rounded-md hover:border-navy hover:bg-white">Sign in</a>
        <a :href="ctaHref" class="btn-primary !py-2.5 !px-5 !text-sm group">{{ ctaLabel }}
          <span class="transition-transform group-hover:translate-x-1">
            <svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>
        </a>
      </div>
      <button class="lg:hidden flex items-center justify-center w-11 h-11" aria-label="Toggle navigation menu" @click="open = !open">
        <span v-if="!open" class="flex flex-col gap-1 w-[18px]"><i v-for="n in 3" :key="n" class="h-0.5 w-full bg-navy rounded"></i></span>
        <span v-else class="relative w-[18px] h-[18px]">
          <i class="absolute top-2 left-0 w-[18px] h-0.5 bg-navy rounded rotate-45"></i>
          <i class="absolute top-2 left-0 w-[18px] h-0.5 bg-navy rounded -rotate-45"></i>
        </span>
      </button>
    </div>
    <div v-if="open" class="lg:hidden border-t border-sand bg-cream" @click="open = false">
      <div class="container-site flex flex-col pb-5 pt-2">
        <Link href="/imagesafe" class="text-base font-semibold text-navy py-3.5 border-b border-sand">ImageSafe <span class="text-sm font-normal text-stone">by Comordo</span></Link>
        <Link href="/contact" class="text-base font-semibold text-navy py-3.5 border-b border-sand">Contact</Link>
        <div class="flex flex-col gap-2.5 mt-4">
          <a :href="ctaHref" class="btn-primary text-center">{{ ctaLabel }}</a>
          <a :href="identityDashboardHref" class="btn-outline text-center bg-white">Sign in</a>
        </div>
      </div>
    </div>
  </header>
</template>
