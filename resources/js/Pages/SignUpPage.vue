<script setup>
import { identityBase } from '@/identity.js'
import { ref, computed } from 'vue'

const identityRegisterHref = `${identityBase}/register`
const logoSrc = new URL('../../../public/comordo-light.svg', import.meta.url).href
const step = ref('details') // details | connecting | payment | processing | done
const trust = ref(''); const name = ref(''); const email = ref(''); const password = ref('')
const schools = ref(9); const billing = ref('monthly'); const compliInterest = ref(false)
const card = ref({ number: '', exp: '', cvc: '', name: '' })
const errs = ref({})

const base = 150
const disc = computed(() => {
  const s = schools.value
  const vol = s >= 30 ? 0.16 : s >= 20 ? 0.12 : s >= 10 ? 0.06 : 0
  return vol > 0 ? vol + 0.04 : 0
})
const div = computed(() => (billing.value === 'monthly' ? 12 : 1))
const gbp = (n) => '£' + Number(n).toLocaleString('en-GB', { minimumFractionDigits: n % 1 ? 2 : 0, maximumFractionDigits: 2 })
const perSchool = computed(() => (base * (1 - disc.value)) / div.value)
const subtotal = computed(() => (base * schools.value) / div.value)
const total = computed(() => perSchool.value * schools.value)
const savings = computed(() => subtotal.value - total.value)
const cadence = computed(() => (billing.value === 'monthly' ? { short: 'month', abbr: 'mo', billed: 'Billed monthly' } : { short: 'year', abbr: 'yr', billed: 'Billed annually' }))
const schoolsText = computed(() => (schools.value === 1 ? '1 school' : schools.value + ' schools'))
const trialEnd = new Date(Date.now() + 14 * 86400000).toLocaleDateString('en-GB', { day: 'numeric', month: 'long' })
const firstName = computed(() => name.value.trim().split(/\s+/)[0] || 'there')

const stepIdx = computed(() => ({ details: 0, connecting: 1, payment: 1, processing: 1, done: 2 })[step.value])
const stepper = ['Account', 'Start trial', 'Done']

function toPayment() {
  errs.value = {
    trust: !trust.value.trim(),
    name: !name.value.trim(),
    email: !/.+@.+\..+/.test(email.value.trim()),
    password: password.value.length < 8,
  }
  if (Object.values(errs.value).some(Boolean)) return
  step.value = 'connecting'
  setTimeout(() => (step.value = 'payment'), 1900)
  window.scrollTo(0, 0)
}
function pay() {
  // Wire to Stripe Checkout / Elements here — card captured, £0 today, first charge after the 14-day trial.
  step.value = 'processing'
  setTimeout(() => (step.value = 'done'), 2100)
}
const fmtCard = (v) => v.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim()
const fmtExp = (v) => { const d = v.replace(/\D/g, '').slice(0, 4); return d.length <= 2 ? d : d.slice(0, 2) + ' / ' + d.slice(2) }
const clamp = (n) => Math.max(1, Math.min(200, Math.round(n) || 1))
</script>

