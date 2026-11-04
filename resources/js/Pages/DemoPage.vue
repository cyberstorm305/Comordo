<script setup>
// Book a demo. Laid out with the ImageSafe homepage's hero, bands and buttons, and the
// free preview form's fields (PreviewBand.vue). Styles stay inline, as on the homepage.
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteFooter from '../Components/SiteFooter.vue'
import SiteHeader from '../Components/SiteHeader.vue'

const sent = ref(false)
const form = ref({ name: '', email: '', org: '', schools: '2–9', slot: 'Morning' })
const err = ref(false)
function submit() {
  if (!form.value.name.trim() || !/.+@.+\..+/.test(form.value.email.trim())) { err.value = true; return }
  err.value = false
  sent.value = true // wire to your booking endpoint / calendar link
}

const expect = [
  'Video call · no slides you could have read yourself',
  'Bring your DSL, communications or governance colleagues',
  'No obligation, no chasing afterwards',
]

// Reveal on scroll, as on the homepage.
const root = ref(null)
let reveal
onMounted(() => {
  reveal = new IntersectionObserver((es) => es.forEach((e) => {
    if (e.isIntersecting) { e.target.setAttribute('data-in', ''); reveal.unobserve(e.target) }
  }), { threshold: 0.12 })
  root.value.querySelectorAll('[data-reveal]').forEach(r => reveal.observe(r))
})
onBeforeUnmount(() => reveal?.disconnect())

const mono = "font-family:'IBM Plex Mono',monospace"
const fieldLabel = `${mono};display:flex;flex-direction:column;gap:8px;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#8A8474`
const field = 'font-family:Satoshi,sans-serif;font-size:16px;letter-spacing:0;text-transform:none;color:#1B2536;background:#fff;border:1px solid #C9C2B2;border-radius:8px;padding:13px 14px;width:100%'
</script>

