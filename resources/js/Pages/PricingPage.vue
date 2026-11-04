<script setup>
// ImageSafe pricing: the estimate calculator from Designs/Imagesafe/Pricing.dc.html, laid out
// with the ImageSafe homepage's bands, type, cards and buttons (ImageSafePage.vue).
// Styles stay inline, as on the homepage, so values can be checked against it one to one.
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { identityStartHref } from '@/identity.js'
import PreviewBand from '../Components/PreviewBand.vue'
import SiteFooter from '../Components/SiteFooter.vue'
import SiteHeader from '../Components/SiteHeader.vue'

const trialHref = identityStartHref

// --- Estimate calculator ---
// A licence is £150 per school a year. A volume discount (6 / 12 / 16%) plus a flat 4%
// applies from 10 schools, so the visible bands are 0 / 10 / 16 / 20% off.
// Same numbers as SignUpPage.vue.
const BASE = 150
const OVERALL = 0.04
const mode = ref('trust') // trust | single
const schools = ref(12)
const billing = ref('monthly') // monthly | yearly

const disc = computed(() => {
  const s = schools.value
  const vol = s >= 30 ? 0.16 : s >= 20 ? 0.12 : s >= 10 ? 0.06 : 0
  return vol > 0 ? vol + OVERALL : 0
})
const div = computed(() => (billing.value === 'monthly' ? 12 : 1))
const cadence = computed(() => (billing.value === 'monthly' ? { short: 'month', per: 'a month' } : { short: 'year', per: 'a year' }))
const gbp = n => '£' + Number(n).toLocaleString('en-GB', { minimumFractionDigits: n % 1 ? 2 : 0, maximumFractionDigits: 2 })
const perSchool = computed(() => (BASE * (1 - disc.value)) / div.value)
const baseRate = computed(() => BASE / div.value)
const total = computed(() => perSchool.value * schools.value)
const savings = computed(() => (baseRate.value - perSchool.value) * schools.value)
const bands = computed(() => {
  const s = schools.value
  return [
    // start: where the slider jumps when the band is clicked.
    { range: '2–9', off: 'Full price', start: 2, active: s <= 9 },
    { range: '10–19', off: '10% off', start: 10, active: s >= 10 && s <= 19 },
    { range: '20–29', off: '16% off', start: 20, active: s >= 20 && s <= 29 },
    { range: '30+', off: '20% off', start: 30, active: s >= 30 },
  ]
})

// Every claim here is one the ImageSafe homepage already makes.
const included = [
  { title: 'Find photos you may have forgotten', body: 'Scan your website, prospectuses and newsletters, with weekly checks for new images.' },
  { title: 'Check who’s in them', body: 'Match photos against permission records, including separate website and social media consent.' },
  { title: 'Spot safeguarding risks', body: 'Flag close-ups, named photos and potential AI misuse risks for your staff to review.' },
  { title: 'Create safer versions', body: 'Blur faces without consent and remove hidden location data in a click.' },
  { title: 'See where everyone appears', body: 'Find every photo linked to a pupil or member of staff, all in one place.' },
  { title: 'Keep a clear record', body: 'Track reviews, decisions and actions, with reports for safeguarding teams and governors.' },
]

// Questions people ask before buying. The answers are the homepage FAQ's, word for word.
const faqs = [
  { q: 'What’s the difference between the free preview and the trial?', a: 'The preview reads the first public pages of your website, counts the photos containing people and shows the checks they would raise. It does not identify anyone or need an account, your photos are never stored, and the results are deleted within 24 hours. A full scan, with permission matching, needs the 7-day trial.' },
  { q: 'Does it connect to our MIS?', a: 'Today, consent records come in by CSV import, using the template we provide; most schools export this from their MIS in a few minutes. Direct connections for Arbor, Bromcom and SIMS are in development.', link: { href: 'mailto:hello@comordo.com?subject=MIS%20connection%20interest', label: 'Register your interest', after: ' and we will tell you when yours is ready.' } },
]
function closeOtherFaqs(e) {
  if (!e.target.open) return
  e.currentTarget.querySelectorAll('details[open]').forEach((d) => { if (d !== e.target) d.open = false })
}

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
const label = `margin:0;${mono};font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#8A8474`
const darkLabel = `margin:0;${mono};font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#E7C9A8`
const seg = on => `flex:1;font-family:Satoshi,sans-serif;font-size:15px;font-weight:500;padding:10px 16px;border-radius:8px;border:0;cursor:pointer;white-space:nowrap;${on ? 'background:#1B2536;color:#fff' : 'background:transparent;color:#4A5468'}`
// Stacks a calculator state in a shared grid cell; the inactive one is hidden but keeps its size.
const layer = on => `grid-area:1/1;visibility:${on ? 'visible' : 'hidden'}`
const segWrap = 'display:flex;gap:4px;background:#fff;border:1px solid #C9C2B2;border-radius:10px;padding:4px'
</script>

