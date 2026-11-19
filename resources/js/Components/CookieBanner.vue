<script setup>
// Analytics consent for Google Tag Manager. app.blade.php sets Consent Mode to "denied"
// before GTM loads and re-applies a stored "accepted"; this banner asks once and updates it.
// The footer's "Cookie settings" link reopens it (window event 'imagesafe:cookie-settings').
import { onBeforeUnmount, onMounted, ref } from 'vue'

const KEY = 'imagesafe-cookie-consent'
const open = ref(false)

function stored() {
  try { return localStorage.getItem(KEY) } catch { return null }
}

function choose(choice) {
  try { localStorage.setItem(KEY, choice) } catch { /* private mode: ask again next visit */ }
  const granted = choice === 'accepted' ? 'granted' : 'denied'
  window.gtag?.('consent', 'update', { analytics_storage: granted })
  window.dataLayer?.push({ event: 'cookie_consent', consent: choice })
  open.value = false
}

const reopen = () => { open.value = true }

onMounted(() => {
  open.value = !['accepted', 'rejected'].includes(stored())
  window.addEventListener('imagesafe:cookie-settings', reopen)
})
onBeforeUnmount(() => window.removeEventListener('imagesafe:cookie-settings', reopen))
</script>

<template>
  <div
    v-if="open"
    role="region"
    aria-label="Cookie choices"
    class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-[560px] rounded-xl border border-sand bg-white p-5 shadow-[0_12px_40px_rgba(27,37,54,.18)] sm:left-auto sm:right-6 sm:bottom-6"
  >
    <p class="m-0 text-[15px] leading-6 text-slate">
      We'd like to use analytics cookies to see how people use this site, so we can improve it.
      They're only set if you accept. See our <Link href="/privacy#cookies" class="font-medium text-navy underline">privacy policy</Link>.
    </p>
    <div class="mt-4 flex flex-wrap gap-2.5">
      <button type="button" class="rounded-lg bg-navy px-4 py-2 text-[15px] font-bold text-white hover:opacity-90" @click="choose('accepted')">Accept analytics</button>
      <button type="button" class="rounded-lg border border-line px-4 py-2 text-[15px] font-bold text-navy hover:bg-cream" @click="choose('rejected')">Reject</button>
    </div>
  </div>
</template>