<template>
  <div class="min-h-screen grid lg:grid-cols-[0.92fr_1.08fr] font-display text-navy">
    <!-- BRAND / ORDER SUMMARY -->
    <aside class="hidden lg:flex bg-navy text-cream p-[clamp(36px,4vw,56px)] flex-col justify-between gap-8 relative overflow-hidden">
      <i class="absolute -right-[120px] -top-[120px] w-[340px] h-[340px] rounded-full border border-cream/[0.07]"></i>
      <Link href="/" class="relative"><img :src="logoSrc" alt="Comordo" class="h-[30px] w-auto block"></Link>
      <div class="relative flex flex-col gap-5.5">
        <div class="eyebrow">Create your account</div>
        <h2 class="text-[clamp(25px,3vw,34px)] font-bold leading-[1.12] tracking-[-0.025em] text-balance m-0">One Comordo account for ImageSafe and Compli.</h2>
        <div class="bg-cream/[0.06] border border-cream/[0.14] rounded-[14px] p-5.5 flex flex-col gap-3.5 mt-1">
          <div class="flex items-center justify-between gap-3">
            <b class="text-[15px]">ImageSafe — {{ schoolsText }}</b>
            <span v-if="disc > 0" class="font-mono text-[10.5px] text-white bg-copper px-2.5 py-1 rounded-full whitespace-nowrap">{{ Math.round(disc * 100) }}% off</span>
          </div>
          <hr class="border-0 h-px bg-cream/[0.14] m-0">
          <div class="flex items-center justify-between gap-3 text-sm text-cream/[0.78]">
            <span>{{ gbp(perSchool) }} × {{ schoolsText }}</span><span class="font-mono">{{ gbp(subtotal) }}</span>
          </div>
          <div v-if="disc > 0" class="flex items-center justify-between gap-3 text-sm text-copper-light">
            <span>Volume saving</span><span class="font-mono">−{{ gbp(savings) }}</span>
          </div>
          <div v-if="compliInterest" class="flex items-center justify-between gap-3 text-sm text-cream/[0.78]">
            <span>Compli · launch interest</span><span class="font-mono">£0</span>
          </div>
          <hr class="border-0 h-px bg-cream/[0.14] m-0">
          <div class="flex items-end justify-between gap-3">
            <span class="flex flex-col gap-1">
              <span class="font-mono text-[11px] tracking-[0.08em] uppercase text-copper">Due today</span>
              <span class="text-xs text-cream/50">14-day free trial · then {{ gbp(total) }} / {{ cadence.abbr }}</span>
            </span>
            <b class="text-[clamp(26px,4vw,32px)] tracking-[-0.02em]">£0</b>
          </div>
        </div>
        <ul class="flex flex-col gap-3 text-sm text-cream/85">
          <li class="flex items-center gap-3"><i class="text-copper-pale not-italic">✓</i> Full ImageSafe workspace for every school</li>
          <li class="flex items-center gap-3"><i class="text-copper-pale not-italic">✓</i> No charge for 14 days · cancel anytime</li>
          <li class="flex items-center gap-3"><i class="text-copper-pale not-italic">✓</i> Add schools any time, pro-rata</li>
          <li class="flex items-center gap-3"><i class="text-copper-pale not-italic">✓</i> Compli joins the same account at launch</li>
        </ul>
      </div>
      <div class="relative font-mono text-[11.5px] text-cream/45">Secured by Stripe · UK statutory requirements</div>
    </aside>

    <!-- FORM PANEL -->
    <main class="flex flex-col p-[clamp(24px,3.5vw,52px)]">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4.5">
          <div v-for="(label, i) in stepper" :key="label" class="flex items-center gap-2">
            <span class="w-[26px] h-[26px] rounded-full flex items-center justify-center text-[12.5px] font-bold border"
              :class="i <= stepIdx ? 'bg-navy text-cream border-navy' : 'bg-white text-mist border-[#DAD3C4]'">{{ i < stepIdx ? '✓' : i + 1 }}</span>
            <span class="text-[13px] font-semibold" :class="i <= stepIdx ? 'text-navy' : 'text-mist'">{{ label }}</span>
          </div>
        </div>
        <a :href="identityRegisterHref" class="text-[13px] text-stone hover:text-navy whitespace-nowrap">Sign in</a>
      </div>

      <div class="flex-1 flex flex-col justify-center max-w-[480px] w-full mx-auto py-7">
        <!-- STEP 1 -->
        <form v-if="step === 'details'" class="flex flex-col gap-5" @submit.prevent="toPayment">
          <div class="flex flex-col gap-1.5">
            <h1 class="text-[clamp(24px,3vw,30px)] font-bold tracking-[-0.02em] m-0">Tell us about your trust</h1>
            <p class="text-[14.5px] text-slate m-0">One account for both products, starting with a 14-day free trial.</p>
          </div>
          <label class="flex flex-col gap-1.5">
            <span class="text-[13px] font-semibold">Trust or school name</span>
            <input v-model="trust" class="field" :class="errs.trust && 'border-[#B23A2E]'" placeholder="e.g. Meridian Learning Trust">
            <span v-if="errs.trust" class="text-[12.5px] text-[#9A3B2A]">Please add your trust or school name.</span>
          </label>
          <label class="flex flex-col gap-1.5">
            <span class="text-[13px] font-semibold">Your name</span>
            <input v-model="name" class="field" :class="errs.name && 'border-[#B23A2E]'" placeholder="e.g. Lewis Hartley">
            <span v-if="errs.name" class="text-[12.5px] text-[#9A3B2A]">Please add your name.</span>
          </label>
          <label class="flex flex-col gap-1.5">
            <span class="text-[13px] font-semibold">Work email</span>
            <input v-model="email" type="email" class="field" :class="errs.email && 'border-[#B23A2E]'" placeholder="you@yourtrust.org.uk">
            <span v-if="errs.email" class="text-[12.5px] text-[#9A3B2A]">Please enter a valid email address.</span>
          </label>
          <label class="flex flex-col gap-1.5">
            <span class="text-[13px] font-semibold">Create a password</span>
            <input v-model="password" type="password" class="field" :class="errs.password && 'border-[#B23A2E]'" placeholder="At least 8 characters">
            <span v-if="errs.password" class="text-[12.5px] text-[#9A3B2A]">Please choose a password of at least 8 characters.</span>
            <span class="text-xs text-stone">This signs you in to every Comordo product — one account for ImageSafe and Compli.</span>
          </label>

          <!-- products -->
          <div class="flex flex-col gap-2">
            <span class="text-[13px] font-semibold">Your products</span>
            <div class="flex items-center gap-3 bg-white border-[1.5px] border-navy rounded-xl px-4 py-3.5">
              <span class="w-[22px] h-[22px] rounded-md bg-navy text-cream flex items-center justify-center text-xs font-bold shrink-0">✓</span>
              <span class="flex flex-col gap-0.5 flex-1 min-w-0">
                <b class="text-[14.5px]">ImageSafe <span class="text-xs font-medium text-stone">· image audit workspace</span></b>
                <span class="text-xs text-stone">Available now — included in today&rsquo;s subscription</span>
              </span>
              <span class="font-mono text-[11.5px] text-slate whitespace-nowrap">{{ gbp(perSchool) }} / school</span>
            </div>
            <button type="button" class="flex items-center gap-3 rounded-xl px-4 py-3.5 border-[1.5px] border-dashed text-left cursor-pointer"
              :class="compliInterest ? 'bg-[#FBF6EC] border-copper' : 'bg-white border-[#DAD3C4]'" @click="compliInterest = !compliInterest">
              <span class="w-[22px] h-[22px] rounded-md border-[1.5px] flex items-center justify-center text-xs font-bold shrink-0"
                :class="compliInterest ? 'bg-copper border-copper text-cream' : 'border-edge text-transparent'">✓</span>
              <span class="flex flex-col gap-0.5 flex-1 min-w-0">
                <b class="text-[14.5px]">Compli <span class="text-xs font-medium text-stone">· website compliance</span></b>
                <span class="text-xs text-stone">Coming soon — register interest and it joins your account at launch</span>
              </span>
              <span class="pill-amber !text-[10.5px]">Coming soon</span>
            </button>
          </div>

          <!-- schools + billing -->
          <div class="flex flex-col gap-3 bg-white border border-[#E2DCCE] rounded-xl p-4.5">
            <div class="flex items-baseline justify-between gap-3">
              <span class="text-[13px] font-semibold">How many schools in your trust?</span>
              <span class="flex items-center gap-2">
                <button type="button" class="w-[30px] h-[30px] rounded-lg border border-[#DAD3C4] bg-cream text-lg leading-none hover:border-navy" @click="schools = clamp(schools - 1)">−</button>
                <input :value="schools" class="w-[54px] text-center text-lg font-bold bg-white border border-[#DAD3C4] rounded-lg py-1.5 outline-none" inputmode="numeric" @input="schools = clamp(Number($event.target.value.replace(/\D/g, '')))">
                <button type="button" class="w-[30px] h-[30px] rounded-lg border border-[#DAD3C4] bg-cream text-lg leading-none hover:border-navy" @click="schools = clamp(schools + 1)">+</button>
              </span>
            </div>
            <input type="range" min="1" max="80" step="1" :value="schools" class="w-full accent-copper cursor-pointer" @input="schools = clamp(Number($event.target.value))">
            <div class="flex justify-between font-mono text-[11px] text-mist"><span>1 school</span><span>80+</span></div>
          </div>
          <div class="flex items-center gap-2.5">
            <span class="font-mono text-[11px] tracking-[0.06em] uppercase text-stone">Billing</span>
            <span class="flex gap-1 bg-white border border-sand-dark rounded-lg p-1">
              <button v-for="b in ['monthly', 'yearly']" :key="b" type="button" class="px-3.5 py-1.5 rounded-md text-[13px] font-semibold capitalize"
                :class="billing === b ? 'bg-navy text-cream' : 'text-stone'" @click="billing = b">{{ b }}</button>
            </span>
            <span class="text-[12.5px] text-stone">{{ gbp(perSchool) }} / school / {{ cadence.short }}</span>
          </div>

          <button type="submit" class="btn-primary w-full !py-3.5 group">Continue to Identity registration <span class="transition-transform group-hover:translate-x-0.5">→</span></button>
          <p class="text-xs text-stone text-center -mt-2 m-0">Due today: <b class="text-navy">£0</b> · then {{ gbp(total) }} / {{ cadence.abbr }} from {{ trialEnd }} · processed by Stripe</p>
        </form>

        <!-- CONNECTING / PROCESSING -->
        <div v-else-if="step === 'connecting' || step === 'processing'" class="flex flex-col items-center gap-5.5 text-center py-10 min-h-[280px] justify-center">
          <span class="relative w-[60px] h-[60px]">
            <i class="absolute inset-0 rounded-full border-[3px] border-sand"></i>
            <i class="absolute inset-0 rounded-full border-[3px] border-r-transparent border-b-transparent animate-spin" :class="step === 'connecting' ? 'border-copper' : 'border-[#635BFF]'"></i>
          </span>
          <div class="flex flex-col gap-2">
            <b class="text-[19px]">{{ step === 'connecting' ? 'Connecting to secure checkout…' : 'Setting up your trial…' }}</b>
            <span class="text-sm text-slate font-mono">{{ step === 'connecting' ? '£0 today · processed by Stripe' : 'Securing your card with Stripe' }}</span>
          </div>
        </div>

        <!-- PAYMENT -->
        <form v-else-if="step === 'payment'" class="flex flex-col gap-4.5" @submit.prevent="pay">
          <div class="flex items-center justify-between gap-3">
            <div class="flex flex-col gap-1">
              <h1 class="text-[clamp(22px,2.6vw,27px)] font-bold tracking-[-0.02em] m-0">Secure payment</h1>
              <span class="text-[13.5px] text-slate">{{ trust }} · {{ schoolsText }}</span>
            </div>
            <span class="font-mono text-[10.5px] tracking-[0.06em] uppercase text-[#946100] bg-[#FBEFD6] border border-[#ECD6A8] px-2.5 py-1 rounded-full whitespace-nowrap">Test mode</span>
          </div>
          <div class="bg-[#F7F4EC] border border-[#E2DCCE] rounded-xl px-4.5 py-4 flex items-center justify-between gap-3">
            <span class="flex flex-col gap-0.5"><b class="text-sm">ImageSafe · 14-day free trial</b><span class="text-xs text-stone">{{ gbp(perSchool) }} × {{ schoolsText }} · {{ cadence.billed }} from {{ trialEnd }}</span></span>
            <b class="text-xl tracking-[-0.02em]">£0.00</b>
          </div>
          <label class="flex flex-col gap-1.5">
            <span class="text-[12.5px] font-semibold text-slate">Email</span>
            <input v-model="email" class="field !text-[14.5px]">
          </label>
          <label class="flex flex-col gap-1.5">
            <span class="text-[12.5px] font-semibold text-slate">Card information</span>
            <span class="border border-[#DAD3C4] rounded-lg bg-white overflow-hidden block">
              <span class="flex items-center gap-2 px-3.5 py-3 border-b border-[#EEEADF]">
                <input :value="card.number" placeholder="1234 1234 1234 1234" inputmode="numeric" class="flex-1 border-0 outline-none text-[14.5px] bg-transparent" @input="card.number = fmtCard($event.target.value)">
                <span class="w-[30px] h-5 rounded bg-gradient-to-br from-[#1A1F71] to-[#2A3FA0] text-white text-[8px] font-extrabold flex items-center justify-center">VISA</span>
              </span>
              <span class="flex">
                <input :value="card.exp" placeholder="MM / YY" class="flex-1 border-0 border-r border-[#EEEADF] outline-none text-[14.5px] bg-transparent px-3.5 py-3" @input="card.exp = fmtExp($event.target.value)">
                <input v-model="card.cvc" placeholder="CVC" inputmode="numeric" maxlength="4" class="flex-1 border-0 outline-none text-[14.5px] bg-transparent px-3.5 py-3">
              </span>
            </span>
          </label>
          <button type="submit" class="btn-primary w-full !py-3.5">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="4" y="10.5" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7.5 10.5V7.5a4.5 4.5 0 0 1 9 0V10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Start free trial — £0 today
          </button>
          <p class="flex items-center justify-center gap-1.5 text-[11.5px] text-stone m-0">Powered by <b class="text-[#635BFF]">Stripe</b> · this is a prototype — no card is charged</p>
          <button type="button" class="text-center text-[12.5px] text-stone hover:text-navy" @click="step = 'details'">← Back to details</button>
        </form>

        <!-- DONE -->
        <div v-else class="flex flex-col gap-5 text-center items-center">
          <span class="w-[60px] h-[60px] rounded-full bg-[#E7F0EA] flex items-center justify-center"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#3F7E63" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
          <div class="flex flex-col gap-2">
            <h1 class="text-[clamp(23px,3vw,29px)] font-bold tracking-[-0.02em] m-0">You&rsquo;re all set, {{ firstName }}.</h1>
            <p class="text-[15px] text-slate leading-relaxed max-w-[380px] text-pretty m-0">Your 14-day ImageSafe trial for <b class="text-navy">{{ trust }}</b> is live. We&rsquo;ve emailed your confirmation to {{ email }} — nothing is charged until {{ trialEnd }}. <template v-if="compliInterest">Compli will be added to the same account at launch.</template></p>
          </div>
          <div class="bg-white border border-[#E2DCCE] rounded-xl px-5 py-4 flex items-center justify-between gap-3 w-full">
            <span class="flex flex-col gap-0.5 text-left"><span class="text-[13px] text-stone">Due today</span><span class="text-xs text-stone">first payment {{ gbp(total) }} on {{ trialEnd }}</span></span>
            <b class="text-[22px] tracking-[-0.02em]">£0.00</b>
          </div>
          <a :href="identityRegisterHref" class="btn-primary w-full !py-3.5">Open Identity registration →</a>
          <p class="text-[12.5px] text-stone m-0">Next: finish registration in Identity and come back here to use the products.</p>
        </div>
      </div>

      <p class="text-xs text-mist text-center m-0">Questions before you subscribe? <Link href="/contact" class="text-copper">Contact us</Link>.</p>
    </main>
  </div>
</template>
