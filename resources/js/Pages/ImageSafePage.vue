<script setup>
import { identityBase } from '@/identity.js'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { createPreview } from '@/usePreview.js'
import SiteNav from '../Components/SiteNav.vue'
import SiteFooter from '../Components/SiteFooter.vue'

const identityRegisterHref = `${identityBase}/start`
const previewUrl = ref('')
const previewError = ref(false)
const previewConfirmed = ref(false)
const previewNeedsConfirm = ref(false)
const previewStarting = ref(false)
const previewFailure = ref(null) // blocked | limited | busy
const errorCopy = {
  blocked: "That address can't be scanned.",
  limited: 'Lots of previews are running right now. Try again in a few minutes.',
  busy: 'Previews are busy right now. Try again shortly, or book a demo.',
}
async function requestPreview() {
  if (previewStarting.value) return
  const u = previewUrl.value.trim()
  if (u.length < 4 || !/\.[a-z]{2,}/i.test(u) && !/^(https?:\/\/)?\d{1,3}(\.\d{1,3}){3}/.test(u)) { previewError.value = true; return }
  previewError.value = false
  if (!previewConfirmed.value) { previewNeedsConfirm.value = true; return }
  previewNeedsConfirm.value = false
  previewFailure.value = null
  previewStarting.value = true
  const res = await createPreview(u)
  if (res.id) {
    // The preview page shows this as "Viewing …"; the API doesn't return it.
    const host = u.replace(/^[a-z][a-z0-9+.-]*:\/\//i, '').replace(/^www\./i, '').split(/[/?#]/)[0]
    try { sessionStorage.setItem(`isp-preview-host:${res.id}`, host) } catch { /* storage blocked */ }
    router.visit(`/preview/${res.id}`)
    return
  }
  previewStarting.value = false
  previewFailure.value = res.error
}
watch(previewConfirmed, (v) => { if (v) previewNeedsConfirm.value = false })

const stages = [
  { n: '01', t: 'Discovered', d: 'Crawls find every published image across your school websites. Manual uploads welcome too.' },
  { n: '02', t: 'Analysed', d: 'Faces detected and compared against your authorised reference records; characteristics noted.' },
  { n: '03', t: 'Needs review', d: 'Images with unidentified people or notable observations join the staff review queue.' },
  { n: '04', t: 'Reviewed', d: 'A person looks, decides and records — with notes attached to the image itself.' },
  { n: '05', t: 'Actioned', d: 'Approved, replaced or taken down — the outcome and its reasoning land in the audit trail.' },
]
const observations = [
  'Individual appears alone in a close-up frame; image could easily be cropped to isolate them.',
  'A name badge may be legible at full resolution.',
  'EXIF data includes GPS coordinates — consider stripping metadata before publishing.',
  'The same person appears in 3 other published images this term.',
]
const orgRows = [
  { school: 'Elm Park Primary', images: '1,284', crawl: '2h ago', queue: '84 to review', cls: 'pill-amber' },
  { school: 'Ferndale High', images: '3,027', crawl: '1d ago', queue: '12 to review', cls: 'pill-muted' },
  { school: 'Birchwood SEND School', images: '642', crawl: '3h ago', queue: 'All reviewed', cls: 'pill-muted' },
  { school: 'St Aidan\u2019s CofE Primary', images: '918', crawl: 'crawling now', queue: '2 flags open', cls: 'pill-red' },
]
const openFaq = ref(0)
const faqs = [
  { q: 'What does ImageSafe check?', a: 'ImageSafe scans your school website for the photos, videos and newsletter PDFs it publishes. For each one it finds who appears and checks them against your permission records, and looks for pupils who must never be published, names next to or printed in a photo, location data in the file, and children in swimwear or undressed. Anything that needs a closer look joins your review queue.' },
  { q: 'Does ImageSafe make decisions automatically?', a: 'No. ImageSafe finds and flags; your staff decide. Every flagged image is reviewed by a person, who records the outcome (keep, replace or take down) and the reasoning. That decision, and who made it, stays in the audit history.' },
  { q: 'How does it know who has photo permission?', a: 'You add your pupils and staff in People & Permissions, either one at a time or by uploading a CSV with their permissions. Reference photos let ImageSafe recognise them in published images. It also tracks leavers, moves year groups on at the end of the school year, and reminds you when permissions are due for renewal.' },
  { q: 'Can it check our Facebook and Instagram?', a: 'Yes. The account owner connects the school’s Facebook Page and Instagram account and chooses which ones ImageSafe can see. Recent photos are checked in the same way as photos on your website.' },
  { q: 'Can we check photos before they go online?', a: 'Yes. If your school website runs on WordPress, the ImageSafe plugin checks each photo as it is uploaded and tells you whether it is safe to publish, and why not if it isn’t. The photo is checked and then deleted from ImageSafe straight away.' },
  { q: 'What is AI exposure?', a: 'AI image datasets have been built from photos collected from public websites. ImageSafe checks whether your website turns away AI crawlers, whether pages with photos of children are already held in public web archives used to train AI, and whether your photos appear in Google and Bing image search. Where they do, the Reports page gives you ready-written removal requests.' },
  { q: 'How is children’s face data protected?', a: 'Face data is encrypted at rest, and access to it is role-based and logged. When a photo has not been seen on your website for a set period (90 days by default), its face data is removed automatically. Your school or trust is the data controller, and we’re happy to walk your DPO through data handling in detail.' },
  { q: 'Does it work for a trust as well as a single school?', a: 'Yes. A single school gets its own workspace. A trust adds every school and sees them together in an All schools view, with staff given access to the schools they work with and trust admins overseeing the whole estate.' },
  { q: 'What reports does it produce?', a: 'A termly safeguarding report for governors (photos found, decisions made, removals and fixes confirmed, alerts and AI exposure) as a PDF, and a compliance summary for one school or several that you can export as CSV or PDF.' },
  { q: 'How much does it cost, and can we try it?', a: 'ImageSafe is licensed per school, billed monthly or yearly, with volume discounts as you add schools. Every account starts with a 7-day free trial, no card needed, and you can request a free preview of one school website before signing up.' },
]

const responsible = [
  { n: '01', t: 'Controlled access', d: 'Role-based access, so imagery and reference records are only visible to the staff who need them.' },
  { n: '02', t: 'Human review', d: 'Analysis produces observations for staff. No safeguarding decision is ever automated.' },
  { n: '03', t: 'Your reference data', d: 'Authorised reference records are created and managed by your organisation, under your governance.' },
  { n: '04', t: 'Transparent audit history', d: 'Every access, review and decision is logged — clear accountability from crawl to outcome.' },
]
</script>

<template>
  <div class="min-h-screen">
    <SiteNav active="imagesafe" cta-label="Request a preview" cta-href="/#preview" />

    <!-- HERO -->
    <section class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[1.05fr_1fr] gap-14 items-center py-[clamp(56px,9vw,88px)] pb-[clamp(64px,10vw,104px)]">
        <div class="flex flex-col gap-6">
          <div class="eyebrow">ImageSafe by Comordo</div>
          <h1 class="text-[clamp(34px,6vw,52px)] font-bold leading-[1.06] tracking-[-0.025em] text-balance">Image safeguarding workspace for schools and trusts.</h1>
          <p class="text-[17.5px] leading-relaxed text-slate max-w-[520px] text-pretty">ImageSafe gives schools and trusts a clearer view of the imagery published across their websites — who appears in it, what may need a closer look, and a structured safeguarding workflow for reviewing it. Analysis supports your staff; it never decides for them.</p>
          <div class="flex flex-wrap gap-3.5 mt-1">
            <a href="#preview" class="btn-primary group">Request a free image preview <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></a>
            <Link href="/demo" class="btn-outline">Book a demo</Link>
          </div>
          <p class="text-[13px] text-stone">A human review and safeguarding assurance tool — built for DSLs, communications and governance teams.</p>
        </div>

        <!-- image library mock -->
        <div class="relative">
          <div class="mock-card shadow-[0_30px_70px_rgba(27,37,54,0.14)]">
            <div class="flex items-center justify-between px-4.5 py-3 border-b border-sand-light">
              <div class="flex flex-col gap-1">
                <b class="text-[13px]">Elm Park Primary · Image library</b>
                <span class="font-mono text-[10.5px] text-mist">Crawl complete · 1,284 images · 214 pages</span>
              </div>
              <span class="pill-amber !text-[9.5px] normal-case">84 need review</span>
            </div>
            <div class="flex gap-2 px-4.5 py-3 border-b border-hair flex-wrap">
              <span class="font-mono text-[10px] text-cream bg-navy rounded-full px-2.5 py-1">All images</span>
              <span v-for="f in ['People detected', 'Unidentified', 'Flagged']" :key="f" class="font-mono text-[10px] text-stone border border-sand-light rounded-full px-2.5 py-1">{{ f }}</span>
            </div>
            <div class="p-4">
              <div class="grid grid-cols-4 gap-2">
                <div v-for="i in 8" :key="i" class="aspect-[4/3] rounded-lg relative overflow-hidden"
                  :class="[['bg-sand','bg-[#DCE0DE]','bg-[#E2DACB]','bg-[#D8DCE2]','bg-[#DFD8CE]','bg-[#E0E4DC]','bg-[#E5DFD3]','bg-[#E7E0D2]'][i-1], i === 2 ? 'shadow-[inset_0_0_0_2px_#C0760B]' : '', i === 5 ? 'shadow-[inset_0_0_0_2px_#C42E22]' : '']">
                  <i v-if="i % 3 !== 0" class="absolute left-1/2 -bottom-[16%] -translate-x-1/2 w-[55%] aspect-square rounded-t-full bg-black/10"></i>
                  <i v-if="i % 3 !== 0" class="absolute left-1/2 top-[18%] -translate-x-1/2 w-[25%] aspect-square rounded-full bg-black/10"></i>
                  <span v-if="i === 2" class="absolute top-1.5 right-1.5 pill-amber !text-[8px] !px-1.5 !py-0.5 normal-case">review</span>
                  <span v-if="i === 5" class="absolute top-1.5 right-1.5 pill-red !text-[8px] !px-1.5 !py-0.5 normal-case">flag</span>
                </div>
              </div>
            </div>
          </div>
          <div class="absolute -right-3.5 -bottom-5 bg-navy text-cream rounded-[10px] px-4 py-3.5 shadow-[0_18px_44px_rgba(27,37,54,0.30)] flex flex-col gap-1.5 w-[210px]">
            <span class="font-mono text-[10px] tracking-[0.08em] uppercase text-copper">Review queue</span>
            <span class="text-xs leading-normal text-cream/85"><b>84 images</b> awaiting review · 3 assigned to you · oldest 2 days</span>
          </div>
        </div>
      </div>
    </section>

    <!-- WORKFLOW -->
    <section id="workflow" class="bg-navy text-cream">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[700px] flex flex-col gap-3.5 mb-14">
          <div class="eyebrow">The review workflow</div>
          <h2 class="section-title">Findings become a manageable queue, not a worry list.</h2>
          <p class="text-[15.5px] leading-relaxed text-cream/70 text-pretty">Every image moves through a clear set of stages. Staff add notes, resolve flags and record decisions — and the full history stays with the image.</p>
        </div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(165px,1fr))] gap-y-9">
          <div v-for="s in stages" :key="s.n" class="border-l border-cream/20 pl-5 pr-5 py-1 flex flex-col gap-2.5">
            <span class="font-mono text-xs text-copper">{{ s.n }}</span>
            <b class="text-[17px]">{{ s.t }}</b>
            <p class="text-[13px] leading-normal text-cream/65 m-0">{{ s.d }}</p>
          </div>
        </div>
        <div class="mt-12 px-6 py-5 border border-cream/15 rounded-[10px] flex flex-wrap gap-x-6 gap-y-3 items-baseline">
          <span class="font-mono text-[11px] tracking-[0.08em] uppercase text-copper">The principle</span>
          <p class="text-[15px] leading-normal text-cream/85 flex-1 basis-[400px] m-0">ImageSafe identifies faces, compares reference records and surfaces considerations. Whether an image is appropriate — and what to do about it — is always your staff&rsquo;s decision.</p>
        </div>
      </div>
    </section>

    <!-- WHO APPEARS -->
    <section id="people" class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[1fr_1.1fr] gap-16 items-center py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4.5">
          <div class="eyebrow">Understand who appears</div>
          <h2 class="section-title">Known people, recognised. Unknown people, surfaced.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">Your organisation keeps authorised reference records for the people who may appear in school imagery. Detected faces are compared against those records — so staff can see at a glance who has been identified, and which images contain people nobody has accounted for yet.</p>
          <ul class="flex flex-col gap-3 text-[14.5px] font-medium mt-1.5">
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Identified people linked to their reference record and consent position</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Unidentified people highlighted for staff follow-up</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Repeat appearances of the same person tracked across the site</li>
          </ul>
        </div>
        <div class="mock-card !rounded-[10px]">
          <div class="flex items-center justify-between px-5 py-3.5 border-b border-sand-light">
            <span class="font-mono text-[11px] text-mist">People in this image · 3 detected</span>
            <span class="font-mono text-[10.5px] text-mist">autumn-fair-04.jpg</span>
          </div>
          <div v-for="(p, i) in [
            { name: 'O. Mensah · Year 5', sub: 'reference record · consent on file', state: 'Identified', cls: 'pill-green', known: true },
            { name: 'T. Whitfield · Staff', sub: 'reference record · staff consent', state: 'Identified', cls: 'pill-green', known: true },
            { name: 'Unidentified person', sub: 'no matching reference record', state: 'Follow up', cls: 'pill-amber', known: false },
          ]" :key="p.name" class="flex items-center gap-3 px-5 py-3.5 border-b border-hair" :class="i === 1 ? 'bg-[#FBFAF6]' : ''">
            <span v-if="p.known" class="w-[34px] h-[34px] rounded-full bg-sand relative overflow-hidden shrink-0"><i class="absolute left-1/2 -bottom-[22%] -translate-x-1/2 w-[70%] aspect-square rounded-t-full bg-edge"></i><i class="absolute left-1/2 top-[16%] -translate-x-1/2 w-[34%] aspect-square rounded-full bg-edge"></i></span>
            <span v-else class="w-[34px] h-[34px] rounded-full bg-[#F0EBDF] border-[1.5px] border-dashed border-edge flex items-center justify-center font-mono text-xs text-stone shrink-0">?</span>
            <span class="flex flex-col gap-0.5"><b class="text-[13px]">{{ p.name }}</b><span class="font-mono text-[9.5px] text-mist">{{ p.sub }}</span></span>
            <span class="ml-auto !text-[9.5px] normal-case" :class="p.cls">{{ p.state }}</span>
          </div>
          <div class="flex gap-2.5 justify-end px-5 py-3.5">
            <span class="text-[12.5px] font-semibold text-slate px-3.5 py-2 border border-sand rounded-md bg-white">Link to record</span>
            <span class="text-[12.5px] font-semibold text-cream px-4 py-2 rounded-md bg-navy">Flag for review</span>
          </div>
        </div>
      </div>
    </section>

    <!-- OBSERVATIONS -->
    <section id="observations" class="border-b border-sand bg-white">
      <div class="container-site grid lg:grid-cols-[1.1fr_1fr] gap-16 items-center py-[clamp(56px,9vw,96px)]">
        <div class="max-lg:order-2 bg-cream border border-sand rounded-[10px] shadow-[0_20px_50px_rgba(27,37,54,0.08)] overflow-hidden">
          <div class="flex items-center justify-between px-5 py-3.5 border-b border-sand-light bg-white">
            <span class="font-mono text-[11px] text-mist">Observations · sports-day-2026.jpg</span>
            <span class="pill-amber !text-[10.5px] normal-case">Needs review</span>
          </div>
          <div class="p-5 flex flex-col gap-2.5">
            <div v-for="(o, i) in observations" :key="i" class="flex gap-3 items-baseline bg-white border border-sand-light rounded-lg px-3.5 py-3">
              <span class="font-mono text-[10px] text-copper shrink-0">OBS 0{{ i + 1 }}</span>
              <span class="text-[13px] leading-normal text-slate">{{ o }}</span>
            </div>
            <p class="text-xs leading-normal text-stone m-0 pt-1 px-0.5">Observations are considerations for staff — not automated risk decisions.</p>
          </div>
        </div>
        <div class="flex flex-col gap-4.5">
          <div class="eyebrow">See what needs attention</div>
          <h2 class="section-title">Considerations, not verdicts.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">Alongside who appears, ImageSafe notes things worth a second look — framing, visible identifiers, metadata, repeat appearances. Each is presented as an observation for staff to weigh up, with the image and its context in front of them. Some will be fine. Some will prompt a change. Your team decides which.</p>
          <ul class="flex flex-col gap-3 text-[14.5px] font-medium mt-1.5">
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Framing and visibility — close-ups, individuals versus groups</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Identifiers — name badges, clothing, background details</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Metadata — EXIF and GPS information travelling with the file</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- WHOLE ORGANISATION -->
    <section id="organisation" class="border-b border-sand">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[720px] flex flex-col gap-4.5 mb-12">
          <div class="eyebrow">See the whole organisation</div>
          <h2 class="section-title">Trust-wide visibility, school-level detail.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">Register every school and see imagery oversight across the estate — crawl status, review queues and flags by school — while each school works its own queue with its own records.</p>
        </div>
        <div class="border border-sand rounded-[10px] overflow-hidden bg-white">
          <div class="grid grid-cols-[1.3fr_1fr_1fr_auto] gap-4 px-5 py-3 bg-[#FBFAF6] border-b border-hair font-mono text-[10px] uppercase tracking-[0.06em] text-stone">
            <span>School</span><span>Images</span><span>Last crawl</span><span>Queue</span>
          </div>
          <div v-for="(r, i) in orgRows" :key="r.school" class="grid grid-cols-[1.3fr_1fr_1fr_auto] gap-4 items-center px-5 py-3.5 border-b border-hair last:border-b-0" :class="i % 2 ? 'bg-[#FBFAF6]' : ''">
            <b class="text-[13.5px]">{{ r.school }}</b>
            <span class="font-mono text-[11px] text-slate">{{ r.images }}</span>
            <span class="font-mono text-[11px] text-mist">{{ r.crawl }}</span>
            <span :class="r.cls" class="!text-[10.5px] normal-case">{{ r.queue }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- RESPONSIBLE USE -->
    <section id="responsible-use" class="border-b border-sand">
      <div class="container-site py-[clamp(56px,9vw,96px)]">
        <div class="max-w-[720px] flex flex-col gap-4.5 mb-12">
          <div class="eyebrow">Built for responsible use</div>
          <h2 class="section-title">Sensitive information, handled carefully.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate text-pretty">ImageSafe works with imagery of children and reference records your organisation controls. The product is designed so that access is deliberate, decisions are human and everything is accountable.</p>
        </div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(215px,1fr))] border-t border-l border-sand">
          <div v-for="c in responsible" :key="c.n" class="p-6 border-r border-b border-sand flex flex-col gap-2 hover:bg-white">
            <span class="font-mono text-[11px] text-copper">{{ c.n }}</span>
            <b class="text-[15.5px]">{{ c.t }}</b>
            <p class="text-[13px] leading-normal text-slate m-0">{{ c.d }}</p>
          </div>
        </div>
        <p class="mt-7 text-[13.5px] leading-relaxed text-stone max-w-[640px]">We&rsquo;re happy to walk safeguarding, data protection and information governance teams through data handling in detail — <Link href="/contact" class="text-copper font-semibold no-underline">get in touch</Link>.</p>
      </div>
    </section>

    <!-- FREE PREVIEW -->
    <section id="preview" class="bg-navy text-cream">
      <div class="container-site grid lg:grid-cols-[1.1fr_1fr] gap-16 items-center py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4.5">
          <div class="eyebrow">Free preview</div>
          <h2 class="text-[clamp(29px,5vw,38px)] font-bold leading-[1.12] tracking-[-0.02em] text-balance">See your imagery the way ImageSafe does.</h2>
          <p class="text-[15.5px] leading-relaxed text-cream/70 text-pretty">See the first photos with people on your school website in under a minute, in the ImageSafe dashboard. Then start your free trial to check every page.</p>
          <ul class="flex flex-col gap-3 text-[14.5px] text-cream/85 mt-1.5">
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>Instant results from the first pages of your website</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>No reference records or personal data needed for a preview</li>
            <li class="flex gap-3 items-baseline"><span class="font-mono text-xs text-copper">→</span>No sign-up needed to see your preview</li>
          </ul>
        </div>
        <div class="bg-cream text-navy rounded-xl p-[clamp(22px,5vw,36px)] flex flex-col gap-4.5 shadow-[0_24px_60px_rgba(0,0,0,0.3)]">
          <b class="text-[19px]">See your preview</b>
            <label class="flex flex-col gap-2">
              <span class="font-mono text-[11.5px] tracking-[0.06em] uppercase text-stone">School website address</span>
              <input v-model="previewUrl" class="field font-mono text-sm" placeholder="www.yourschool.org.uk" @keydown.enter="requestPreview">
            </label>
            <label class="flex gap-3 items-start text-[13.5px] leading-normal text-slate cursor-pointer">
              <input v-model="previewConfirmed" type="checkbox" class="mt-0.5 w-4 h-4 shrink-0 accent-copper">
              <span>I work for this school or trust, or have its permission to scan this website.</span>
            </label>
            <button class="btn-primary group w-full" :disabled="!previewConfirmed || previewStarting" :class="previewConfirmed && !previewStarting ? '' : 'opacity-50 cursor-not-allowed'" @click="requestPreview">{{ previewStarting ? 'Starting your preview…' : 'Request a free image preview' }} <span class="transition-transform group-hover:translate-x-1"><svg width="7" height="11" viewBox="0 0 7 11" fill="none"><path d="M1 1l4.6 4.5L1 10" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span></button>
            <p v-if="previewError" class="flex gap-2.5 items-baseline px-3.5 py-3 bg-[#F4E2DC] rounded-lg text-[13.5px] leading-normal text-[#9A3B2A] m-0"><b>!</b> Please enter a full school website address, like <b>www.yourschool.org.uk</b>.</p>
            <p v-if="previewNeedsConfirm" class="flex gap-2.5 items-baseline px-3.5 py-3 bg-[#F4E2DC] rounded-lg text-[13.5px] leading-normal text-[#9A3B2A] m-0"><b>!</b> Please confirm you have permission to scan this website.</p>
            <p v-if="previewFailure" role="alert" class="flex gap-2.5 items-baseline px-3.5 py-3 bg-[#F4E2DC] rounded-lg text-[13.5px] leading-normal text-[#9A3B2A] m-0"><b>!</b> {{ errorCopy[previewFailure] }}</p>
            <p class="text-[12.5px] leading-normal text-stone m-0">We only look at publicly visible pages and photos on the address you give. Previews show up to five photos with people.</p>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faqs" class="border-b border-sand">
      <div class="container-site grid lg:grid-cols-[0.8fr_1.4fr] gap-16 py-[clamp(56px,9vw,96px)]">
        <div class="flex flex-col gap-4">
          <div class="eyebrow">FAQs</div>
          <h2 class="section-title">Sensible questions, straight answers.</h2>
          <p class="text-[15.5px] leading-relaxed text-slate">Anything we haven&rsquo;t covered, <Link href="/contact" class="text-copper font-semibold no-underline">ask us</Link> or book a demo.</p>
        </div>
        <div class="border-t border-sand">
          <div v-for="(f, i) in faqs" :key="f.q" class="border-b border-sand">
            <button class="w-full flex justify-between items-center gap-6 py-5 px-1 text-left cursor-pointer hover:bg-white/60" :aria-expanded="openFaq === i" @click="openFaq = openFaq === i ? -1 : i">
              <span class="text-[16.5px] font-bold">{{ f.q }}</span>
              <span class="font-mono text-lg text-copper w-6 text-center shrink-0" aria-hidden="true">{{ openFaq === i ? '−' : '+' }}</span>
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
          <h2 class="text-[clamp(30px,5vw,40px)] font-bold tracking-[-0.025em] max-w-[640px] leading-[1.1] text-balance">Safer image publishing starts with visibility.</h2>
          <p class="text-base leading-relaxed text-slate max-w-[480px]">Request a free image preview for one school, or talk to us about your organisation.</p>
          <div class="flex flex-wrap justify-center gap-3.5 mt-2.5">
            <a href="#preview" class="btn-primary">Request a free image preview</a>
            <a :href="identityRegisterHref" class="btn-outline">Start a 7-day free trial</a>
            <Link href="/demo" class="btn-outline">Book a demo</Link>
          </div>
        </div>
      </div>
    </section>

    <SiteFooter />
  </div>
</template>
