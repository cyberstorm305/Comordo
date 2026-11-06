<script setup>
// The instant free preview band ("Curious what it would find on your site?" on the homepage).
// Shared by the ImageSafe homepage and the pricing page. Styles stay inline, as in the
// homepage handoff.
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { identityLoginHref } from '@/identity.js'
import { createPreview } from '@/usePreview.js'

// The copy defaults to the homepage's; a page can pass its own.
defineProps({
  heading: { type: String, default: 'Curious what it would find on your site? Find out in under a minute.' },
  lead: { type: String, default: "Enter your school's website address. ImageSafe scans the first public pages straight away, counts the images containing people, and shows the safeguarding checks they would raise. Nobody is identified, your photos are never stored, and no account is needed." },
  points: {
    type: Array,
    default: () => [
      'Results on screen in under a minute, from the first pages of your website',
      'No faces identified, no permission records needed',
      'Reads public pages only; changes nothing on your site',
    ],
  },
})

const signInHref = identityLoginHref
const mono = "font-family:'IBM Plex Mono',monospace"

// --- Free preview form ---
const previewUrl = ref('')
const previewError = ref(false)
const previewEmail = ref('')
const previewEmailError = ref(false)
const previewConfirmed = ref(false)
const previewNeedsConfirm = ref(false)
const previewStarting = ref(false)
const previewFailure = ref(null) // blocked | limited | busy
const errorCopy = {
  email: 'Please enter a valid email address, like you@yourschool.org.uk.',
  blocked: "That address can't be scanned.",
  limited: 'Lots of previews are running right now. Try again in a few minutes.',
  busy: 'Previews are busy right now. Try again shortly, or book a demo.',
}
async function requestPreview() {
  if (previewStarting.value) return
  const u = previewUrl.value.trim()
  if (u.length < 4 || !/\.[a-z]{2,}/i.test(u) && !/^(https?:\/\/)?\d{1,3}(\.\d{1,3}){3}/.test(u)) { previewError.value = true; return }
  previewError.value = false
  const email = previewEmail.value.trim()
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { previewEmailError.value = true; return }
  previewEmailError.value = false
  if (!previewConfirmed.value) { previewNeedsConfirm.value = true; return }
  previewNeedsConfirm.value = false
  previewFailure.value = null
  previewStarting.value = true
  const res = await createPreview(u, email)
  if (res.id) {
    // The preview page shows this as "Viewing …"; the API doesn't return it.
    const host = u.replace(/^[a-z][a-z0-9+.-]*:\/\//i, '').replace(/^www\./i, '').split(/[/?#]/)[0]
    try {
      sessionStorage.setItem(`isp-preview-host:${res.id}`, host)
      // The preview page's "share or keep" banner sends its confirmation to this person.
      sessionStorage.setItem(`isp-preview-lead:${res.id}`, JSON.stringify({ lead: res.lead, email }))
    } catch { /* storage blocked */ }
    router.visit(`/preview/${res.id}`)
    return
  }
  previewStarting.value = false
  previewFailure.value = res.error
}
watch(previewConfirmed, (v) => { if (v) previewNeedsConfirm.value = false })

// Reveal on scroll, as on the homepage. Parent pages can't reach these elements with their scoped styles.
const band = ref(null)
let reveal
onMounted(() => {
  reveal = new IntersectionObserver((es) => es.forEach((e) => {
    if (e.isIntersecting) { e.target.setAttribute('data-in', ''); reveal.unobserve(e.target) }
  }), { threshold: 0.12 })
  band.value.querySelectorAll('[data-reveal]').forEach(r => reveal.observe(r))
})
onBeforeUnmount(() => reveal?.disconnect())
</script>

<template>
  <!-- Free preview -->
  <section ref="band" id="preview" data-band="" style="background:#1B2536;color:#fff">
    <div data-pad="" data-cols="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:0 32px;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:56px;align-items:center">
      <div data-reveal="">
        <h2 data-h2="" style="margin:0 0 16px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#fff;text-wrap:balance">{{ heading }}</h2>
        <p data-lead="" style="margin:0 0 22px;font-size:18px;line-height:1.6;color:#C9C2B2;text-wrap:pretty">{{ lead }}</p>
        <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px;font-size:16px;line-height:1.6;color:#E7C9A8">
          <li v-for="point in points" :key="point" style="display:flex;gap:10px"><span>—</span>{{ point }}</li>
        </ul>
      </div>
      <form data-reveal="" novalidate style="background:#F8F6F1;color:#4A5468;border-radius:14px;padding:32px;display:flex;flex-direction:column;gap:16px;box-shadow:0 30px 60px -30px rgba(0,0,0,.5)" @submit.prevent="requestPreview">
        <p style="margin:0;font-size:20px;font-weight:700;color:#1B2536">Get your instant free preview</p>
        <label :style="mono + ';display:flex;flex-direction:column;gap:8px;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#8A8474'">School website
          <input v-model="previewUrl" data-preview-input="" type="text" inputmode="url" autocomplete="url" placeholder="www.yourschool.org.uk" style="font-family:Satoshi,sans-serif;font-size:16px;letter-spacing:0;text-transform:none;color:#1B2536;background:#fff;border:1px solid #C9C2B2;border-radius:8px;padding:13px 14px;width:100%">
        </label>
        <label :style="mono + ';display:flex;flex-direction:column;gap:8px;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#8A8474'">Work email
          <input v-model="previewEmail" data-preview-input="" type="email" autocomplete="email" placeholder="you@yourschool.org.uk" style="font-family:Satoshi,sans-serif;font-size:16px;letter-spacing:0;text-transform:none;color:#1B2536;background:#fff;border:1px solid #C9C2B2;border-radius:8px;padding:13px 14px;width:100%">
        </label>
        <label style="display:flex;gap:10px;align-items:flex-start;font-size:14px;line-height:1.5;color:#4A5468;cursor:pointer"><input v-model="previewConfirmed" type="checkbox" style="margin-top:3px;accent-color:#B4702F;width:16px;height:16px;flex-shrink:0"><!-- If this wording changes, change PreviewLead::CONSENT_VERSION in ImageSafe. -->
          <span>I work for this school or trust and have permission to have its website scanned. I agree that Comordo may contact me at this email address about ImageSafe and its other products, and I've read the <a href="/privacy" target="_blank" rel="noopener" style="font-weight:500">privacy policy</a>. You can opt out at any time.</span></label>
        <p v-if="previewError" role="alert" data-preview-error="">Please enter a full school website address, like <b>www.yourschool.org.uk</b>.</p>
        <p v-if="previewEmailError" role="alert" data-preview-error="">{{ errorCopy.email }}</p>
        <p v-if="previewNeedsConfirm" role="alert" data-preview-error="">Please tick the box to agree before we scan this website.</p>
        <p v-if="previewFailure" role="alert" data-preview-error="">{{ errorCopy[previewFailure] }}</p>
        <button type="submit" data-btn="" :disabled="previewStarting" :style="`font-family:Satoshi,sans-serif;background:#B4702F;color:#fff;border:1px solid transparent;border-radius:10px;padding:15px 20px;font-size:16px;font-weight:500;cursor:${previewStarting ? 'progress' : 'pointer'};margin-top:4px`">{{ previewStarting ? 'Scanning your first pages…' : 'Scan my website now →' }}</button>
        <p style="margin:0;font-size:13px;color:#8A8474;line-height:1.5">Already have an account? <a :href="signInHref" style="font-weight:500">Sign in</a> to run a full scan of your school. Prefer a conversation? <Link href="/demo" style="font-weight:500">Book a demo</Link></p>
      </form>
    </div>
  </section>
</template>

<style scoped>
[data-band] { padding-top: 96px; padding-bottom: 96px; }
a { color: #B4702F; text-decoration: none; }
a:hover { color: #8F5722; }
[data-reveal] { opacity: 0; transform: translateY(22px); transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1); }
[data-reveal][data-in] { opacity: 1; transform: none; }
[data-btn] { white-space: nowrap; transition: background-color .2s, color .2s, border-color .2s, transform .25s cubic-bezier(.2,.7,.2,1); }
[data-btn]:hover { transform: translateY(-1px); }
[data-preview-input]:focus { outline: 2px solid #B4702F; outline-offset: 1px; }
[data-preview-error] { margin: 0; padding: 12px 14px; background: #F4E2DC; border-radius: 8px; font-size: 14px; line-height: 1.5; color: #9A3B2A; }
@media (max-width: 900px) {
  [data-cols] { grid-template-columns: minmax(0,1fr) !important; }
  [data-band] { padding-top: 64px; padding-bottom: 64px; }
  [data-btn] { font-size: 14px !important; padding: 9px 14px !important; border-radius: 9px !important; }
  [data-h2] { font-size: 26px !important; }
  [data-lead] { font-size: 16px !important; }
  [data-pad] { padding-left: 20px !important; padding-right: 20px !important; }
}
@media (prefers-reduced-motion: reduce) {
  [data-reveal] { transition: none; transform: none; }
}
</style>
