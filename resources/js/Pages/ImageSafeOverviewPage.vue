<script setup>
// ImageSafe summary on comordo.com. Copy is drawn from ImageSafePage.vue; the full story lives on imagesafe.co.uk.
import { identityStartHref } from '@/identity.js'
import { imagesafeSiteUrl } from '@/site.js'
import SiteNav from '../Components/SiteNav.vue'
import SiteFooter from '../Components/SiteFooter.vue'

const imagesafeHref = imagesafeSiteUrl
const pricingHref = `${imagesafeSiteUrl}/pricing`

const risks = [
  { title: 'Hard to keep track of', body: "Photos are scattered across your website and documents, with no simple way to see them all or know who's in them." },
  { title: 'Permissions change', body: 'Pupils leave, consent is withdrawn and older photos can remain published without anyone realising they need reviewing.' },
  { title: 'Images can be misused', body: 'Clear faces, named captions and high-resolution photos make it easier for images to be copied, manipulated or used to create AI-generated fakes.' },
]

const steps = [
  { n: '01', title: 'Start by finding out what is actually out there', body: "Enter your school's web address and ImageSafe does the rest. It reads your public pages, linked PDFs and video posters, finds every image containing a person, and ranks each one high, medium or low. Scans run overnight on a schedule you set, and nothing on your site is changed." },
  { n: '02', title: 'Check every photo for safeguarding risks', body: 'ImageSafe checks each photo for consent and potential AI misuse risks, including recognisable faces, close-ups, lone children, names, downloadable high-resolution images and location data. Every finding is for your staff to review, never an automatic judgement.' },
  { n: '03', title: "Know who's pictured and whether they have permission", body: 'Import consent records using our CSV template, with Arbor, Bromcom and SIMS integrations in development. ImageSafe matches pupils and staff across website images, PDFs and newsletters, and checks separate website and social media permissions.' },
  { n: '04', title: 'Make the call, and have it on record', body: 'Remove, fix on site, or keep as is. Download a safe version with unpermitted faces censored. Every decision is logged with who made it and when, ready for governors, your DPO or an inspection.' },
]

const audiences = [
  { title: 'Schools and multi-academy trusts', body: 'Whether you are a single school or a multi-academy trust, the same record covers every site, with trust-wide oversight when you need it. School staff see their own school’s records, while trust administrators have oversight across the trust.' },
  { title: 'Safeguarding leads and communications teams', body: 'Your safeguarding lead gets a clear picture of what’s online, what needs reviewing and why. Safeguarding and communications teams share a clear workflow for reviewing anything that needs attention.' },
]
</script>

<template>
  <div class="min-h-screen">
    <SiteNav active="imagesafe" />

    <!-- HERO -->
    <section class="border-b border-sand">
      <div class="container-site py-[clamp(56px,9vw,88px)] pb-[clamp(64px,10vw,104px)]">
        <div class="max-w-[760px] flex flex-col gap-6">
          <div class="eyebrow">ImageSafe by Comordo</div>
          <h1 class="text-[clamp(34px,6vw,56px)] font-bold leading-[1.06] tracking-[-0.025em] text-balance">Every photo your school publishes is a safeguarding decision.</h1>
          <p class="text-[17.5px] leading-relaxed text-slate max-w-[600px] text-pretty">ImageSafe finds photos of people across your website, prospectuses and newsletters, checking them against permission records and safeguarding guidance. It highlights images that need attention, flags potential AI misuse risks and helps you create safer versions in a click.</p>
          <div class="flex flex-wrap gap-3.5 mt-1">
            <a :href="imagesafeHref" class="btn-primary group">See ImageSafe <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></a>
            <a :href="identityStartHref" class="btn-outline">Start free</a>
          </div>
        </div>
      </div>
    </section>

    <!-- WHAT IT DOES -->
    <section class="bg-navy text-cream">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[720px] flex flex-col gap-4 mb-11">
          <div class="eyebrow">What it does</div>
          <h2 class="section-title">Your website holds years of photos of pupils. Do you know what&rsquo;s still out there?</h2>
          <p class="text-[15.5px] leading-relaxed text-cream/70 text-pretty">Across galleries, news posts and prospectuses, photos remain online long after pupils leave or permissions change. Some include names or high-resolution images that are easy to copy and manipulate using AI.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
          <div v-for="r in risks" :key="r.title" class="rounded-xl p-[clamp(22px,3vw,30px)] flex flex-col gap-2.5 bg-cream/[0.04]">
            <h3 class="text-[17px] font-bold">{{ r.title }}</h3>
            <p class="text-[14.5px] leading-relaxed text-cream/70 text-pretty m-0">{{ r.body }}</p>
          </div>
        </div>
        <p class="mt-9 text-[16px] font-medium text-[#E7C9A8]">You can&rsquo;t protect what you don&rsquo;t know is there.</p>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="border-b border-sand bg-white">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[720px] flex flex-col gap-4 mb-12">
          <div class="eyebrow">How it works</div>
          <h2 class="section-title">How a school gets from its first scan to a decision it can stand behind</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">Four steps, all in one place.</p>
        </div>
        <ol class="grid lg:grid-cols-2 gap-6 list-none p-0 m-0">
          <li v-for="s in steps" :key="s.n" class="border border-sand rounded-xl bg-cream p-[clamp(24px,3.5vw,36px)] flex flex-col gap-3">
            <span class="font-mono text-[11.5px] uppercase tracking-[0.08em] text-copper">Step {{ s.n }}</span>
            <h3 class="text-[19px] font-bold leading-snug tracking-[-0.01em]">{{ s.title }}</h3>
            <p class="text-[14.5px] leading-relaxed text-slate text-pretty m-0">{{ s.body }}</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- WHO IT'S FOR -->
    <section class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[0.8fr_1.4fr] gap-16 py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4">
          <div class="eyebrow">Who it&rsquo;s for</div>
          <h2 class="section-title">Built for schools and trusts.</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
          <div v-for="a in audiences" :key="a.title" class="flex flex-col gap-2.5 border-t-2 border-copper pt-5">
            <h3 class="text-[17px] font-bold">{{ a.title }}</h3>
            <p class="text-[14.5px] leading-relaxed text-slate text-pretty m-0">{{ a.body }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- PRICING TEASER + CTA -->
    <section>
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="border border-sand-dark rounded-xl bg-white p-[clamp(28px,6vw,64px)] flex flex-col items-center text-center gap-4.5">
          <div class="eyebrow after:content-[''] after:block after:w-6 after:h-px after:bg-copper">Pricing</div>
          <h2 class="text-[clamp(30px,5vw,40px)] font-bold tracking-[-0.025em] max-w-[640px] leading-[1.1] text-balance">Per school, monthly or yearly, with a 7-day free trial.</h2>
          <a :href="pricingHref" class="text-[14.5px] font-bold inline-flex items-center gap-2 group">See pricing <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></a>
          <div class="flex flex-wrap justify-center gap-3.5 mt-2.5">
            <a :href="imagesafeHref" class="btn-primary group">See ImageSafe <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></a>
            <a :href="identityStartHref" class="btn-outline">Start free</a>
          </div>
        </div>
      </div>
    </section>

    <SiteFooter />
  </div>
</template>
