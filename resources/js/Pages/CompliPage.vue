<script setup>
import SiteNav from '../components/SiteNav.vue'
import SiteFooter from '../components/SiteFooter.vue'

const steps = [
  { n: '01', t: 'Find the evidence', d: 'Automated scans read each website and match what they find against the requirements for that school.' },
  { n: '02', t: 'Review the requirement', d: 'Staff check the evidence, add notes and supporting documents, and judge whether it satisfies the requirement.' },
  { n: '03', t: 'Record the decision', d: 'Set the status. Every change lands in the audit history — who, when, and on what evidence.' },
  { n: '04', t: 'Track progress', d: 'Rescan over time, watch the position move and report on any school or the whole organisation.' },
]
const classes = [
  { n: 'Class 01', t: 'Statutory', d: 'UK publication requirements, kept current and matched to each school\u2019s type — primary, secondary, all-through, SEND, special.' },
  { n: 'Class 02', t: 'Trust mandatory', d: 'Your own required standards — internal policies, local agreements, anything you want evidenced consistently across every school.' },
  { n: 'Class 03', t: 'Trust recommended', d: 'Good-practice items you encourage but don\u2019t mandate — tracked separately so they never muddy the statutory picture.' },
]
const reqRows = [
  { dot: 'bg-rag-green', name: 'Ofsted reports & results', sub: 'evidence · /about/ofsted', pill: 'Met · reviewed', cls: 'pill-muted' },
  { dot: 'bg-rag-amber', name: 'Pupil premium strategy', sub: 'possible evidence · dated 2024', pill: 'Needs review', cls: 'pill-amber' },
  { dot: 'bg-rag-red', name: 'SEND information report', sub: 'no matching page found', pill: 'Not found', cls: 'pill-red' },
  { dot: 'bg-rag-green', name: 'Safeguarding policy', sub: 'evidence · policy-2026.pdf', pill: 'Met · reviewed', cls: 'pill-muted' },
]
const trustRows = [
  { name: 'Elm Park Primary', sub: 'primary · 68 reqs', w: '92%', bar: 'bg-rag-green', cls: 'pill-muted' },
  { name: 'Ferndale High', sub: 'secondary · 74 reqs', w: '64%', bar: 'bg-rag-amber', cls: 'pill-amber' },
  { name: 'Birchwood SEND School', sub: 'special · 71 reqs', w: '97%', bar: 'bg-rag-green', cls: 'pill-muted' },
  { name: 'St Aidan\u2019s CofE Primary', sub: 'primary · 69 reqs', w: '41%', bar: 'bg-rag-red', cls: 'pill-red' },
]
</script>