<template>
  <Head title="Book a demo — ImageSafe by Comordo" />
  <div ref="root" style="position:relative;min-height:100vh;background:#F8F6F1;color:#4A5468;overflow-x:clip">
    <div aria-hidden="true" style="position:absolute;inset:0 0 auto 0;height:816px;background:radial-gradient(70% 60% at 50% 0%,rgba(231,201,168,.35),transparent 70%);pointer-events:none"></div>

    <SiteHeader />

    <main id="top">
      <!-- Hero -->
      <section style="position:relative">
        <div data-pad="" style="position:relative;max-width:var(--is-wide,1180px);margin:0 auto;padding:64px 32px 0">
          <div style="max-width:min(100%,27em);font-size:clamp(40px,4.2vw,84px);margin:0 auto;display:flex;flex-direction:column;align-items:center;text-align:center;gap:30px">
            <h1 data-h1="" style="margin:0;font-size:clamp(40px,4.2vw,84px);line-height:1.02;letter-spacing:-.025em;font-weight:700;color:#1B2536;text-wrap:balance">A 30-minute walkthrough, <br>tailored to your trust.</h1>
            <p data-hero-p="" style="margin:0;font-size:18px;line-height:1.6;max-width:66.666%;text-wrap:pretty">We’ll walk through ImageSafe with your own context in mind: your schools, your team and your safeguarding workflow.</p>
          </div>
        </div>
      </section>

      <!-- Request form -->
      <section data-band="" style="position:relative">
        <div data-pad="" data-cols="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:0 32px;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:56px;align-items:center">
          <div data-reveal="">
            <h2 data-h2="" style="margin:0 0 16px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#1B2536;text-wrap:balance">What to expect</h2>
            <p data-lead="" style="margin:0 0 22px;font-size:18px;line-height:1.6;text-wrap:pretty">Tell us a little about your school or trust and when suits you. We’ll reply with a couple of times within one working day.</p>
            <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px;font-size:16px;line-height:1.6">
              <li v-for="item in expect" :key="item" style="display:flex;gap:10px"><span style="color:#B4702F">—</span>{{ item }}</li>
            </ul>
          </div>

          <div data-reveal="" style="background:#fff;border:1px solid #E7E2D6;border-radius:14px;padding:32px;box-shadow:0 30px 60px -30px rgba(27,37,54,.25)">
            <form v-if="!sent" novalidate style="display:flex;flex-direction:column;gap:16px" @submit.prevent="submit">
              <p style="margin:0;font-size:20px;font-weight:700;color:#1B2536">Request a demo</p>
              <div data-pair="" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px">
                <label :style="fieldLabel">Your name<input v-model="form.name" data-field="" autocomplete="name" placeholder="e.g. Jane Smith" :style="field"></label>
                <label :style="fieldLabel">Work email<input v-model="form.email" data-field="" type="email" autocomplete="email" placeholder="you@yourtrust.org.uk" :style="field"></label>
              </div>
              <label :style="fieldLabel">School or trust<input v-model="form.org" data-field="" autocomplete="organization" placeholder="e.g. Meridian Learning Trust" :style="field"></label>
              <div data-pair="" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px">
                <label :style="fieldLabel">Schools
                  <select v-model="form.schools" data-field="" :style="field + ';cursor:pointer'"><option>1</option><option>2–9</option><option>10–19</option><option>20–29</option><option>30+</option></select>
                </label>
                <label :style="fieldLabel">Preferred time
                  <select v-model="form.slot" data-field="" :style="field + ';cursor:pointer'"><option>Morning</option><option>Lunchtime</option><option>Afternoon</option></select>
                </label>
              </div>
              <p v-if="err" role="alert" data-form-error="">Please add your name and a valid email address.</p>
              <button type="submit" data-btn="" style="font-family:Satoshi,sans-serif;background:#B4702F;color:#fff;border:1px solid transparent;border-radius:10px;padding:15px 20px;font-size:16px;font-weight:500;cursor:pointer;margin-top:4px">Request a demo →</button>
              <p style="margin:0;font-size:13px;color:#8A8474;line-height:1.5;text-align:center">We’ll reply with a couple of times within one working day.</p>
            </form>
            <div v-else role="status" style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:16px;padding:40px 0">
              <span aria-hidden="true" style="width:60px;height:60px;border-radius:50%;background:#E7F0EA;display:flex;align-items:center;justify-content:center"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#3F7E63" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5" /></svg></span>
              <h2 style="margin:0;font-size:24px;line-height:1.2;letter-spacing:-.01em;font-weight:700;color:#1B2536">Demo requested.</h2>
              <p style="margin:0;max-width:380px;font-size:16px;line-height:1.6">Thanks {{ form.name.trim().split(' ')[0] }}. We’ll email <b style="color:#1B2536">{{ form.email }}</b> with a couple of times to choose from.</p>
              <Link href="/" data-btn="" style="display:inline-flex;align-items:center;gap:10px;background:#fff;color:#1B2536;font-weight:500;font-size:16px;padding:12px 18px;border-radius:9px;border:1px solid #C9C2B2;margin-top:8px">Explore ImageSafe meanwhile</Link>
            </div>
          </div>
        </div>
      </section>
    </main>

    <SiteFooter />
  </div>
</template>

<style scoped>
/* Same band spacing, links, buttons, fields, reveal and breakpoints as the homepage. */
[data-band] { padding-top: 96px; padding-bottom: 96px; }
a { color: #B4702F; text-decoration: none; }
a:hover { color: #8F5722; }
[data-reveal] { opacity: 0; transform: translateY(22px); transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1); }
[data-reveal][data-in] { opacity: 1; transform: none; }
[data-btn] { white-space: nowrap; transition: background-color .2s, color .2s, border-color .2s, transform .25s cubic-bezier(.2,.7,.2,1); }
[data-btn]:hover { transform: translateY(-1px); }
[data-field]:focus { outline: 2px solid #B4702F; outline-offset: 1px; }
[data-form-error] { margin: 0; padding: 12px 14px; background: #F4E2DC; border-radius: 8px; font-size: 14px; line-height: 1.5; color: #9A3B2A; }
@media (max-width: 900px) {
  [data-cols] { grid-template-columns: minmax(0,1fr) !important; }
  [data-hero-p] { max-width: none !important; font-size: 16px !important; }
  [data-band] { padding-top: 64px; padding-bottom: 64px; }
  [data-btn] { font-size: 14px !important; padding: 9px 14px !important; border-radius: 9px !important; }
  [data-h1] { font-size: 34px !important; }
  [data-h1] br { display: none; }
  [data-h2] { font-size: 26px !important; }
  [data-lead] { font-size: 16px !important; }
  [data-pad] { padding-left: 20px !important; padding-right: 20px !important; }
}
@media (max-width: 560px) {
  [data-pair] { grid-template-columns: minmax(0,1fr) !important; }
}
@media (prefers-reduced-motion: reduce) {
  [data-reveal] { transition: none; transform: none; }
}
</style>
