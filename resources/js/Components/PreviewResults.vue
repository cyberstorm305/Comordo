<script setup>
import { reactive, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { identityBase } from '@/identity.js'

const props = defineProps({ result: { type: Object, required: true }, host: { type: String, default: '' } })

const registerHref = `${identityBase}/register`
const broken = reactive({})
const found = computed(() => props.result.found || [])

const locked = [
  { t: 'Permission matching', d: 'Every face checked against your own consent records.' },
  { t: 'Names and location data', d: 'Captions, file names and GPS data flagged for review.' },
  { t: 'AI exposure', d: 'Whether your photos are open to AI crawlers and web archives.' },
  { t: 'Termly governor reports', d: 'A safeguarding report ready for your governors.' },
]

function pagePath(u) {
  try { const p = new URL(u).pathname; return p === '/' ? 'the home page' : p } catch { return 'your website' }
}
function alt(f) {
  const n = (f.faces || []).length
  return `Photo from ${pagePath(f.page_url)} with ${n} ${n === 1 ? 'person' : 'people'}`
}
function box(face, f) {
  return {
    left: `${(face.x / f.width) * 100}%`,
    top: `${(face.y / f.height) * 100}%`,
    width: `${(face.w / f.width) * 100}%`,
    height: `${(face.h / f.height) * 100}%`,
  }
}
</script>

<template>
  <section id="preview-results" class="border-b border-sand bg-white">
    <div class="container-site py-[clamp(48px,8vw,88px)]">
      <div class="max-w-[760px] flex flex-col gap-3.5 mb-10">
        <div class="eyebrow">Your preview</div>
        <h2 v-if="found.length" class="section-title" data-testid="headline">
          We found {{ found.length }} {{ found.length === 1 ? 'photo' : 'photos' }} with people in the first {{ result.images_checked }} images on {{ host }}.
        </h2>
        <template v-else>
          <h2 class="section-title" data-testid="headline">No photos with people in the first pages we checked.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate m-0">A full scan covers every page.</p>
        </template>
      </div>

      <ul v-if="found.length" class="grid grid-cols-1 min-[560px]:grid-cols-2 lg:grid-cols-3 gap-5 items-start list-none p-0 m-0">
        <li v-for="(f, i) in found" :key="i" class="mock-card !rounded-[10px] overflow-hidden flex flex-col" data-testid="photo-card">
          <div v-if="!broken[i]" class="relative w-full bg-sand-light overflow-hidden" :style="{ aspectRatio: `${f.width} / ${f.height}` }">
            <img :src="f.image_url" :alt="alt(f)" referrerpolicy="no-referrer" loading="lazy" class="absolute inset-0 w-full h-full block" @error="broken[i] = true">
            <span v-for="(face, j) in f.faces" :key="j" class="absolute border-2 border-copper rounded-[3px] shadow-[0_0_0_1px_rgba(255,255,255,0.6)]" :style="box(face, f)" data-testid="face-box" aria-hidden="true"></span>
          </div>
          <div v-else class="aspect-[4/3] bg-sand-light flex items-center justify-center text-center px-6 text-[13px] text-stone" data-testid="photo-placeholder">This photo couldn't be shown here.</div>
          <div class="flex items-center justify-between gap-3 px-4 py-3 border-t border-sand-light">
            <span class="font-mono text-[10.5px] text-mist truncate">{{ pagePath(f.page_url) }}</span>
            <span class="pill-amber !text-[10px] normal-case shrink-0">{{ (f.faces || []).length }} {{ (f.faces || []).length === 1 ? 'person' : 'people' }}</span>
          </div>
        </li>
      </ul>

      <div class="mt-14">
        <h3 class="text-[19px] font-bold m-0 mb-5">Your full trial also checks…</h3>
        <ul class="grid grid-cols-1 min-[560px]:grid-cols-2 lg:grid-cols-4 gap-4 list-none p-0 m-0">
          <li v-for="c in locked" :key="c.t" class="bg-cream border border-sand rounded-[10px] p-5 flex flex-col gap-2">
            <span class="flex items-center gap-2 font-mono text-[10px] tracking-[0.06em] uppercase text-copper">
              <svg width="11" height="13" viewBox="0 0 11 13" fill="none" aria-hidden="true"><rect x="1" y="5.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M3 5.5V3.8a2.5 2.5 0 015 0v1.7" stroke="currentColor" stroke-width="1.4"/></svg>
              Included in your trial
            </span>
            <b class="text-[15px]">{{ c.t }}</b>
            <p class="text-[13px] leading-normal text-slate m-0">{{ c.d }}</p>
          </li>
        </ul>
      </div>

      <div class="mt-10 flex flex-wrap gap-3.5">
        <a :href="registerHref" class="btn-primary" data-testid="trial-link">Start your 14-day free trial</a>
        <Link href="/demo" class="btn-outline">Book a demo</Link>
      </div>
    </div>
  </section>
</template>