<template>
  <div class="min-h-screen">
    <SiteNav active="compli" cta-label="Register interest" cta-href="/contact" />

    <!-- HERO -->
    <section class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[1.05fr_1fr] gap-14 items-center py-[clamp(56px,9vw,88px)] pb-[clamp(64px,10vw,104px)]">
        <div class="flex flex-col gap-6">
          <div class="eyebrow">Compli by Comordo · Coming soon</div>
          <h1 class="text-[clamp(34px,6vw,52px)] font-bold leading-[1.06] tracking-[-0.025em] text-balance">Compliance shouldn&rsquo;t be a once-a-year exercise.</h1>
          <p class="text-[17.5px] leading-relaxed text-slate max-w-[520px] text-pretty">A collaborative compliance workspace for schools and multi-academy trusts. Scan school websites against publication requirements, review the evidence found, set the compliance position and keep an audit history you can share at any time.</p>
          <div class="flex flex-wrap gap-3.5 mt-1">
            <Link href="/contact" class="btn-primary group">Register interest <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></Link>
            <Link href="/demo" class="btn-outline">Book a demo</Link>
          </div>
          <p class="text-[13px] text-stone">Compli is launching soon — register and be first to run a free compliance snapshot.</p>
        </div>

        <!-- requirements mock -->
        <div class="relative">
          <div class="mock-card shadow-[0_30px_70px_rgba(27,37,54,0.14)]">
            <div class="flex items-center justify-between px-4.5 py-3 border-b border-sand-light">
              <div class="flex flex-col gap-1">
                <b class="text-[13px]">Elm Park Primary · Requirements</b>
                <span class="font-mono text-[10.5px] text-mist">Last scan 2h ago · 68 requirements</span>
              </div>
              <div class="flex gap-1.5">
                <span class="pill-green !text-[9.5px] normal-case">58 met</span>
                <span class="pill-amber !text-[9.5px] normal-case">7 review</span>
                <span class="pill-red !text-[9.5px] normal-case">3 gaps</span>
              </div>
            </div>
            <div class="flex gap-2 px-4.5 py-3.5 border-b border-hair">
              <span class="font-mono text-[10px] text-cream bg-navy rounded-full px-2.5 py-1">Statutory</span>
              <span class="font-mono text-[10px] text-stone border border-sand-light rounded-full px-2.5 py-1">Trust mandatory</span>
              <span class="font-mono text-[10px] text-stone border border-sand-light rounded-full px-2.5 py-1">Trust recommended</span>
            </div>
            <div v-for="(r, i) in reqRows" :key="r.name" class="grid grid-cols-[auto_1fr_auto] gap-3 items-center px-4.5 py-3 border-b border-hair last:border-b-0" :class="i % 2 ? 'bg-[#FBFAF6]' : ''">
              <i class="w-2 h-2 rounded-full" :class="r.dot"></i>
              <span class="flex flex-col gap-0.5"><b class="text-[12.5px]">{{ r.name }}</b><span class="font-mono text-[9.5px] text-mist">{{ r.sub }}</span></span>
              <span :class="r.cls" class="!text-[9.5px] normal-case">{{ r.pill }}</span>
            </div>
          </div>
          <div class="absolute -right-3.5 -bottom-5 bg-navy text-cream rounded-[10px] px-4 py-3.5 shadow-[0_18px_44px_rgba(27,37,54,0.30)] flex flex-col gap-1.5 w-[200px]">
            <span class="font-mono text-[10px] tracking-[0.08em] uppercase text-copper">Audit history</span>
            <span class="text-xs leading-normal text-cream/85">Status set to <b>Met</b> by J. Hargreaves · evidence attached · 14 May, 09:42</span>
          </div>
        </div>
      </div>
    </section>

    <!-- WORKFLOW -->
    <section class="bg-navy text-cream">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[700px] flex flex-col gap-3.5 mb-14">
          <div class="eyebrow">How it works</div>
          <h2 class="section-title">Find the evidence. Review it. Record the decision. Track progress.</h2>
          <p class="text-[15.5px] leading-relaxed text-cream/70 text-pretty">Scans identify possible evidence and potential gaps. Your staff review each requirement and determine the final compliance position — the platform keeps the record.</p>
        </div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-y-9">
          <div v-for="s in steps" :key="s.n" class="border-l border-cream/20 pl-5 pr-5 py-1 flex flex-col gap-2.5">
            <span class="font-mono text-xs text-copper">{{ s.n }}</span>
            <b class="text-[17px]">{{ s.t }}</b>
            <p class="text-[13px] leading-normal text-cream/65 m-0">{{ s.d }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- REQUIREMENT CLASSES -->
    <section class="border-b border-sand">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[720px] flex flex-col gap-4.5 mb-12">
          <div class="eyebrow">Requirements</div>
          <h2 class="section-title">Your requirements, not just the statutory list.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">Every school is checked against what actually applies to it — with a clear RAG position across three classes of requirement.</p>
        </div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(260px,1fr))] gap-5">
          <div v-for="c in classes" :key="c.n" class="bg-white border border-sand rounded-[10px] p-7 flex flex-col gap-3">
            <span class="font-mono text-xs text-copper">{{ c.n }}</span>
            <b class="text-[17.5px]">{{ c.t }}</b>
            <p class="text-sm leading-relaxed text-slate m-0">{{ c.d }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- TRUST VIEW -->
    <section class="border-b border-sand bg-white">
      <div class="container-site grid lg:grid-cols-[1fr_1.1fr] gap-16 items-center py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4.5">
          <div class="eyebrow">For trusts</div>
          <h2 class="section-title">Know where every school stands.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">Organise schools within your trust and see the whole estate at a glance — compliance by school, movement over time and what&rsquo;s awaiting review. Compare schools, spot patterns and drill into any requirement without leaving the trust view.</p>
          <ul class="flex flex-col gap-3 text-[14.5px] font-medium mt-1.5">
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>RAG overview across every school, updated as reviews happen</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>School comparison and trends across the term</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Works for a single school too — same workspace, no trust layer</li>
          </ul>
        </div>
        <div class="mock-card !rounded-[10px]">
          <div class="flex items-center justify-between px-5 py-3.5 border-b border-sand-light">
            <span class="font-mono text-[11px] text-mist">Hartswood Learning Trust · 12 schools</span>
            <span class="font-mono text-[10.5px] text-mist">Compliance by school</span>
          </div>
          <div v-for="(r, i) in trustRows" :key="r.name" class="grid grid-cols-[1.3fr_1fr_auto] gap-4 items-center px-5 py-3.5 border-b border-hair last:border-b-0" :class="i % 2 ? 'bg-[#FBFAF6]' : ''">
            <span class="flex flex-col gap-0.5"><b class="text-[12.5px]">{{ r.name }}</b><span class="font-mono text-[9.5px] text-mist">{{ r.sub }}</span></span>
            <span class="h-2 rounded bg-[#F0EBDF] overflow-hidden"><i class="block h-full rounded" :class="r.bar" :style="{ width: r.w }"></i></span>
            <span :class="r.cls" class="!text-[10.5px] normal-case">{{ r.w }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- COMING SOON / SNAPSHOT -->
    <section id="snapshot" class="bg-navy text-cream">
      <div class="container-site grid lg:grid-cols-[1.1fr_1fr] gap-16 items-center py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4.5">
          <div class="eyebrow">Free snapshot</div>
          <h2 class="text-[clamp(29px,5vw,38px)] font-bold leading-[1.12] tracking-[-0.02em] text-balance">How compliant is your school website?</h2>
          <p class="text-[15.5px] leading-relaxed text-cream/70 text-pretty">When Compli launches, you&rsquo;ll be able to enter your website and get an initial snapshot of potential publication gaps — no account required. Paid plans unlock the full ongoing workspace: evidence review, history, rescanning, reporting and organisation-wide oversight.</p>
          <ul class="flex flex-col gap-3 text-[14.5px] text-cream/85 mt-1.5">
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Headline findings for one school website</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>A sample of issues by category — not the full list</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>No account, no card, no obligation</li>
          </ul>
        </div>
        <div class="bg-cream text-navy rounded-xl p-[clamp(22px,5vw,36px)] flex flex-col gap-4.5 shadow-[0_24px_60px_rgba(0,0,0,0.3)]">
          <b class="text-[19px]">Be first in line</b>
          <Link href="/contact" class="btn-primary group w-full">Register interest <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></Link>
          <p class="text-[12.5px] leading-normal text-stone m-0">Registering takes a minute — we&rsquo;ll let you know the moment the free snapshot opens.</p>
        </div>
      </div>
    </section>

    <!-- FINAL CTA -->
    <section>
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="border border-sand-dark rounded-xl bg-white p-[clamp(28px,6vw,64px)] flex flex-col items-center text-center gap-4.5">
          <div class="eyebrow after:content-[''] after:block after:w-6 after:h-px after:bg-copper">Get started</div>
          <h2 class="text-[clamp(30px,5vw,40px)] font-bold tracking-[-0.025em] max-w-[620px] leading-[1.1] text-balance">See where every school stands.</h2>
          <p class="text-base leading-relaxed text-slate max-w-[480px]">Compli launches soon. Register your interest, or talk to us about your trust.</p>
          <div class="flex flex-wrap justify-center gap-3.5 mt-2.5">
            <Link href="/contact" class="btn-primary">Register interest</Link>
            <Link href="/demo" class="btn-outline">Book a demo</Link>
          </div>
        </div>
      </div>
    </section>

    <SiteFooter />
  </div>
</template>