<template>
  <Head title="Pricing — ImageSafe by Comordo" />
  <div ref="root" style="position:relative;min-height:100vh;background:#F8F6F1;color:#4A5468;overflow-x:clip">
    <div aria-hidden="true" style="position:absolute;inset:0 0 auto 0;height:816px;background:radial-gradient(70% 60% at 50% 0%,rgba(231,201,168,.35),transparent 70%);pointer-events:none"></div>

    <SiteHeader />

    <main id="top">
      <!-- Hero -->
      <section style="position:relative">
        <div data-pad="" style="position:relative;max-width:var(--is-wide,1180px);margin:0 auto;padding:64px 32px 0">
          <div style="max-width:min(100%,27em);font-size:clamp(40px,4.2vw,84px);margin:0 auto;display:flex;flex-direction:column;align-items:center;text-align:center;gap:30px">
            <h1 data-h1="" style="margin:0;font-size:clamp(40px,4.2vw,84px);line-height:1.02;letter-spacing:-.025em;font-weight:700;color:#1B2536;text-wrap:balance">Image safeguarding from <br>£12.50 per school a month.</h1>
            <p data-hero-p="" style="margin:0;font-size:18px;line-height:1.6;max-width:66.666%;text-wrap:pretty">Whether you’re one school or part of a trust, you get everything ImageSafe has to offer. No setup fees or complicated plans. And the more schools in your trust, the less you pay per school.</p>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-top:10px">
              <a :href="trialHref" data-btn="" data-hero-btn="" style="display:inline-flex;align-items:center;gap:10px;background:#B4702F;color:#fff;font-weight:500;font-size:16px;padding:12px 20px;border-radius:9px;border:1px solid transparent">Start your 7-day free trial <span aria-hidden="true">→</span></a>
              <a href="#estimate" data-btn="" data-hero-btn="" style="display:inline-flex;align-items:center;gap:10px;background:#fff;color:#1B2536;font-weight:500;font-size:16px;padding:12px 18px;border-radius:9px;border:1px solid #C9C2B2">Estimate your price</a>
            </div>
            <p style="margin:-12px 0 0;font-size:14px;color:#8A8474">No card needed · Down to £10 per school a month as your trust grows</p>
          </div>
        </div>
      </section>

      <!-- Estimate calculator -->
      <section id="estimate" data-band="" style="position:relative;scroll-margin-top:40px">
        <div data-pad="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:0 32px">
          <div data-reveal="" data-half="" style="max-width:66.666%">
            <h2 data-h2="" style="margin:0 0 16px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#1B2536;text-wrap:pretty">See what ImageSafe would cost your school or trust</h2>
            <p data-lead="" style="margin:0;font-size:18px;line-height:1.6;text-wrap:pretty">Use the slider to choose your number of schools and see your price instantly. The more schools you have, the less you pay per school.</p>
          </div>

          <div data-reveal="" style="margin-top:48px;background:#fff;border:1px solid #E7E2D6;border-radius:14px;box-shadow:0 30px 60px -30px rgba(27,37,54,.25);overflow:hidden">
            <!-- Toggles -->
            <div data-calc-top="" style="padding:24px 32px;border-bottom:1px solid #E7E2D6;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
              <div role="group" aria-label="School or trust" :style="segWrap + ';flex:1 1 300px;max-width:440px'">
                <button type="button" data-seg="" :aria-pressed="mode === 'single'" :style="seg(mode === 'single')" @click="mode = 'single'">Single school</button>
                <button type="button" data-seg="" :aria-pressed="mode === 'trust'" :style="seg(mode === 'trust')" @click="mode = 'trust'">Multi-academy trust</button>
              </div>
              <div style="display:flex;align-items:center;gap:12px">
                <p :style="label">Billing</p>
                <div role="group" aria-label="Billing" :style="segWrap">
                  <button type="button" data-seg="" :aria-pressed="billing === 'monthly'" :style="seg(billing === 'monthly')" @click="billing = 'monthly'">Monthly</button>
                  <button type="button" data-seg="" :aria-pressed="billing === 'yearly'" :style="seg(billing === 'yearly')" @click="billing = 'yearly'">Yearly</button>
                </div>
              </div>
            </div>

            <div data-cols="" data-calc-body="" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:56px;align-items:center;padding:32px">
              <!-- Both modes are laid out in the same cell and the hidden one keeps its space,
                   so the calculator is always as tall as its tallest state. -->
              <div style="display:grid;align-items:center">
                <!-- Trust: slider and bands -->
                <div :inert="mode !== 'trust'" :style="layer(mode === 'trust') + ';display:flex;flex-direction:column;gap:22px'">
                <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px">
                  <label for="calc-schools" style="font-size:20px;font-weight:700;color:#1B2536">How many schools in your trust?</label>
                  <p style="margin:0;display:flex;align-items:baseline;gap:6px"><!-- Fixed width, so the question beside it wraps the same for 2 and 80. -->
                    <span style="min-width:2ch;text-align:right;font-variant-numeric:tabular-nums;font-size:40px;line-height:1;font-weight:700;letter-spacing:-.02em;color:#1B2536">{{ schools }}</span><span style="font-size:14px;color:#8A8474">schools</span></p>
                </div>
                <div>
                  <input id="calc-schools" v-model.number="schools" type="range" min="2" max="80" step="1" style="width:100%;accent-color:#B4702F;cursor:pointer">
                  <div :style="`display:flex;justify-content:space-between;${mono};font-size:11px;color:#8A8474;margin-top:8px`"><span>2</span><span>80</span></div>
                </div>
                <div data-bands="" role="group" aria-label="Jump to a price band" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;background:#E7E2D6;border:1px solid #E7E2D6;border-radius:12px;overflow:hidden">
                  <button v-for="b in bands" :key="b.range" type="button" data-band-btn="" :aria-pressed="b.active" :aria-label="`${b.range} schools, ${b.off}`" :style="`font-family:Satoshi,sans-serif;border:0;cursor:pointer;background:${b.active ? '#F9F1E6' : '#fff'};padding:14px 8px;display:flex;flex-direction:column;align-items:center;gap:4px;text-align:center`" @click="schools = b.start">
                    <span :style="`${mono};font-size:11px;color:${b.active ? '#B4702F' : '#8A8474'}`">{{ b.range }}</span>
                    <span :style="`font-size:14px;font-weight:${b.active ? 700 : 500};color:${b.active ? '#1B2536' : '#4A5468'}`">{{ b.off }}</span>
                  </button>
                </div>
                <p style="margin:0;font-size:14px;line-height:1.6;color:#8A8474">Estimates use £12.50 per school a month (£150 a year) and the bands above. Need something different? <Link href="/contact" style="font-weight:500">Talk to us</Link>.</p>
              </div>

                <!-- Single school -->
                <div :inert="mode !== 'single'" :style="layer(mode === 'single') + ';display:flex;flex-direction:column;gap:14px'">
                <h3 style="margin:0;font-size:24px;line-height:1.2;letter-spacing:-.01em;font-weight:700;color:#1B2536">One school</h3>
                <p style="margin:0;font-size:16px;line-height:1.6;text-wrap:pretty">A single school reviewing and recording its own image decisions, with all of ImageSafe. One flat price, billed monthly or yearly.</p>
                <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px;font-size:16px;line-height:1.6">
                  <li style="display:flex;gap:10px"><span style="color:#B4702F">—</span>Scanning, permission matching and safeguarding checks</li>
                  <li style="display:flex;gap:10px"><span style="color:#B4702F">—</span>Safer versions in a click</li>
                  <li style="display:flex;gap:10px"><span style="color:#B4702F">—</span>Audit log and governors’ reports</li>
                </ul>
                <p style="margin:0;font-size:14px;line-height:1.6;color:#8A8474">Estimates use £12.50 a month (£150 a year). Need something different? <Link href="/contact" style="font-weight:500">Talk to us</Link>.</p>
              </div>
              </div>

              <!-- Result -->
              <div data-result-stack="" style="display:grid">
                <div aria-live="polite" data-result="" :inert="mode !== 'trust'" :style="layer(mode === 'trust') + ';background:#1B2536;color:#fff;border-radius:14px;padding:32px;display:flex;flex-direction:column;gap:16px'">
                  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                    <p :style="darkLabel">Per school</p>
                    <span :style="`${mono};font-size:11px;color:#fff;background:#B4702F;padding:4px 10px;border-radius:99px;visibility:${disc > 0 ? 'visible' : 'hidden'}`">{{ Math.round(disc * 100) }}% off</span>
                  </div>
                  <p style="margin:0;display:flex;align-items:baseline;gap:10px;flex-wrap:wrap">
                    <span style="font-size:28px;font-weight:700;letter-spacing:-.01em">{{ gbp(perSchool) }}</span>
                    <s v-if="disc > 0" style="font-size:16px;color:#8A8474">{{ gbp(baseRate) }}</s>
                    <!-- On its own line, so the height never depends on how long the price is. -->
                    <span style="flex-basis:100%;font-size:14px;color:#C9C2B2">/ school · {{ cadence.short }} · × {{ schools }} schools</span>
                  </p>
                  <div style="height:1px;background:#2A3650"></div>
                  <p :style="darkLabel">Estimated total</p>
                  <p style="margin:0;display:flex;align-items:baseline;gap:8px;flex-wrap:wrap"><span style="font-size:clamp(36px,3.4vw,48px);line-height:1;font-weight:700;letter-spacing:-.02em">{{ gbp(total) }}</span><span style="font-size:16px;color:#C9C2B2">/ {{ cadence.short }}</span></p>
                  <!-- Kept in the layout at full price too, so crossing a band doesn't change the height. -->
                  <p :style="`margin:0;font-size:16px;line-height:1.6;color:#E7C9A8;font-weight:500;white-space:nowrap;visibility:${disc > 0 ? 'visible' : 'hidden'}`">Total saving: {{ gbp(savings) }} {{ cadence.per }}</p>
                  <a :href="trialHref" data-btn="" style="margin-top:auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#B4702F;color:#fff;font-weight:500;font-size:16px;padding:14px 20px;border-radius:10px;border:1px solid transparent">Start your 7-day free trial <span aria-hidden="true">→</span></a>
                  <p style="margin:0;font-size:14px;color:#C9C2B2;text-align:center">No card needed · First scan in minutes</p>
                </div>
                <div aria-live="polite" data-result="" :inert="mode !== 'single'" :style="layer(mode === 'single') + ';background:#1B2536;color:#fff;border-radius:14px;padding:32px;display:flex;flex-direction:column;gap:16px'">
                  <p :style="darkLabel">Estimated total</p>
                  <p style="margin:0;display:flex;align-items:baseline;gap:8px;flex-wrap:wrap"><span style="font-size:clamp(36px,3.4vw,48px);line-height:1;font-weight:700;letter-spacing:-.02em">{{ gbp(baseRate) }}</span><span style="font-size:16px;color:#C9C2B2">/ {{ cadence.short }}</span></p>
                  <p style="margin:0;font-size:16px;line-height:1.6;color:#C9C2B2">For one school, with all of ImageSafe.</p>
                  <a :href="trialHref" data-btn="" style="margin-top:auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#B4702F;color:#fff;font-weight:500;font-size:16px;padding:14px 20px;border-radius:10px;border:1px solid transparent">Start your 7-day free trial <span aria-hidden="true">→</span></a>
                  <p style="margin:0;font-size:14px;color:#C9C2B2;text-align:center">No card needed · First scan in minutes</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- What's included -->
      <section data-band="" style="background:#fff;border-top:1px solid #E7E2D6">
        <div data-pad="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:0 32px">
          <div data-reveal="" data-half="" style="max-width:66.666%">
            <h2 data-h2="" style="margin:0 0 16px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#1B2536;text-wrap:pretty">Everything included. For every school.</h2>
            <p data-lead="" style="margin:0;font-size:18px;line-height:1.6;text-wrap:pretty">Every school gets the same tools to find, review and manage photos of pupils and staff.</p>
          </div>
          <div data-inc3="" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1px;background:#E7E2D6;border:1px solid #E7E2D6;border-radius:12px;overflow:hidden;margin-top:48px">
            <div v-for="f in included" :key="f.title" data-reveal="" style="background:#fff;padding:32px 28px;display:flex;flex-direction:column;gap:10px">
              <h3 style="margin:0;font-size:22px;font-weight:700;color:#1B2536;letter-spacing:-.01em">{{ f.title }}</h3>
              <p style="margin:0;font-size:16px;line-height:1.6;text-wrap:pretty">{{ f.body }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- FAQ: same layout as the homepage's -->
      <section data-band="" style="border-top:1px solid #E7E2D6">
        <div data-pad="" data-cols="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:0 32px;display:grid;grid-template-columns:minmax(0,2fr) minmax(0,3fr);gap:56px;align-items:start">
          <div data-reveal="">
            <h2 data-h2="" style="margin:0 0 16px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#1B2536;text-wrap:pretty">Before you start</h2>
            <p data-lead="" style="margin:0;font-size:18px;line-height:1.6">Anything else, <Link href="/contact" style="font-weight:500">ask us directly</Link>.</p>
          </div>
          <div style="display:flex;flex-direction:column;gap:10px" @toggle.capture="closeOtherFaqs">
            <details v-for="f in faqs" :key="f.q" name="faq" data-reveal="" style="background:#fff;border:1px solid #E7E2D6;border-radius:12px;padding:0 24px">
              <summary style="list-style:none;cursor:pointer;display:flex;align-items:center;gap:16px;padding:20px 0;font-size:17px;font-weight:700;color:#1B2536">{{ f.q }}<span aria-hidden="true" style="margin-left:auto;color:#B4702F;font-size:20px;line-height:1;flex-shrink:0">+</span></summary>
              <p style="margin:0;padding:0 0 22px;font-size:16px;line-height:1.6;color:#4A5468;text-wrap:pretty">{{ f.a }}<template v-if="f.link">{{ ' ' }}<a :href="f.link.href" style="font-weight:500">{{ f.link.label }}</a>{{ f.link.after }}</template></p>
            </details>
          </div>
        </div>
      </section>

      <!-- Free preview -->
      <PreviewBand
        heading="See what ImageSafe finds on your website."
        lead="Enter your school’s website address to get a quick preview of the photos and potential safeguarding concerns on its first public pages."
        :points="['No account needed, no photos stored and nothing changed on your website.']"
      />
    </main>

    <SiteFooter />
  </div>
</template>

<style scoped>
/* Same band spacing, links, buttons, reveal and breakpoints as the homepage. */
[data-band] { padding-top: 96px; padding-bottom: 96px; }
a { color: #B4702F; text-decoration: none; }
a:hover { color: #8F5722; }
[data-reveal] { opacity: 0; transform: translateY(22px); transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1); }
[data-reveal][data-in] { opacity: 1; transform: none; }
[data-btn] { white-space: nowrap; transition: background-color .2s, color .2s, border-color .2s, transform .25s cubic-bezier(.2,.7,.2,1); }
[data-btn]:hover { transform: translateY(-1px); }
[data-seg] { transition: background-color .2s, color .2s; }
[data-band-btn] { transition: background-color .2s; }
[data-band-btn][aria-pressed="false"]:hover { background: #F8F6F1 !important; }
[data-band-btn]:focus-visible { outline-offset: -2px; }
summary::-webkit-details-marker { display: none; }
summary span[aria-hidden] { transition: transform .2s; }
details[open] summary span[aria-hidden] { transform: rotate(45deg); }
input[type="range"]:focus-visible, button:focus-visible, a:focus-visible { outline: 2px solid #B4702F; outline-offset: 3px; }
@media (max-width: 1180px) {
  [data-inc3] { grid-template-columns: repeat(2,minmax(0,1fr)) !important; }
}
@media (max-width: 900px) {
  [data-inc3] { grid-template-columns: minmax(0,1fr) !important; }
  [data-cols] { grid-template-columns: minmax(0,1fr) !important; gap: 32px !important; }
  [data-half] { max-width: none !important; }
  [data-hero-p] { max-width: none !important; font-size: 16px !important; }
  [data-band] { padding-top: 64px; padding-bottom: 64px; }
  [data-btn] { font-size: 14px !important; padding: 9px 14px !important; border-radius: 9px !important; }
  [data-h1] { font-size: 34px !important; }
  [data-h1] br { display: none; }
  [data-h2] { font-size: 26px !important; }
  [data-lead] { font-size: 16px !important; }
  [data-pad] { padding-left: 20px !important; padding-right: 20px !important; }
}
@media (max-width: 480px) {
  [data-bands] { grid-template-columns: repeat(2,minmax(0,1fr)) !important; }
  [data-calc-top], [data-calc-body] { padding: 20px !important; }
  [data-result] { padding: 24px !important; }
}
@media (prefers-reduced-motion: reduce) {
  [data-reveal] { transition: none; transform: none; }
}
</style>
