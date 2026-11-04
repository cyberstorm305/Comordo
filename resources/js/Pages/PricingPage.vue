<script setup>
// ImageSafe pricing, built from the hero and estimate sections of Designs/Imagesafe/Pricing.dc.html.
// Styles stay inline, as in the design, so values can be checked against it one to one.
// The copy is ImageSafe's: the design was drafted with Compli wording (scan credits, DfE library).
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { identityStartHref } from '@/identity.js'
import SiteFooter from '../Components/SiteFooter.vue'
import SiteHeader from '../Components/SiteHeader.vue'

const trialHref = identityStartHref

// --- Estimate calculator ---
// Base licence is £150 per school a year. A volume discount (6 / 12 / 16%) plus a flat 4%
// applies once a trust reaches 10 schools, so the visible bands are 0 / 10 / 16 / 20% off.
// Same numbers as SignUpPage.vue.
const BASE = 150
const OVERALL = 0.04
const mode = ref('mat') // mat | single
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
    { range: '2–9', off: 'Full price', active: s <= 9 },
    { range: '10–19', off: '10% off', active: s >= 10 && s <= 19 },
    { range: '20–29', off: '16% off', active: s >= 20 && s <= 29 },
    { range: '30+', off: '20% off', active: s >= 30 },
  ]
})

const summary = [
  'Website, prospectus & newsletter scanning',
  'Permission matching against your consent records',
  'Safeguarding checks on every photo of people',
  'Safer versions in a click',
  'Decision record & audit log',
]
const included = [
  'Website, prospectus & newsletter scanning',
  'Permission matching: website & social media',
  'Safeguarding checks on every photo of people',
  'Potential AI-misuse risk flags',
  'Safer versions: blur faces, remove location data',
  'People profiles that track every appearance',
  'Weekly re-scans',
  'Decision record & audit log',
  "Governors' reports",
  'Trust & school dashboards',
]

const mono = "font-family:'IBM Plex Mono',monospace"
const eyebrow = `display:flex;align-items:center;gap:10px;${mono};font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:#B4702F`
const pill = `display:flex;align-items:center;gap:8px;${mono};font-size:11.5px;color:#4A5468;padding:7px 13px;border:1px solid #DAD3C4;border-radius:99px;background:#fff`
const tick = 'width:22px;height:22px;border-radius:50%;background:#F0EBDF;color:#B4702F;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0'
const seg = on => `flex:1;text-align:center;padding:11px 16px;border-radius:7px;font-size:14px;font-weight:600;cursor:pointer;user-select:none;border:0;font-family:inherit;${on ? 'background:#1B2536;color:#F8F6F1' : 'background:transparent;color:#4A5468'}`
const segSm = on => `padding:8px 15px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;user-select:none;border:0;font-family:inherit;${on ? 'background:#1B2536;color:#F8F6F1' : 'background:transparent;color:#4A5468'}`
const smallLabel = `${mono};font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#B4702F`
</script>

