<script setup>
import { ref } from 'vue'
import SiteNav from '../Components/SiteNav.vue'
import SiteFooter from '../Components/SiteFooter.vue'
import { imagesafeSiteUrl } from '@/site.js'

const demoHref = `${imagesafeSiteUrl}/demo`

const openFaq = ref(0)
const faqs = [
  { q: 'What is Comordo?', a: 'Comordo is an education technology company building digital assurance tools for schools and multi-academy trusts. Our first product, ImageSafe, helps teams understand and review the imagery published across their websites, and is available now. More products will follow, built on the same model \u2014 discover, analyse, review, evidence, improve.' },
  { q: 'Do the products make decisions automatically?', a: 'No. Automation finds information and surfaces what may need attention \u2014 possible evidence, potential gaps, images worth a closer look. Staff review each item, decide the outcome and record it. Nothing changes status without a person deciding it should.' },
  { q: 'How does ImageSafe handle sensitive data?', a: 'Carefully and transparently. Access is controlled, reference records are managed by your organisation, and every review action is logged in the audit history. Face analysis produces observations for staff to review \u2014 it never makes safeguarding decisions. We\u2019re happy to walk through data handling in detail on a call.' },
  { q: 'Does it work for a single school?', a: 'Yes. ImageSafe works for a standalone school as well as a trust \u2014 the same workspace, without the organisation-wide layer. Trusts additionally get oversight across every school in the trust.' },
]

const about = [
  { title: 'Built for education', body: 'An education technology company making digital assurance tools for UK schools and multi-academy trusts.' },
  { title: 'People make the decisions', body: 'Automation finds information and surfaces what may need attention. Staff review it, decide the outcome and record it.' },
  { title: 'One Comordo account', body: 'Sign in once with your Comordo account to reach every Comordo product your organisation uses.' },
]
</script>

