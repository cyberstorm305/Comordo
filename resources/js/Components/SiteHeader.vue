<script setup>
// Site header from the ImageSafe homepage design: transparent at the top, then fades
// to cream and shrinks as the page scrolls.
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { identityStartHref, identityLoginHref } from '@/identity.js'

const logoSrc = new URL('../../../public/imagesafe.svg', import.meta.url).href
const header = ref(null)
let raf = 0

function update() {
  if (raf) return
  raf = requestAnimationFrame(() => {
    raf = 0
    const y = window.scrollY || document.documentElement.scrollTop || 0
    header.value.style.setProperty('--hp', Math.min(1, Math.max(0, (y - 8) / 72)).toFixed(3))
    header.value.toggleAttribute('data-scrolled', y > 40)
  })
}
onMounted(() => {
  window.addEventListener('scroll', update, { passive: true })
  update()
})
onBeforeUnmount(() => {
  window.removeEventListener('scroll', update)
  cancelAnimationFrame(raf)
})
</script>

<template>
  <header ref="header" data-header="">
    <div data-header-inner="">
      <Link href="/" class="flex items-center">
        <img data-header-logo="" :src="logoSrc" alt="ImageSafe">
      </Link>
      <div class="ml-auto flex items-center gap-2.5">
        <Link href="/pricing" data-header-btn="" data-link="">Pricing</Link>
        <a :href="identityLoginHref" data-header-btn="" data-secondary="">Sign in</a>
        <a :href="identityStartHref" data-header-btn="" data-primary="">Start free trial</a>
      </div>
    </div>
  </header>
</template>

<style scoped>
[data-header] { --hp: 0; position: sticky; top: 0; z-index: 40; background: rgba(248,246,241,var(--hp)); box-shadow: 0 1px 0 rgba(231,226,214,var(--hp)); }
[data-header-inner] { max-width: var(--is-wide); margin: 0 auto; padding: 0 32px; height: 96px; display: flex; align-items: center; gap: 28px; transition: height .35s cubic-bezier(.2,.7,.2,1); }
[data-header-logo] { height: 40px; width: auto; display: block; flex-shrink: 0; transition: height .35s cubic-bezier(.2,.7,.2,1); }
[data-header-btn] { font-size: 16px; font-weight: 500; border-radius: 9px; white-space: nowrap; transition: font-size .35s, padding .35s, transform .25s cubic-bezier(.2,.7,.2,1), border-color .2s, background-color .2s; }
[data-header-btn]:hover { transform: translateY(-1px); }
[data-secondary] { color: #1B2536; padding: 12px 18px; border: 1px solid #C9C2B2; background: transparent; }
[data-secondary]:hover { border-color: #1B2536; }
[data-link] { color: #1B2536; padding: 12px 10px; border: 1px solid transparent; }
[data-link]:hover { color: #B4702F; }
[data-primary] { color: #fff; background: #B4702F; padding: 12px 18px; border: 1px solid transparent; }
[data-primary]:hover { background: #8F5722; }
[data-header][data-scrolled] [data-header-inner] { height: 64px; }
[data-header][data-scrolled] [data-header-logo] { height: 28px; }
[data-header][data-scrolled] [data-header-btn] { font-size: 14px; padding: 9px 14px; }
@media (max-width: 900px) {
  [data-header-inner] { height: 72px !important; padding: 0 20px; }
  [data-header-logo] { height: 30px !important; }
  [data-header-btn] { font-size: 14px !important; padding: 9px 14px !important; }
  [data-link] { padding: 9px 6px !important; }
}
/* No room beside both buttons on a phone: Pricing is in the footer there. */
@media (max-width: 480px) {
  [data-link] { display: none; }
}
</style>