<template>
  <Head title="Pricing — ImageSafe by Comordo" />
  <div style="position:relative;min-height:100vh;background:#F8F6F1;color:#1B2536;overflow-x:clip">
    <SiteHeader />

    <main id="top">
      <!-- Hero + plans -->
      <section>
        <div data-pad="" data-cols="" style="max-width:var(--is-wide,1180px);margin:0 auto;display:grid;grid-template-columns:1.05fr .95fr;gap:clamp(36px,5vw,64px);padding:clamp(52px,8vw,80px) 32px clamp(36px,5vw,52px);align-items:center">
          <div style="display:flex;flex-direction:column;gap:22px">
            <div :style="eyebrow"><div style="width:24px;height:1px;background:#B4702F"></div>Pricing</div>
            <h1 style="font-size:clamp(32px,5.5vw,52px);font-weight:700;line-height:1.06;letter-spacing:-.025em;margin:0;text-wrap:balance">Priced for one school, or a whole trust.</h1>
            <p style="margin:0;font-size:17px;line-height:1.6;color:#4A5468;max-width:540px;text-wrap:pretty">One ImageSafe licence per school, billed monthly or yearly. Every school gets the full platform. The only thing that changes is the price per school, which falls as your trust grows. Same product for everyone; larger groups simply pay less.</p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:2px">
              <div v-for="p in ['7-day free trial', 'No setup fee', 'Add schools any time']" :key="p" :style="pill"><div style="width:6px;height:6px;border-radius:50%;background:#B4702F"></div>{{ p }}</div>
            </div>
          </div>

          <!-- Summary card -->
          <div style="background:#fff;border-radius:14px;box-shadow:0 18px 50px rgba(27,37,54,.08);padding:clamp(30px,4vw,40px) clamp(28px,3.5vw,38px);display:flex;flex-direction:column;gap:22px">
            <div style="display:flex;flex-direction:column;gap:8px">
              <div :style="smallLabel">The complete platform</div>
              <div style="font-size:20px;font-weight:700;letter-spacing:-.01em">Every school gets everything</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px">
              <div v-for="s in summary" :key="s" style="display:flex;gap:12px;align-items:center"><div :style="tick" aria-hidden="true">✓</div><div style="font-size:14.5px;font-weight:600">{{ s }}</div></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;padding-top:20px;border-top:1px solid #F2EEE3">
              <div style="display:flex;align-items:baseline;gap:8px"><div style="font-size:32px;font-weight:700;letter-spacing:-.02em">£12.50</div><div style="font-size:13.5px;color:#8A8474">per school · month</div></div>
              <div style="font-size:13px;color:#8A8474">Billed yearly at £150 — down to £10 / month as your trust grows.</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Estimate calculator -->
      <section id="estimate" style="border-bottom:1px solid #E7E2D6">
        <div data-pad="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:clamp(8px,2vw,20px) 32px clamp(56px,9vw,96px)">
          <div style="max-width:680px;display:flex;flex-direction:column;gap:14px;margin-bottom:28px">
            <h2 style="margin:0;font-size:clamp(21px,3.4vw,27px);font-weight:700;line-height:1.2;letter-spacing:-.02em">Estimate your price.</h2>
            <p style="margin:0;font-size:15.5px;line-height:1.65;color:#4A5468;text-wrap:pretty">Per-school pricing falls in bands as your trust grows. Drag the slider to see where you’d land, then start your free trial in minutes. No call required.</p>
          </div>

          <div style="border:1px solid #E0DACB;border-radius:14px;background:#fff;overflow:hidden;box-shadow:0 18px 44px rgba(27,37,54,.07)">
            <!-- Toggles -->
            <div style="padding:clamp(20px,4vw,28px) clamp(22px,4vw,36px);border-bottom:1px solid #EEE9DD;background:#F8F6F1;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
              <div role="group" aria-label="School or trust" style="display:flex;gap:6px;background:#fff;border:1px solid #E0DACB;border-radius:9px;padding:5px;flex:1 1 300px;max-width:460px">
                <button type="button" :aria-pressed="mode === 'single'" :style="seg(mode === 'single')" @click="mode = 'single'">Single school</button>
                <button type="button" :aria-pressed="mode === 'mat'" :style="seg(mode === 'mat')" @click="mode = 'mat'">Multi-academy trust</button>
              </div>
              <div style="display:flex;align-items:center;gap:10px">
                <div :style="`${mono};font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#8A8474`">Billing</div>
                <div role="group" aria-label="Billing" style="display:flex;gap:5px;background:#fff;border:1px solid #E0DACB;border-radius:9px;padding:4px">
                  <button type="button" :aria-pressed="billing === 'monthly'" :style="segSm(billing === 'monthly')" @click="billing = 'monthly'">Monthly</button>
                  <button type="button" :aria-pressed="billing === 'yearly'" :style="segSm(billing === 'yearly')" @click="billing = 'yearly'">Yearly</button>
                </div>
              </div>
            </div>

            <div style="padding:clamp(24px,4vw,40px)">
              <!-- Trust -->
              <div v-if="mode === 'mat'" data-cols="" style="display:grid;grid-template-columns:1.05fr .95fr;gap:clamp(28px,4vw,48px);align-items:center">
                <div style="display:flex;flex-direction:column;gap:22px">
                  <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px">
                    <label for="calc-schools" style="font-size:16px;font-weight:600">How many schools in your trust?</label>
                    <div style="display:flex;align-items:baseline;gap:6px"><div style="font-size:38px;font-weight:700;letter-spacing:-.02em">{{ schools }}</div><div style="font-size:13px;color:#8A8474">schools</div></div>
                  </div>
                  <div>
                    <input id="calc-schools" v-model.number="schools" type="range" min="2" max="80" step="1" style="width:100%;accent-color:#B4702F;cursor:pointer;height:6px">
                    <div :style="`display:flex;justify-content:space-between;${mono};font-size:11px;color:#9A937F;margin-top:8px`"><div>2</div><div>80</div></div>
                  </div>
                  <div data-bands="" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
                    <div v-for="b in bands" :key="b.range" :style="`border:1px solid ${b.active ? '#B4702F' : '#E7E2D6'};background:${b.active ? '#F9F1E6' : '#fff'};border-radius:8px;padding:11px 8px;display:flex;flex-direction:column;gap:4px;text-align:center`">
                      <div :style="`${mono};font-size:11px;color:${b.active ? '#B4702F' : '#9A937F'}`">{{ b.range }}</div>
                      <div :style="`font-size:11.5px;font-weight:${b.active ? 700 : 600};color:${b.active ? '#1B2536' : '#8A8474'}`">{{ b.off }}</div>
                    </div>
                  </div>
                </div>

                <div aria-live="polite" style="background:#1B2536;color:#F8F6F1;border-radius:12px;padding:clamp(24px,3.5vw,30px);display:flex;flex-direction:column;gap:16px">
                  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                    <div :style="smallLabel">Per school</div>
                    <div v-if="disc > 0" :style="`${mono};font-size:10.5px;color:#fff;background:#B4702F;padding:4px 10px;border-radius:99px`">{{ Math.round(disc * 100) }}% off</div>
                  </div>
                  <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap">
                    <div style="font-size:30px;font-weight:700;letter-spacing:-.01em">{{ gbp(perSchool) }}</div>
                    <div v-if="disc > 0" style="font-size:15px;text-decoration:line-through;color:rgba(248,246,241,.4)">{{ gbp(baseRate) }}</div>
                    <div style="font-size:13px;color:rgba(248,246,241,.55)">/ school · {{ cadence.short }}</div>
                  </div>
                  <div style="height:1px;background:rgba(248,246,241,.14)"></div>
                  <div style="font-size:14px;color:rgba(248,246,241,.6)">{{ gbp(perSchool) }} × {{ schools }} schools</div>
                  <div style="display:flex;flex-direction:column;gap:8px">
                    <div :style="smallLabel">Estimated total</div>
                    <div style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap"><div style="font-size:clamp(34px,5vw,42px);font-weight:700;letter-spacing:-.02em">{{ gbp(total) }}</div><div style="font-size:14px;color:rgba(248,246,241,.55)">/ {{ cadence.short }}</div></div>
                  </div>
                  <div v-if="disc > 0" style="font-size:13.5px;line-height:1.5;color:#E2B188">You save {{ gbp(savings) }} {{ cadence.per }} versus the full per-school price.</div>
                  <div style="display:flex;gap:10px;align-items:baseline;font-size:13px;line-height:1.5;color:rgba(248,246,241,.6);padding-top:14px;border-top:1px solid rgba(248,246,241,.12)">
                    <div aria-hidden="true" style="color:#B4702F;flex-shrink:0">→</div>Trust-wide oversight across every school, included
                  </div>
                  <a :href="trialHref" data-btn="" style="margin-top:4px;font-size:15px;font-weight:600;color:#1B2536;background:#F8F6F1;padding:14px 24px;border-radius:8px;text-align:center">Start your 7-day free trial →</a>
                  <div style="font-size:12px;line-height:1.5;color:rgba(248,246,241,.5);text-align:center">No card needed. Set up online in minutes.</div>
                </div>
              </div>

              <!-- Single school -->
              <div v-else style="display:flex;flex-wrap:wrap;gap:28px;align-items:center;justify-content:space-between">
                <div style="display:flex;flex-direction:column;gap:10px;max-width:420px">
                  <div style="font-size:18px;font-weight:700">One standalone school</div>
                  <p style="margin:0;font-size:14.5px;line-height:1.6;color:#4A5468">A single school that reviews and records its own image decisions, with the full ImageSafe platform. One flat per-school price, billed monthly or yearly, with no volume banding.</p>
                  <div style="display:flex;flex-direction:column;gap:9px;margin-top:6px;font-size:14px;color:#4A5468">
                    <div style="display:flex;gap:10px"><div aria-hidden="true" style="color:#B4702F">—</div>Scanning, permission matching &amp; safeguarding checks</div>
                    <div style="display:flex;gap:10px"><div aria-hidden="true" style="color:#B4702F">—</div>Safer versions in a click</div>
                    <div style="display:flex;gap:10px"><div aria-hidden="true" style="color:#B4702F">—</div>Decision record, audit log &amp; reports included</div>
                  </div>
                </div>
                <div aria-live="polite" style="background:#1B2536;color:#F8F6F1;border-radius:12px;padding:28px 32px;display:flex;flex-direction:column;gap:10px;min-width:230px">
                  <div :style="smallLabel">Estimated total</div>
                  <div style="display:flex;align-items:baseline;gap:8px"><div style="font-size:42px;font-weight:700;letter-spacing:-.02em">{{ gbp(baseRate) }}</div><div style="font-size:14px;color:rgba(248,246,241,.55)">/ {{ cadence.short }}</div></div>
                  <div style="font-size:13px;color:rgba(248,246,241,.6);border-top:1px solid rgba(248,246,241,.12);padding-top:12px;margin-top:4px">For one school · the full platform</div>
                  <a :href="trialHref" data-btn="" style="margin-top:6px;font-size:15px;font-weight:600;color:#1B2536;background:#F8F6F1;padding:14px 24px;border-radius:8px;text-align:center">Start your 7-day free trial →</a>
                  <div style="font-size:12px;line-height:1.5;color:rgba(248,246,241,.5);text-align:center">No card needed. Set up online in minutes.</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Included -->
          <div style="margin-top:clamp(44px,6vw,60px)">
            <div style="display:flex;align-items:baseline;gap:12px;flex-wrap:wrap;margin-bottom:22px">
              <div :style="eyebrow"><div style="width:24px;height:1px;background:#B4702F"></div>Every school gets the full platform</div>
              <div style="font-size:14px;color:#8A8474">Same product for everyone. Larger groups simply pay a lower price per school.</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(248px,1fr));border-top:1px solid #E7E2D6;border-left:1px solid #E7E2D6">
              <div v-for="f in included" :key="f" style="padding:20px 22px;background:#fff;border-right:1px solid #E7E2D6;border-bottom:1px solid #E7E2D6;display:flex;align-items:center;gap:12px"><div :style="tick" aria-hidden="true">✓</div><div style="font-size:14.5px;font-weight:600;line-height:1.35">{{ f }}</div></div>
            </div>
          </div>
          <div style="display:flex;gap:10px;align-items:baseline;margin-top:24px;font-size:13px;line-height:1.6;color:#8A8474;max-width:720px">
            <div aria-hidden="true" :style="`color:#B4702F;flex-shrink:0;${mono}`">*</div>
            <div>Estimates use an indicative £12.50 per-school monthly rate (£150 a year) and the volume bands above. You can subscribe online at this price now, or <Link href="/contact" style="color:#B4702F;text-decoration:underline">talk to us</Link> about any custom requirements.</div>
          </div>
        </div>
      </section>
    </main>

    <SiteFooter />
  </div>
</template>

<style scoped>
[data-btn] { transition: background-color .2s, transform .25s cubic-bezier(.2,.7,.2,1); }
[data-btn]:hover { background: #fff !important; transform: translateY(-1px); }
input[type="range"]:focus-visible, button:focus-visible, a:focus-visible { outline: 2px solid #B4702F; outline-offset: 3px; border-radius: 4px; }
@media (max-width: 980px) {
  [data-cols] { grid-template-columns: minmax(0,1fr) !important; }
}
@media (max-width: 900px) {
  [data-pad] { padding-left: 20px !important; padding-right: 20px !important; }
}
@media (max-width: 480px) {
  [data-bands] { grid-template-columns: repeat(2,1fr) !important; }
}
</style>
