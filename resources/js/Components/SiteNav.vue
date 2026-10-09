<script setup>
import { ref } from 'vue'
import { identityBase } from '@/identity.js'
defineProps({
  active: { type: String, default: '' },
  ctaLabel: { type: String, default: 'Get started' },
  ctaHref: { type: String, default: () => `${identityBase}/register` },
})
const open = ref(false)
const logoSrc = new URL('../../../public/comordo.svg', import.meta.url).href
const links = [
  { to: '/imagesafe', key: 'imagesafe', label: 'ImageSafe' },
  { to: '/compli', key: 'compli', label: 'Compli' },
  { to: '/#products', key: 'products', label: 'Products' },
  { to: '/#faqs', key: 'faqs', label: 'FAQs' },
]
</script>

<template>
  <header class="sticky top-0 z-50 border-b border-sand bg-cream/95 backdrop-blur-sm">
    <div class="container-site flex items-center justify-between py-3.5">
      <Link href="/" class="flex items-center gap-2.5">
        <img :src="logoSrc" alt="Comordo" class="h-7 w-auto block">
      </Link>
      <nav class="hidden lg:flex items-center gap-7 text-sm font-medium">
        <Link v-for="l in links" :key="l.key" :href="l.to"
          :class="active === l.key ? 'text-navy font-bold' : 'text-slate hover:text-navy'">{{ l.label }}</Link>
      </nav>
      <div class="hidden lg:flex items-center gap-3">
        <a :href="`${identityBase}/login`" class="text-sm font-semibold text-navy px-4.5 py-2.5 border border-edge rounded-md hover:border-navy hover:bg-white">Sign in</a>
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
        <Link v-for="l in links" :key="l.key" :href="l.to" class="text-base font-semibold text-navy py-3.5 border-b border-sand">{{ l.label }}</Link>
        <div class="flex flex-col gap-2.5 mt-4">
          <a :href="ctaHref" class="btn-primary text-center">{{ ctaLabel }}</a>
          <a :href="`${identityBase}/login`" class="btn-outline text-center bg-white">Sign in</a>
        </div>
      </div>
    </div>
  </header>
</template>
