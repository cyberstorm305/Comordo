<script setup>
import { identityBase } from '@/identity.js'
import { ref } from 'vue'

const identityDashboardHref = `${identityBase}/dashboard`
const identityLoginHref = `${identityBase}/login`
const logoSrc = new URL('../../../public/comordo.svg', import.meta.url).href
const compliRegistered = ref(true)
const addClicked = ref(false)
const stripeClicked = ref(false)
function openStripe() { stripeClicked.value = true; setTimeout(() => (stripeClicked.value = false), 1600) } // open Stripe customer portal here

const invoices = [
  { date: '01 Aug 2026', desc: 'ImageSafe · 12 schools', amount: '£135.00' },
  { date: '01 Jul 2026', desc: 'ImageSafe · 12 schools', amount: '£135.00' },
  { date: '01 Jun 2026', desc: 'ImageSafe · 11 schools (pro-rata)', amount: '£128.75' },
  { date: '01 May 2026', desc: 'ImageSafe · 11 schools', amount: '£123.75' },
]
const schools = [
  { name: 'Elm Park Primary', type: 'primary' },
  { name: 'Ferndale High', type: 'secondary' },
  { name: 'Birchwood SEND School', type: 'special' },
  { name: 'St Aidan\u2019s CofE Primary', type: 'primary' },
]
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- top bar -->
    <header class="bg-white border-b border-sand">
      <div class="max-w-[1100px] mx-auto flex items-center justify-between gap-4 px-5 sm:px-8 py-3.5">
        <div class="flex items-center gap-3.5">
          <Link href="/"><img :src="logoSrc" alt="Comordo" class="h-[26px] w-auto block"></Link>
          <i class="w-px h-5 bg-sand"></i>
          <span class="font-mono text-[11px] tracking-[0.08em] uppercase text-stone">Account</span>
        </div>
        <div class="flex items-center gap-3.5">
          <span class="hidden sm:block text-[13px] text-slate">admin@hartswood.org.uk</span>
          <span class="w-8 h-8 rounded-full bg-navy text-cream flex items-center justify-center text-xs font-bold">JH</span>
          <a :href="identityLoginHref" class="text-[13px] font-semibold text-slate hover:text-navy">Sign in</a>
        </div>
      </div>
    </header>

    <div class="max-w-[1100px] mx-auto w-full px-5 sm:px-8 py-[clamp(28px,5vw,48px)] pb-16 flex flex-col gap-9">
      <!-- header -->
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-col gap-2">
          <div class="eyebrow">One Comordo account</div>
          <h1 class="text-[clamp(26px,4vw,34px)] font-bold tracking-[-0.02em] m-0">Hartswood Learning Trust</h1>
          <span class="font-mono text-xs text-mist">12 school licences · account since May 2026 · owner J. Hargreaves</span>
        </div>
        <Link href="/contact" class="btn-outline !text-[13.5px] !py-2.5 bg-white">Contact support</Link>
      </div>

      <!-- licences -->
      <section class="flex flex-col gap-3.5">
        <h2 class="text-[17px] font-bold m-0">Products &amp; licences</h2>
        <div class="grid lg:grid-cols-2 gap-4.5">
          <!-- ImageSafe -->
          <div class="bg-white border border-sand-dark rounded-xl p-6.5 flex flex-col gap-4 shadow-[0_12px_32px_rgba(27,37,54,0.05)]">
            <div class="flex items-center justify-between gap-3">
              <span class="flex items-center gap-2.5">
                <span class="w-[34px] h-[34px] rounded-lg bg-navy flex items-center justify-center"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#B4702F" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="15" rx="2.5"/><circle cx="9" cy="10.2" r="1.8"/><path d="M3.5 17.5l5-4.5 3.5 3 4-4 4.5 4.5"/></svg></span>
                <b class="text-[17px]">ImageSafe</b>
              </span>
              <span class="pill-green">Active</span>
            </div>
            <dl class="m-0">
              <div class="flex justify-between gap-3 py-2.5 border-b border-hair text-[13.5px]"><dt class="text-stone">School licences</dt><dd class="m-0 font-bold">12</dd></div>
              <div class="flex justify-between gap-3 py-2.5 border-b border-hair text-[13.5px]"><dt class="text-stone">Per school</dt><dd class="m-0 font-bold">£11.25 / month <span class="font-medium text-copper">(10% volume band)</span></dd></div>
              <div class="flex justify-between gap-3 py-2.5 text-[13.5px]"><dt class="text-stone">Renews</dt><dd class="m-0 font-bold">1 September 2026</dd></div>
            </dl>
            <div class="flex flex-wrap gap-2.5 mt-auto">
              <a :href="identityDashboardHref" class="flex-1 min-w-[130px] text-[13.5px] font-semibold text-cream bg-navy px-4.5 py-3 rounded-md text-center hover:bg-navy-light">Open Identity</a>
              <button class="flex-1 min-w-[130px] text-[13.5px] font-semibold border border-edge bg-white px-4.5 py-2.5 rounded-md hover:border-navy" @click="addClicked = true">{{ addClicked ? 'Added to next invoice ✓' : 'Add schools' }}</button>
            </div>
          </div>
          <!-- Compli -->
          <div class="bg-[#FBFAF6] border-[1.5px] border-dashed border-[#DAD3C4] rounded-xl p-6.5 flex flex-col gap-4">
            <div class="flex items-center justify-between gap-3">
              <span class="flex items-center gap-2.5">
                <span class="w-[34px] h-[34px] rounded-lg bg-navy flex items-center justify-center"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#B4702F" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.4-3 8.2-7 9.5C8 19.2 5 15.4 5 11V6l7-3z"/><path d="M9 11.8l2.1 2.2L15.4 9.4"/></svg></span>
                <b class="text-[17px]">Compli</b>
              </span>
              <span class="pill-amber">Coming soon</span>
            </div>
            <p class="text-sm leading-relaxed text-slate text-pretty m-0">Website compliance for the same schools, on this account. When Compli launches you&rsquo;ll be able to add licences here — no new account, no new billing setup.</p>
            <div v-if="compliRegistered" class="flex items-center gap-2.5 bg-white border border-sand-dark rounded-lg px-3.5 py-3 text-[13.5px]">
              <span class="w-5 h-5 rounded-full bg-[#E9F4EE] text-rag-green flex items-center justify-center text-[11px] font-bold shrink-0">✓</span>
              <span><b>Interest registered.</b> We&rsquo;ll email you when Compli opens for your schools.</span>
            </div>
            <div class="flex flex-wrap gap-2.5 mt-auto">
              <button v-if="!compliRegistered" class="flex-1 min-w-[150px] text-[13.5px] font-semibold text-cream bg-navy px-4.5 py-3 rounded-md hover:bg-navy-light" @click="compliRegistered = true">Register interest</button>
              <Link href="/compli" class="flex-1 min-w-[150px] text-[13.5px] font-semibold border border-edge bg-white px-4.5 py-2.5 rounded-md text-center hover:border-navy">See what&rsquo;s coming</Link>
            </div>
          </div>
        </div>
      </section>

      <!-- billing -->
      <section class="flex flex-col gap-3.5">
        <h2 class="text-[17px] font-bold m-0">Billing</h2>
        <div class="grid lg:grid-cols-[1fr_1.35fr] gap-4.5 items-start">
          <div class="bg-white border border-sand-dark rounded-xl p-6 flex flex-col gap-3.5">
            <span class="font-mono text-[11px] tracking-[0.08em] uppercase text-stone">Payment method</span>
            <div class="flex items-center gap-3">
              <span class="w-10 h-[26px] rounded bg-gradient-to-br from-[#1A1F71] to-[#2A3FA0] text-white text-[10px] font-extrabold flex items-center justify-center">VISA</span>
              <span class="flex flex-col gap-0.5"><b class="text-sm">Visa ending 4242</b><span class="font-mono text-[11px] text-mist">expires 08 / 28</span></span>
            </div>
            <div class="flex justify-between gap-3 pt-3 border-t border-hair text-[13.5px]"><span class="text-stone">Next invoice</span><b>£135 · 1 October 2026</b></div>
            <button class="text-[13.5px] font-semibold border border-edge bg-white px-4.5 py-3 rounded-md hover:border-navy" @click="openStripe">{{ stripeClicked ? 'Opening Stripe portal…' : 'Manage billing in Stripe' }}</button>
            <p class="flex items-center justify-center gap-1.5 text-[11px] text-stone m-0">Cards and invoices managed securely by <b class="text-[#635BFF]">Stripe</b></p>
          </div>
          <div class="bg-white border border-sand-dark rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-sand-light">
              <span class="font-mono text-[11px] tracking-[0.08em] uppercase text-stone">Invoices</span>
              <span class="font-mono text-[10.5px] text-mist">ImageSafe · monthly</span>
            </div>
            <div v-for="inv in invoices" :key="inv.date" class="grid grid-cols-[1fr_1.4fr_auto_auto] max-sm:grid-cols-[1.2fr_auto] gap-3.5 items-center px-5 py-3 border-b border-hair last:border-b-0">
              <span class="font-mono text-xs text-slate">{{ inv.date }}</span>
              <span class="max-sm:hidden text-[13px] text-slate">{{ inv.desc }}</span>
              <span class="max-sm:hidden pill-green !text-[10px] normal-case">Paid</span>
              <span class="flex items-center gap-3"><b class="text-[13.5px]">{{ inv.amount }}</b><a href="#" class="font-mono text-[10.5px] text-copper">PDF</a></span>
            </div>
          </div>
        </div>
      </section>

      <!-- school licences -->
      <section class="flex flex-col gap-3.5">
        <div class="flex flex-wrap items-baseline justify-between gap-2.5">
          <h2 class="text-[17px] font-bold m-0">School licences</h2>
          <span class="text-[13px] text-stone">Licences apply per school, across every Comordo product on this account.</span>
        </div>
        <div class="bg-white border border-sand-dark rounded-xl overflow-hidden">
          <div class="grid grid-cols-[1.4fr_1fr_1fr_auto] max-sm:grid-cols-[1.2fr_auto] gap-3.5 px-5 py-3 bg-[#FBFAF6] border-b border-hair font-mono text-[10px] uppercase tracking-[0.06em] text-stone">
            <span>School</span><span class="max-sm:hidden">Type</span><span class="max-sm:hidden">ImageSafe</span><span>Compli</span>
          </div>
          <div v-for="s in schools" :key="s.name" class="grid grid-cols-[1.4fr_1fr_1fr_auto] max-sm:grid-cols-[1.2fr_auto] gap-3.5 items-center px-5 py-3 border-b border-hair">
            <b class="text-[13.5px]">{{ s.name }}</b>
            <span class="max-sm:hidden font-mono text-[11px] text-mist">{{ s.type }}</span>
            <span class="max-sm:hidden"><span class="pill-green !text-[10px] normal-case">Licensed</span></span>
            <span><span class="pill-muted !text-[10px] normal-case">At launch</span></span>
          </div>
          <div class="px-5 py-3 text-[13px] text-stone">+ 8 more schools · <a href="#" class="text-copper font-semibold">view all</a></div>
        </div>
      </section>

      <p class="text-xs text-mist text-center m-0">Prototype — licences, invoices and Stripe actions are illustrative.</p>
    </div>
  </div>
</template>