<template>
  <div class="min-h-screen">
    <SiteNav />

    <!-- HERO -->
    <section class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[1fr_1.05fr] gap-14 items-center py-[clamp(56px,9vw,88px)] pb-[clamp(64px,10vw,104px)]">
        <div class="flex flex-col gap-6">
          <div class="eyebrow">Digital assurance for education</div>
          <h1 class="text-[clamp(34px,6vw,56px)] font-bold leading-[1.06] tracking-[-0.025em] text-balance">Safeguarding tools for schools and trusts.</h1>
          <p class="text-[17.5px] leading-relaxed text-slate max-w-[500px] text-pretty">Comordo is an education technology company building practical digital assurance tools for schools and multi-academy trusts. Our first product, ImageSafe, is available now: it helps teams find and review the images published across their websites, pairing intelligent scanning with structured human review and evidence.</p>
          <div class="flex flex-wrap gap-3.5 mt-1">
            <Link href="/imagesafe" class="btn-primary group">Explore ImageSafe <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></Link>
            <Link href="/contact" class="btn-outline">Contact us</Link>
          </div>
          <div class="flex flex-wrap gap-x-5 gap-y-2 text-[13px] text-stone items-baseline">
            <span class="flex gap-2 items-baseline"><i class="w-1.5 h-1.5 rounded-[2px] bg-navy relative -top-px"></i>ImageSafe by Comordo</span>
          </div>
        </div>

        <!-- ImageSafe mock -->
        <div class="relative min-w-0">
          <div class="mock-card !rounded-xl shadow-[0_30px_70px_rgba(27,37,54,0.14)]">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-sand-light">
              <div class="font-mono text-[10.5px] text-mist">Hartswood Learning Trust · 12 schools</div>
              <span class="text-[11.5px] font-bold px-3 py-1 rounded-md bg-navy text-cream">ImageSafe</span>
            </div>

            <div class="p-4 flex flex-col gap-3.5">
              <div class="flex items-center justify-between font-mono text-[10px] text-stone uppercase tracking-[0.06em]">
                <span>Image library · Elm Park Primary</span>
                <span class="normal-case tracking-normal text-mist">1,284 images · last crawl 2h ago</span>
              </div>
              <div class="grid grid-cols-4 gap-2">
                <div v-for="(t, i) in 8" :key="i" class="aspect-[4/3] rounded-lg relative overflow-hidden"
                  :class="[['bg-sand','bg-[#DCE0DE]','bg-[#E2DACB]','bg-[#D8DCE2]','bg-[#DFD8CE]','bg-[#E0E4DC]','bg-[#E5DFD3]','bg-[#E7E0D2]'][i], i === 1 ? 'shadow-[inset_0_0_0_2px_#C0760B]' : '', i === 4 ? 'shadow-[inset_0_0_0_2px_#C42E22]' : '']">
                  <i v-if="i % 3 !== 2" class="absolute left-1/2 -bottom-[16%] -translate-x-1/2 w-[55%] aspect-square rounded-t-full bg-black/10"></i>
                  <i v-if="i % 3 !== 2" class="absolute left-1/2 top-[18%] -translate-x-1/2 w-[25%] aspect-square rounded-full bg-black/10"></i>
                  <span v-if="i === 1" class="absolute top-1.5 right-1.5 pill-amber !text-[8px] !px-1.5 !py-0.5 normal-case">review</span>
                  <span v-if="i === 4" class="absolute top-1.5 right-1.5 pill-red !text-[8px] !px-1.5 !py-0.5 normal-case">flag</span>
                </div>
              </div>
              <div class="flex items-center justify-between gap-3 border border-sand-light rounded-lg px-3.5 py-2.5">
                <div class="flex gap-4 font-mono text-[10px] text-stone">
                  <span><i class="text-rag-green">●</i> 1,192 reviewed</span>
                  <span><i class="text-rag-amber">●</i> 84 need review</span>
                  <span><i class="text-rag-red">●</i> 8 flagged</span>
                </div>
                <span class="text-[11px] font-bold text-cream bg-navy px-3 py-1.5 rounded-[5px]">Open review queue</span>
              </div>
            </div>
          </div>
          <div class="absolute left-6 right-6 -bottom-2.5 h-2.5 bg-white border border-sand-dark border-t-0 rounded-b-[10px] opacity-65"></div>
        </div>
      </div>
    </section>

    <!-- PRODUCTS -->
    <section id="products" class="border-b border-sand bg-white">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[720px] flex flex-col gap-4 mb-12">
          <div class="eyebrow">Products</div>
          <h2 class="section-title">Our products</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">Each Comordo product inspects part of your digital estate, surfaces evidence and areas needing attention, and puts professional judgement inside a structured review workflow — with a record of every decision.</p>
        </div>
        <div class="grid lg:grid-cols-2 gap-6">
          <!-- ImageSafe card -->
          <Link href="/imagesafe" class="group border border-sand rounded-xl bg-cream p-[clamp(26px,4vw,40px)] flex flex-col gap-4.5 transition hover:border-navy hover:shadow-[0_16px_40px_rgba(27,37,54,0.10)] hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
              <span class="font-mono text-[11.5px] uppercase tracking-[0.08em] text-stone">Product 01</span>
              <span class="w-[34px] h-[34px] rounded-lg bg-navy flex items-center justify-center">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#B4702F" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="15" rx="2.5"/><circle cx="9" cy="10.2" r="1.8"/><path d="M3.5 17.5l5-4.5 3.5 3 4-4 4.5 4.5"/></svg>
              </span>
            </div>
            <div class="flex flex-col gap-2">
              <div class="text-2xl font-bold tracking-[-0.02em]">ImageSafe <span class="text-[15px] font-medium text-stone">by Comordo</span></div>
              <div class="text-[17px] font-bold text-copper">Safer image publishing starts with visibility.</div>
            </div>
            <p class="text-[14.5px] leading-relaxed text-slate text-pretty">Discover the images published across your school websites, understand who appears in them and give safeguarding and communications teams a clear workflow for reviewing anything that needs attention.</p>
            <ul class="flex flex-col gap-2 text-sm leading-normal text-slate">
              <li class="flex gap-2.5"><i class="text-copper not-italic">✓</i>Discover every image across your school websites</li>
              <li class="flex gap-2.5"><i class="text-copper not-italic">✓</i>See who appears — identified and unidentified people</li>
              <li class="flex gap-2.5"><i class="text-copper not-italic">✓</i>Safeguarding observations for staff to review</li>
              <li class="flex gap-2.5"><i class="text-copper not-italic">✓</i>Notes, statuses and audit trails on every image</li>
            </ul>
            <span class="mt-auto text-[14.5px] font-bold inline-flex items-center gap-2">Explore ImageSafe <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></span>
          </Link>
        </div>
      </div>
    </section>

    <!-- ABOUT -->
    <section id="about" class="bg-navy text-cream">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[660px] flex flex-col gap-4 mb-11">
          <div class="eyebrow">About Comordo</div>
          <h2 class="section-title">Digital assurance for schools and trusts.</h2>
          <p class="text-[15.5px] leading-relaxed text-cream/70 text-pretty">Comordo is an education technology company building digital assurance tools for UK schools and multi-academy trusts. Our products pair intelligent scanning with structured human review, so the people responsible stay in control of every decision.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
          <div v-for="a in about" :key="a.title" class="rounded-xl p-[clamp(22px,3vw,30px)] flex flex-col gap-2.5 bg-cream/[0.04]">
            <h3 class="text-[17px] font-bold">{{ a.title }}</h3>
            <p class="text-[14.5px] leading-relaxed text-cream/70 text-pretty m-0">{{ a.body }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faqs" class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[0.8fr_1.4fr] gap-16 py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4">
          <div class="eyebrow">FAQs</div>
          <h2 class="section-title">Sensible questions, straight answers.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate">Anything we haven&rsquo;t covered, ask us on a demo call.</p>
        </div>
        <div class="border-t border-sand">
          <div v-for="(f, i) in faqs" :key="f.q" class="border-b border-sand">
            <button class="w-full flex justify-between items-center gap-6 py-5 px-1 text-left cursor-pointer hover:bg-white/60" @click="openFaq = openFaq === i ? -1 : i">
              <span class="text-[16.5px] font-bold">{{ f.q }}</span>
              <span class="font-mono text-lg text-copper w-6 text-center shrink-0">{{ openFaq === i ? '−' : '+' }}</span>
            </button>
            <p v-if="openFaq === i" class="text-[14.5px] leading-relaxed text-slate pb-6 px-1 pr-10 max-w-[640px] text-pretty m-0">{{ f.a }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FINAL CTA -->
    <section>
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="border border-sand-dark rounded-xl bg-white p-[clamp(28px,6vw,64px)] flex flex-col items-center text-center gap-4.5">
          <div class="eyebrow after:content-[''] after:block after:w-6 after:h-px after:bg-copper">Get started</div>
          <h2 class="text-[clamp(30px,5vw,40px)] font-bold tracking-[-0.025em] max-w-[640px] leading-[1.1] text-balance">Clearer oversight across your digital estate.</h2>
          <p class="text-base leading-relaxed text-slate max-w-[500px]">Explore ImageSafe, or talk to us about your organisation.</p>
          <div class="flex flex-wrap justify-center gap-3.5 mt-2.5">
            <Link href="/imagesafe" class="btn-primary group">Explore ImageSafe <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></Link>
            <a :href="demoHref" class="btn-outline">Book a demo</a>
          </div>
        </div>
      </div>
    </section>

    <SiteFooter />
  </div>
</template>
