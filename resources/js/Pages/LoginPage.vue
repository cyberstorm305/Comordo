<script setup>
import { identityBase } from '@/identity.js'
const identityLoginHref = `${identityBase}/login`
const logoSrc = new URL('../../../public/comordo-light.svg', import.meta.url).href
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-5">
    <div class="max-w-[620px] w-full bg-white border border-sand-dark rounded-xl p-[clamp(28px,5vw,48px)] flex flex-col gap-5">
      <div class="flex items-center gap-2.5">
        <img :src="logoSrc" alt="Comordo" class="h-[30px] w-auto block">
        <span class="font-mono text-[11px] tracking-[0.08em] uppercase text-stone">Identity</span>
      </div>
      <div class="flex flex-col gap-2">
        <h1 class="text-[clamp(24px,3vw,32px)] font-bold tracking-[-0.02em] m-0">Sign in on Identity</h1>
        <p class="text-[15px] leading-relaxed text-slate m-0">Authentication has moved to the Identity app. Use the central account for all products.</p>
      </div>
      <div class="flex flex-wrap gap-3.5">
        <a :href="identityLoginHref" class="btn-primary">Continue to sign in</a>
        <Link href="/contact" class="btn-outline">Contact us</Link>
      </div>
    </div>
  </div>
</template>
