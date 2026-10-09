<script setup>
import { ref } from 'vue'
import SiteNav from '../Components/SiteNav.vue'
import SiteFooter from '../Components/SiteFooter.vue'

const sent = ref(false)
const form = ref({ name: '', email: '', org: '', schools: '2–9', slot: 'Morning' })
const err = ref(false)
function submit() {
  if (!form.value.name.trim() || !/.+@.+\..+/.test(form.value.email.trim())) { err.value = true; return }
  err.value = false
  sent.value = true // wire to your booking endpoint / calendar link
}
</script>

<template>
  <div class="min-h-screen">
    <SiteNav />
    <section class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[0.9fr_1.1fr] gap-16 py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4.5">
          <div class="eyebrow">Book a demo</div>
          <h1 class="text-[clamp(30px,5vw,44px)] font-bold leading-[1.08] tracking-[-0.025em] m-0 text-balance">A 30-minute walkthrough, tailored to your trust.</h1>
          <p class="text-[16px] leading-relaxed text-slate text-pretty m-0">We&rsquo;ll walk through ImageSafe with your own context in mind — schools, teams and safeguarding workflow — and preview what&rsquo;s coming in Compli.</p>
          <ul class="flex flex-col gap-3 text-[14.5px] font-medium mt-1.5">
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Video call · no slides you could have read yourself</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Bring your DSL, communications or governance colleagues</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>No obligation, no chasing afterwards</li>
          </ul>
        </div>
        <div class="bg-white border border-sand-dark rounded-xl p-[clamp(24px,4vw,40px)]">
          <form v-if="!sent" class="flex flex-col gap-4.5" @submit.prevent="submit">
            <div class="grid sm:grid-cols-2 gap-4.5">
              <label class="flex flex-col gap-1.5"><span class="text-[13px] font-semibold">Your name</span><input v-model="form.name" class="field" placeholder="e.g. Lewis Hartley"></label>
              <label class="flex flex-col gap-1.5"><span class="text-[13px] font-semibold">Work email</span><input v-model="form.email" type="email" class="field" placeholder="you@yourtrust.org.uk"></label>
            </div>
            <label class="flex flex-col gap-1.5"><span class="text-[13px] font-semibold">School or trust</span><input v-model="form.org" class="field" placeholder="e.g. Meridian Learning Trust"></label>
            <div class="grid sm:grid-cols-2 gap-4.5">
              <label class="flex flex-col gap-1.5"><span class="text-[13px] font-semibold">Schools</span>
                <select v-model="form.schools" class="field cursor-pointer"><option>1</option><option>2–9</option><option>10–19</option><option>20–29</option><option>30+</option></select>
              </label>
              <label class="flex flex-col gap-1.5"><span class="text-[13px] font-semibold">Preferred time</span>
                <select v-model="form.slot" class="field cursor-pointer"><option>Morning</option><option>Lunchtime</option><option>Afternoon</option></select>
              </label>
            </div>
            <p v-if="err" class="flex gap-2.5 items-baseline px-3.5 py-3 bg-[#F4E2DC] rounded-lg text-[13.5px] text-[#9A3B2A] m-0"><b>!</b> Please add your name and a valid email address.</p>
            <button type="submit" class="btn-primary w-full !py-3.5">Request a demo →</button>
            <p class="text-xs text-stone text-center m-0">We&rsquo;ll reply with a couple of times within one working day.</p>
          </form>
          <div v-else class="flex flex-col items-center text-center gap-4 py-10">
            <span class="w-[60px] h-[60px] rounded-full bg-[#E7F0EA] flex items-center justify-center"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#3F7E63" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
            <h2 class="text-[24px] font-bold tracking-[-0.02em] m-0">Demo requested.</h2>
            <p class="text-[15px] text-slate max-w-[380px] m-0">Thanks {{ form.name.split(' ')[0] }} — we&rsquo;ll email <b class="text-navy">{{ form.email }}</b> with a couple of times to choose from.</p>
            <Link href="/imagesafe" class="btn-outline mt-2">Explore ImageSafe meanwhile</Link>
          </div>
        </div>
      </div>
    </section>
    <SiteFooter />
  </div>
</template>
