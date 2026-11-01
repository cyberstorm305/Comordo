<script setup>
// ImageSafe homepage, built from the Claude Design handoff (design_handoff_imagesafe_homepage).
// Styles stay inline, as in the handoff, so values can be checked against the design one to one.
import { identityBase, identityStartHref } from '@/identity.js'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { createPreview } from '@/usePreview.js'
import SiteFooter from '../Components/SiteFooter.vue'
import SiteHeader from '../Components/SiteHeader.vue'

const trialHref = identityStartHref
const signInHref = `${identityBase}/login`
const photo = n => `/images/home/photo-${n}.jpg`

// --- Free preview form ---
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

// --- Testimonials and partner logos ---
// The band only shows once there are real quotes. Never ship placeholder quotes or sample logos.
const quotes = []
const partnerLogos = [] // { src, alt }: white-out SVGs
const quoteIndex = ref(0)
const quote = () => quotes[((quoteIndex.value % quotes.length) + quotes.length) % quotes.length]

// --- Mock data for the product frames ---
const queue = [
  { img: 2, name: 'prospectus.pdf (p. 23)', untagged: 1, selected: true },
  { img: 2, name: 'prospectus.pdf (p. 9)', untagged: 2 },
  { img: 4, name: 'IMG_1048-scaled.jpg', untagged: 23 },
  { img: 1, name: 'sports-day-2026.jpeg', untagged: 12 },
  { img: 2, name: 'choir-concert.jpg', untagged: 18 },
  { img: 4, name: 'newsletter-july.pdf (p. 2)', untagged: 4 },
]
const heroFaces = [
  { left: '9%', top: '23%', width: '13%', label: 'Unknown' },
  { left: '36.5%', top: '23%', width: '13%', label: 'Unknown' },
  { left: '64.5%', top: '24%', width: '13%', label: 'A. Okonkwo · Y5', tone: 'green' },
  { left: '82.5%', top: '20%', width: '14%', label: 'Unknown' },
]
const detailFaces = [
  { left: '5%', top: '21%', width: '12%', label: 'Unknown' },
  { left: '26%', top: '23%', width: '12%', label: 'Unknown' },
  { left: '39%', top: '13%', width: '13%', label: 'H. Begum · Y3', tone: 'red' },
  { left: '56%', top: '10%', width: '11%', label: 'Unknown' },
  { left: '66%', top: '24%', width: '12%', label: 'Unknown' },
  { left: '84%', top: '16%', width: '13%', label: 'J. Patel · Y6', tone: 'green' },
]
const faceTone = { green: '#178049', red: '#C42E22' }
const checkStyle = {
  flag: { row: 'background:#FBE9E7;border:1px solid #F3CFC9;color:#C42E22', dot: 'background:#C42E22;color:#fff', icon: '✕', tag: 'FLAG' },
  check: { row: 'background:#FBF1E2;border:1px solid #EFD9B6;color:#C0760B', dot: 'background:#C0760B;color:#fff', icon: '!', tag: 'CHECK' },
}
const heroChecks = [['flag', 'Recognisable faces'], ['flag', 'Close-up of a child'], ['flag', 'Single child in shot'], ['check', 'Permission to use']]
const detailChecks = [['flag', 'Permission refused'], ['flag', 'Recognisable faces'], ['check', 'High-resolution file'], ['check', 'Name in or near the photo']]
const chip = {
  red: 'background:#FBE9E7;color:#C42E22',
  amber: 'background:#FBF1E2;color:#C0760B',
}
const inventory = [
  { img: 3, risk: 'HIGH', name: 'prospectus.pdf (p. 23)', on: 'Prospectus', people: 1, checks: [['red', 'Recognisable faces'], ['red', 'Close-up of a child'], ['amber', 'Permission to use']] },
  { img: 4, risk: 'HIGH', name: 'IMG_1048-scaled.jpg', on: 'Website ↗', link: true, people: 24, checks: [['amber', 'High-resolution file'], ['amber', 'Permission to use']] },
  { img: 1, risk: 'HIGH', name: 'choir-concert.jpg', on: 'Website ↗', link: true, people: 18, checks: [['amber', 'High-resolution file'], ['amber', 'Permission to use']] },
  { img: 2, risk: 'MED', name: 'prospectus.pdf (p. 20)', on: 'Prospectus', people: 3, checks: [['amber', 'Permission to use'], ['amber', 'Name in or near the photo']] },
  { img: 1, risk: 'HIGH', name: 'sports-day-2026.jpeg', on: 'Website ↗', link: true, people: 12, checks: [['red', 'Single child in shot'], ['amber', 'Location data in file']] },
  { img: 3, risk: 'MED', name: 'newsletter-july.pdf (p. 2)', on: 'Newsletter', people: 4, checks: [['amber', 'Name in or near the photo']] },
  { img: 2, risk: 'MED', name: 'year6-residential.jpg', on: 'Website ↗', link: true, people: 7, checks: [['amber', 'High-resolution file'], ['amber', 'Permission to use']] },
  { img: 4, risk: 'LOW', name: 'harvest-festival.jpg', on: 'Website ↗', link: true, people: 9, checks: [['amber', 'Permission to use']] },
]
const riskColour = { HIGH: '#C42E22', MED: '#C0760B', LOW: '#178049' }
const ok = '<span style="color:#178049">✓</span>'
const no = '<span style="color:#C42E22">✕</span>'
const none = '<span style="color:#8A8474">—</span>'
const people = [
  { name: 'A. Okonkwo', sub: 'Updated 21 Oct 2026 · CSV import', avatar: '#c9a58a,#8f8f86', year: 'Y5', web: ok, social: ok, found: '2', foundStyle: 'color:#1B2536', status: 'Approved', statusStyle: 'background:#E6F3EB;color:#178049' },
  { name: 'H. Begum', sub: 'Updated 20 Oct 2026 · Manual edit', avatar: '#b58e75,#9a9a92', year: 'Y3', web: no, social: no, found: '3', foundStyle: 'color:#C42E22;font-weight:700', status: 'Refused — do not use', statusStyle: 'background:#C42E22;color:#fff', rowStyle: 'background:#FBE9E7' },
  { name: 'T. Walsh', sub: 'Left July 2025 · retention 12 months', avatar: '#d1b49a,#8a9aa1', year: '—', web: ok, social: none, found: '4', foundStyle: 'color:#C0760B;font-weight:700', status: 'Leaver · remove by Jul 2026', statusStyle: 'background:#FBF1E2;color:#C0760B' },
  { name: 'S. Ahmed', sub: 'Updated 18 Oct 2026 · Manual edit', avatar: '#c4a28c,#93a0a6', year: 'Y4', web: no, social: no, found: '3', foundStyle: 'color:#C42E22;font-weight:700', status: 'Never publish', statusStyle: 'background:#C42E22;color:#fff' },
  { name: 'J. Patel', sub: 'Updated 21 Oct 2026 · CSV import', avatar: '#bfa087,#9db1a8', year: 'Y6', web: ok, social: no, found: '1', foundStyle: 'color:#1B2536', status: 'Website only', statusStyle: 'background:#E6F3EB;color:#178049' },
]
const audit = [
  ['23 Oct, 14:02', 'Matt', 'Decision recorded', 'Remove from website · prospectus.pdf (page 23)'],
  ['23 Oct, 14:01', 'Matt', 'Safe version downloaded', '1 face censored · IMG_1048-scaled.jpg'],
  ['23 Oct, 13:58', 'Priya', 'Permission changed', 'Website: allowed → not allowed'],
  ['20 Oct, 01:00', 'System', 'Scheduled scan', '177 images · 12 new since last scan'],
]
const risks = [
  { icon: 'photos', title: 'Hard to keep track of', body: "Photos are scattered across your website and documents, with no simple way to see them all or know who's in them." },
  { icon: 'shield', title: 'Permissions change', body: 'Pupils leave, consent is withdrawn and older photos can remain published without anyone realising they need reviewing.' },
  { icon: 'warning', title: 'Images can be misused', body: 'Clear faces, named captions and high-resolution photos make it easier for images to be copied, manipulated or used to create AI-generated fakes.' },
]
const steps = [
  { n: '01', title: 'Start by finding out what is actually out there', body: ["Enter your school's web address and ImageSafe does the rest. It reads your public pages, linked PDFs and video posters, finds every image containing a person, and ranks each one high, medium or low. Scans run overnight on a schedule you set, and nothing on your site is changed."] },
  { n: '02', title: 'Check every photo for safeguarding risks', body: [
    'ImageSafe checks each photo for consent and potential AI misuse risks, including recognisable faces, close-ups, lone children, names, downloadable high-resolution images, location data, inappropriate exposure and signs of AI manipulation. It also checks whether AI crawlers can access your images and whether pages appear in Common Crawl archives.',
    'Every finding is for your staff to review, never an automatic judgement. Create a safer version in one click by blurring faces without consent and removing location data. ImageSafe reduces risk, but cannot prevent misuse elsewhere.',
  ] },
  { n: '03', title: "Know who's pictured and whether they have permission", body: [
    'Import consent records using our CSV template, with Arbor, Bromcom and SIMS integrations in development. ImageSafe matches pupils and staff across website images, PDFs and newsletters, checks separate website and social media permissions, and tracks every appearance against their profile.',
    "Pupils marked 'never publish' trigger urgent DSL alerts, and if consent is withdrawn while a pupil's photos are still online, your safeguarding team is alerted. School staff see their own school's records, while trust administrators have oversight across the trust.",
  ] },
  { n: '04', title: 'Make the call, and have it on record', body: ['Remove, fix on site, or keep as is. Download a safe version with unpermitted faces censored. Every decision is logged with who made it and when, ready for governors, your DPO or an inspection. Whether you are a single school or a multi-academy trust, the same record covers every site, with trust-wide oversight when you need it.'], cta: true },
]
const faqs = [
  { q: 'Can it stop deepfakes of our pupils?', a: 'No, and we would be wary of anything that claimed to. ImageSafe reduces what can be misused: it finds the close-ups, lone children, full-size originals, names next to faces and crawler-accessible pages that make a photo easy to manipulate, and helps you fix or replace them. It cannot detect or stop fake images made elsewhere from a photo someone already has.' },
  { q: 'Does it connect to our MIS?', a: 'Today, consent records come in by CSV import, using the template we provide; most schools export this from their MIS in a few minutes. Direct connections for Arbor, Bromcom and SIMS are in development.', link: { href: 'mailto:hello@comordo.com?subject=MIS%20connection%20interest', label: 'Register your interest', after: ' and we will tell you when yours is ready.' } },
  { q: 'Does it decide what to remove?', a: 'No. Every check is a consideration shown beside the photo for a member of staff to weigh. The decision, and who made it, is recorded. Nothing on your website is changed by ImageSafe.' },
  { q: 'How does it fit with KCSIE and DfE guidance?', a: 'ImageSafe is built around Keeping Children Safe in Education and DfE guidance on using images of pupils. It gives DSLs a record of who appears in each published photograph, whether permission exists, what was considered, what was decided and by whom: a review process you can show governors or an inspector.' },
  { q: 'Where does the face analysis happen, and what is kept?', a: "On ImageSafe's own servers, never a third-party AI service. Downloaded website images are deleted straight after analysis. Face data is encrypted and removed 90 days after a photo leaves your website. Your school stays in control of its data; Comordo only processes it under UK GDPR. Results are shown only to authorised staff and every action is logged. ICO guidance on biometric data, children's information and DPIAs is linked inside the product.", link: { href: '/privacy', label: 'Read our privacy notice →', internal: true } },
  { q: 'What does the instant preview do, and what does it not do?', a: 'It reads the first public pages of your website, counts the photos containing people and shows the checks they would raise. It does not identify anyone or need an account, your photos are never stored, and the results are deleted within 24 hours. A full scan, with permission matching, needs the 7-day trial.' },
]

// Only one FAQ open at a time. name="faq" does this natively; this covers older browsers.
function closeOtherFaqs(e) {
  if (!e.target.open) return
  e.currentTarget.querySelectorAll('details[open]').forEach((d) => { if (d !== e.target) d.open = false })
}

// --- Scroll behaviour: reveals, pinned story, hero parallax ---
const root = ref(null)
const activeStep = ref(0)
let cleanup = () => {}

onMounted(() => {
  const el = root.value
  const q = s => Array.from(el.querySelectorAll(s))
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches

  const reveal = new IntersectionObserver((es) => es.forEach((e) => {
    if (e.isIntersecting) { e.target.setAttribute('data-in', ''); reveal.unobserve(e.target) }
  }), { threshold: 0.12 })
  q('[data-reveal]').forEach(r => reveal.observe(r))

  const stepEls = q('[data-step]')
  const ol = el.querySelector('[data-steps]')
  const sticky = el.querySelector('[data-sticky]')
  const px = q('[data-parallax]')
  const pickStep = () => {
    const wide = window.innerWidth > 900
    if (wide) {
      // Pad the list so step 4 stays level with the panel without excess scroll.
      const last = stepEls[stepEls.length - 1]
      const visible = Math.min(sticky.offsetHeight, window.innerHeight - 96)
      ol.style.paddingBottom = Math.max(0, visible - last.offsetHeight - 80) + 'px'
    } else ol.style.paddingBottom = '0px'
    const line = sticky.getBoundingClientRect().top + Math.min(sticky.offsetHeight, window.innerHeight * 0.6) * 0.5
    let i = 0
    stepEls.forEach((s, j) => { if (s.getBoundingClientRect().top <= line) i = j })
    if (wide && sticky.parentElement.getBoundingClientRect().bottom <= sticky.getBoundingClientRect().bottom + 2) i = stepEls.length - 1
    activeStep.value = i
  }
  let raf = 0
  const onScroll = () => {
    if (raf) return
    raf = requestAnimationFrame(() => {
      raf = 0
      pickStep()
      if (reduced) return
      const y = Math.min(window.scrollY, 900)
      px.forEach((p) => { p.style.transform = `translateY(${-y * (+p.dataset.parallax || 0)}px)` })
    })
  }

  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', onScroll)
  onScroll()
  cleanup = () => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('resize', onScroll)
    reveal.disconnect()
    cancelAnimationFrame(raf)
  }
})
onBeforeUnmount(() => cleanup())

const mono = "font-family:'IBM Plex Mono',monospace"
const label = `margin:0;${mono};font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#8A8474`
const panelStyle = 'grid-area:1/1;aspect-ratio:16/9;background:#fff;border:1px solid #E7E2D6;border-radius:14px;box-shadow:0 30px 60px -30px rgba(27,37,54,.25);overflow:hidden;display:flex;flex-direction:column'
</script>

<template>
  <Head title="ImageSafe by Comordo — Image safeguarding for schools and trusts" />
  <div ref="root" data-root="" style="position:relative;min-height:100vh;background:#F8F6F1;color:#4A5468;overflow-x:clip">
    <div aria-hidden="true" style="position:absolute;inset:0 0 auto 0;height:816px;background:radial-gradient(70% 60% at 50% 0%,rgba(231,201,168,.35),transparent 70%);pointer-events:none"></div>

    <SiteHeader />

    <main id="top">
      <!-- Hero -->
      <section style="position:relative;overflow:hidden">
        <div data-pad="" style="position:relative;max-width:var(--is-wide,1180px);margin:0 auto;padding:64px 32px 0">
          <div style="max-width:min(100%,27em);font-size:clamp(40px,4.2vw,84px);margin:0 auto;display:flex;flex-direction:column;align-items:center;text-align:center;gap:30px">
            <h1 data-h1="" style="margin:0;font-size:clamp(40px,4.2vw,84px);line-height:1.02;letter-spacing:-.025em;font-weight:700;color:#1B2536;text-wrap:pretty">Every photo your school <br>publishes is a safeguarding&nbsp;decision.</h1>
            <p data-hero-p="" style="margin:0;font-size:18px;line-height:1.6;max-width:66.666%;text-wrap:pretty">ImageSafe finds photos of people across your website, prospectuses and newsletters, checking them against permission records and safeguarding guidance. It highlights images that need attention, flags potential AI misuse risks and helps you create safer versions in a click. Your safeguarding lead gets a clear picture of what's online, what needs reviewing and why.</p>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-top:10px">
              <a :href="trialHref" data-btn="" style="display:inline-flex;align-items:center;gap:10px;background:#B4702F;color:#fff;font-weight:500;font-size:16px;padding:14px 24px;border-radius:10px">Start your 7-day free trial <span aria-hidden="true">→</span></a>
              <a href="#preview" data-btn="" style="display:inline-flex;align-items:center;gap:10px;background:#fff;color:#1B2536;font-weight:500;font-size:16px;padding:14px 24px;border-radius:10px;border:1px solid #C9C2B2">Get an instant free preview</a>
            </div>
            <p style="margin:-12px 0 0;font-size:14px;color:#8A8474">No card needed · First scan in minutes · Prefer a conversation? <Link href="/demo" style="font-weight:500">Book a demo</Link></p>
          </div>

          <!-- Hero product frame: review queue -->
          <figure role="img" aria-label="The ImageSafe review queue: a ranked list of images, the selected image with detected faces, and its safeguarding checks" style="position:relative;margin:64px auto 0">
            <div data-parallax="0.04" style="position:relative;background:#fff;border:1px solid #E7E2D6;border-radius:16px;box-shadow:0 2px 4px rgba(27,37,54,.06),0 24px 48px -16px rgba(27,37,54,.35),0 80px 140px -40px rgba(27,37,54,.5);overflow:hidden">
              <div style="display:flex;align-items:center;gap:16px;padding:12px 18px;border-bottom:1px solid #E7E2D6;background:#F8F6F1">
                <span style="display:flex;align-items:center;gap:8px;color:#1B2536;font-weight:700;font-size:14px"><svg width="20" height="20" viewBox="0 0 30 30" aria-hidden="true"><circle cx="15" cy="15" r="15" fill="#1B2536" /><path d="M8.5 15.5l4.2 4.2L22 10.5" fill="none" stroke="#B4702F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg>ImageSafe</span>
                <span data-hide-sm="" style="display:flex;gap:4px;font-size:13px"><span style="background:#F1EDE3;color:#1B2536;padding:5px 10px;border-radius:7px;font-weight:500">Dashboard</span><span style="padding:5px 10px;color:#4A5468">Inventory</span><span style="padding:5px 10px;color:#4A5468">People</span></span>
                <span style="margin-left:auto;display:flex;align-items:center;gap:8px;border:1px solid #E7E2D6;background:#fff;border-radius:8px;padding:6px 12px;font-size:13px;color:#1B2536;font-weight:500;min-width:0;white-space:nowrap">Elm Park Primary <span style="color:#8A8474">⌄</span></span>
              </div>
              <div data-mock-3="" style="display:grid;grid-template-columns:270px minmax(0,1fr) 300px;gap:16px;padding:16px;background:#F8F6F1">
                <div data-mock-queue="" style="background:#fff;border:1px solid #E7E2D6;border-radius:12px;overflow:hidden;display:flex;flex-direction:column">
                  <div style="padding:14px 14px 10px;border-bottom:1px solid #E7E2D6">
                    <p :style="label + ';margin:0 0 8px'">Queue</p>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;font-size:12px"><span style="background:#1B2536;color:#fff;padding:4px 9px;border-radius:999px">All 97</span><span style="border:1px solid #C42E22;color:#C42E22;padding:4px 9px;border-radius:999px">High 34</span><span style="border:1px solid #C0760B;color:#C0760B;padding:4px 9px;border-radius:999px">Med 63</span></div>
                  </div>
                  <ul style="list-style:none;margin:0;padding:8px;display:flex;flex-direction:column;gap:2px;font-size:12px">
                    <li v-for="item in queue" :key="item.name" :style="'display:flex;gap:10px;align-items:center;padding:8px' + (item.selected ? ';border-radius:8px;background:#FBE9E7;border:1px solid #F3CFC9' : '')">
                      <span :style="`width:38px;height:38px;border-radius:6px;background:url('${photo(item.img)}') center/cover;flex-shrink:0`"></span>
                      <span style="min-width:0"><span :style="mono + ';display:block;color:#1B2536;white-space:nowrap;overflow:hidden;text-overflow:ellipsis'">{{ item.name }}</span><span style="color:#8A8474">● {{ item.untagged }} untagged</span></span>
                    </li>
                  </ul>
                  <p style="margin:auto 0 0;padding:10px 14px;border-top:1px solid #E7E2D6;font-size:12px;color:#8A8474">97 to review</p>
                </div>
                <div style="background:#fff;border:1px solid #E7E2D6;border-radius:12px;overflow:hidden;display:flex;flex-direction:column;min-width:0">
                  <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:#C42E22;color:#fff;font-size:13px"><span :style="mono + ';background:rgba(255,255,255,.2);padding:2px 8px;border-radius:999px;font-size:11px'">HIGH</span><span :style="mono + ';opacity:.8;white-space:nowrap'">1 / 97</span><span :style="mono + ';font-weight:500;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap'">prospectus.pdf (page 23)</span></div>
                  <div style="position:relative;margin:14px;aspect-ratio:16/10;border-radius:10px;overflow:hidden;background:#d9cfbf">
                    <img :src="photo(2)" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:50% 45%">
                    <div v-for="f in heroFaces" :key="f.left" :style="`position:absolute;left:${f.left};top:${f.top};width:${f.width};aspect-ratio:3/4;border:2px solid ${faceTone[f.tone] || '#fff'};border-radius:6px;box-shadow:0 0 0 1px rgba(27,37,54,.5)`"><span :style="`position:absolute;left:50%;top:-22px;transform:translateX(-50%);background:${faceTone[f.tone] || '#1B2536'};color:#fff;font-size:10px;padding:2px 7px;border-radius:4px;white-space:nowrap`">{{ f.label }}</span></div>
                    <span style="position:absolute;left:12px;bottom:12px;background:#1B2536;color:#fff;font-size:12px;padding:6px 10px;border-radius:7px">4 faces</span>
                  </div>
                  <div data-decisions="" style="display:flex;gap:8px;padding:0 14px 14px;font-size:12px;flex-wrap:wrap">
                    <span style="flex:1;min-width:140px;display:flex;align-items:center;gap:8px;border:2px solid #B4702F;border-radius:8px;padding:9px 12px;color:#1B2536;font-weight:500;background:#FDF8F2"><span style="width:12px;height:12px;border-radius:50%;border:4px solid #B4702F;flex-shrink:0"></span>Remove from website <span :style="mono + ';margin-left:auto;font-size:10px;color:#B4702F'">REC</span></span>
                    <span style="flex:1;min-width:100px;display:flex;align-items:center;gap:8px;border:1px solid #E7E2D6;border-radius:8px;padding:9px 12px;color:#1B2536"><span style="width:12px;height:12px;border-radius:50%;border:1px solid #C9C2B2;flex-shrink:0"></span>Fix on site</span>
                    <span style="flex:1;min-width:100px;display:flex;align-items:center;gap:8px;border:1px solid #E7E2D6;border-radius:8px;padding:9px 12px;color:#1B2536"><span style="width:12px;height:12px;border-radius:50%;border:1px solid #C9C2B2;flex-shrink:0"></span>Keep as is</span>
                  </div>
                </div>
                <div style="background:#fff;border:1px solid #E7E2D6;border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:8px;font-size:13px">
                  <p :style="label + ';margin:0 0 4px'">Safeguarding checks</p>
                  <span v-for="[kind, text] in heroChecks" :key="text" :style="`display:flex;align-items:center;gap:8px;border-radius:8px;padding:8px 10px;${checkStyle[kind].row}`"><span :style="`width:16px;height:16px;border-radius:50%;font-size:10px;display:grid;place-items:center;${checkStyle[kind].dot}`">{{ checkStyle[kind].icon }}</span>{{ text }}<span :style="mono + ';margin-left:auto;font-size:10px'">{{ checkStyle[kind].tag }}</span></span>
                  <span style="display:flex;align-items:center;gap:8px;padding:6px 10px;color:#178049"><span style="width:16px;height:16px;border-radius:50%;background:#E6F3EB;color:#178049;font-size:10px;display:grid;place-items:center">✓</span>4 checks passed<span style="margin-left:auto;color:#8A8474;font-size:12px">Hide</span></span>
                  <div style="margin-top:auto;border-top:1px solid #E7E2D6;padding-top:10px">
                    <p :style="label + ';margin:0 0 6px'">People · 4</p>
                    <span style="display:flex;align-items:center;gap:8px;color:#4A5468">3 untagged faces<span style="margin-left:auto;background:#F1EDE3;color:#1B2536;padding:3px 8px;border-radius:999px;font-size:11px">Unknown</span></span>
                    <span style="display:flex;align-items:center;gap:8px;color:#1B2536;margin-top:4px">A. Okonkwo · Y5<span style="margin-left:auto;background:#E6F3EB;color:#178049;padding:3px 8px;border-radius:999px;font-size:11px">Approved</span></span>
                  </div>
                </div>
              </div>
            </div>
          </figure>
        </div>
      </section>

      <!-- Risk band -->
      <section style="background:#1B2536;color:#fff;margin-top:-120px;padding-top:200px;padding-bottom:88px">
        <div data-pad="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:0 32px">
          <div data-reveal="" data-half="" style="max-width:66.666%">
            <h2 data-h2="" style="margin:0 0 18px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#fff;text-wrap:pretty">Your website holds years of photos of pupils. Do you know what's still out there?</h2>
            <p style="margin:0;font-size:18px;line-height:1.6;color:#C9C2B2;text-wrap:pretty">Across galleries, news posts and prospectuses, photos remain online long after pupils leave or permissions change. Some include names or high-resolution images that are easy to copy and manipulate using AI. And once an image has been copied, removing it from your website may not be enough.</p>
          </div>
          <div data-cols="" data-risk3="" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1px;align-items:stretch;background:#2A3650;border:1px solid #2A3650;border-radius:12px;overflow:hidden;margin-top:44px">
            <div v-for="r in risks" :key="r.title" data-reveal="" style="background:#1B2536;padding:32px 28px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:10px">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#E7C9A8" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <template v-if="r.icon === 'photos'"><rect x="3" y="7" width="14" height="14" rx="2" /><path d="M7 3h12a2 2 0 0 1 2 2v12" /><circle cx="8" cy="12" r="1.5" /><path d="M3 18l4-4 3 3 3-3 4 4" /></template>
                <template v-else-if="r.icon === 'shield'"><path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6z" /><path d="M9 12l2 2 4-4" /></template>
                <template v-else><path d="M12 4l9 16H3z" /><path d="M12 10v4" /><path d="M12 17.5h.01" /></template>
              </svg>
              <h3 style="margin:6px 0 0;font-size:22px;font-weight:700;color:#fff;letter-spacing:-.01em">{{ r.title }}</h3>
              <p style="margin:6px 0 0;font-size:16px;line-height:1.6;color:#C9C2B2;text-wrap:pretty">{{ r.body }}</p>
            </div>
          </div>
          <p style="margin:32px 0 0;font-size:18px;color:#E7C9A8;max-width:40em;line-height:1.6;font-weight:500">You can't protect what you don't know is there.</p>
        </div>
      </section>

      <!-- How it works: pinned story -->
      <section id="how" data-pad="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:96px 32px 96px">
        <div data-reveal="" data-half="" style="max-width:66.666%">
          <h2 data-h2="" style="margin:0 0 14px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#1B2536;text-wrap:pretty">How a school gets from its first scan to a decision it can stand behind</h2>
          <p style="margin:0;font-size:18px;line-height:1.6">Four steps, all in one place. Each one is shown here as it appears in ImageSafe.</p>
        </div>
        <div data-cols="" style="display:grid;grid-template-columns:minmax(0,5fr) minmax(0,7fr);gap:56px;align-items:start;margin-top:48px">
          <ol data-steps="" style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column">
            <li v-for="(s, i) in steps" :key="s.n" data-step="" :data-active="activeStep === i ? '' : null" :style="s.cta ? 'display:flex;gap:20px;padding:40px 0 0' : 'display:flex;gap:20px;padding:40px 0;min-height:48vh'">
              <div style="display:flex;flex-direction:column;align-items:center;gap:10px;flex-shrink:0">
                <span data-stepnum="" :style="mono + ';min-width:44px;font-size:28px;line-height:1;font-weight:500;color:#C9C2B2;text-align:center;padding-top:6px;transition:color .3s'">{{ s.n }}</span>
                <span v-if="!s.cta" data-stepline="" style="width:1px;flex:1;background:#E7E2D6;transition:background .3s"></span>
              </div>
              <div style="min-width:0">
                <h3 style="margin:4px 0 10px;font-size:24px;line-height:1.2;letter-spacing:-.01em;font-weight:700;color:#1B2536">{{ s.title }}</h3>
                <p v-for="(para, k) in s.body" :key="k" :style="`margin:0 0 ${k < s.body.length - 1 ? 14 : 0}px;font-size:16px;line-height:1.6;text-wrap:pretty`">{{ para }}</p>
                <template v-if="s.cta">
                  <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:22px"><a :href="trialHref" data-btn="" style="display:inline-flex;align-items:center;gap:8px;background:#B4702F;color:#fff;font-weight:500;font-size:15px;padding:12px 20px;border-radius:10px">Start your 7-day free trial</a><a href="#preview" style="display:inline-flex;align-items:center;font-weight:500;font-size:15px;padding:12px 4px">Get an instant free preview →</a></div>
                  <p style="margin:12px 0 0;font-size:14px;color:#8A8474">Prefer a conversation? <Link href="/demo" style="font-weight:500">Book a demo</Link></p>
                </template>
              </div>
            </li>
          </ol>

          <div data-sticky="" aria-hidden="true" style="position:sticky;top:96px;display:grid">
            <!-- Panel 1: inventory -->
            <div data-panel="" :data-active="activeStep === 0 ? '' : null" :style="panelStyle">
              <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #E7E2D6;background:#F8F6F1;font-size:12px;flex-wrap:wrap">
                <span data-hide-md="" style="border:1px solid #E7E2D6;background:#fff;border-radius:7px;padding:6px 10px;color:#8A8474;min-width:160px">⌕ Search filename or page…</span>
                <span style="display:flex;gap:6px"><span style="background:#1B2536;color:#fff;padding:5px 9px;border-radius:999px">All 101</span><span style="border:1px solid #C42E22;color:#C42E22;padding:5px 9px;border-radius:999px">High 35</span><span style="border:1px solid #C0760B;color:#C0760B;padding:5px 9px;border-radius:999px">Medium 63</span><span data-hide-md="" style="border:1px solid #178049;color:#178049;padding:5px 9px;border-radius:999px">Low 3</span></span>
                <span style="margin-left:auto;background:#1B2536;color:#fff;padding:6px 12px;border-radius:7px;font-weight:500">Scan now</span>
              </div>
              <div data-row-inv="" :style="mono + ';padding:10px 16px;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#8A8474;border-bottom:1px solid #E7E2D6'"><span>Image</span><span>Appears on</span><span>People</span><span>Safeguarding checks</span></div>
              <div data-clip="" style="display:flex;flex-direction:column;font-size:12px;flex:1;min-height:0;overflow:hidden">
                <div v-for="row in inventory" :key="row.name" data-row-inv="" style="padding:8px 16px;border-bottom:1px solid #F1EDE3">
                  <span style="display:flex;align-items:center;gap:8px;min-width:0"><span :style="`width:28px;height:28px;border-radius:5px;background:url('${photo(row.img)}') center/cover;flex-shrink:0`"></span><span :style="`${mono};background:${riskColour[row.risk]};color:#fff;font-size:9px;padding:2px 5px;border-radius:4px`">{{ row.risk }}</span><span :style="mono + ';color:#1B2536;overflow:hidden;text-overflow:ellipsis;white-space:nowrap'">{{ row.name }}</span></span>
                  <span :style="row.link ? 'color:#B4702F' : 'color:#8A8474'">{{ row.on }}</span>
                  <span style="display:inline-grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#F1EDE3;color:#1B2536">{{ row.people }}</span>
                  <span style="display:flex;flex-wrap:wrap;gap:4px"><span v-for="[tone, text] in row.checks" :key="text" :style="`${chip[tone]};padding:2px 7px;border-radius:4px;font-size:11px`">{{ text }}</span></span>
                </div>
              </div>
              <p style="margin:auto 0 0;padding:12px 16px;border-top:1px solid #E7E2D6;font-size:12px;color:#8A8474;display:flex;gap:16px"><span>Last scan 20 Oct 2026, 01:00</span><span>177 images · 54 pages · 2 PDFs</span><span style="margin-left:auto">Re-scan weekly</span></p>
            </div>

            <!-- Panel 2: image detail -->
            <div data-panel="" :data-active="activeStep === 1 ? '' : null" :style="panelStyle">
              <div style="display:flex;align-items:center;gap:12px;padding:11px 16px;background:#C42E22;color:#fff;font-size:13px"><span :style="mono + ';background:rgba(255,255,255,.2);padding:2px 8px;border-radius:999px;font-size:11px'">HIGH</span><span :style="mono + ';font-weight:500;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap'">prospectus-2026.pdf (page 19)</span><span data-hide-sm="" style="opacity:.85;font-size:12px;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">linked from /about-us/prospectus/</span><span style="margin-left:auto;border:1px solid rgba(255,255,255,.5);padding:4px 10px;border-radius:6px;font-size:12px;white-space:nowrap">Check again</span></div>
              <div data-cols="" style="display:grid;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr);gap:14px;padding:14px;flex:1;min-height:0">
                <div style="position:relative;border-radius:10px;overflow:hidden;background:#d9cfbf;min-height:200px">
                  <img :src="photo(1)" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:50% 45%">
                  <div v-for="f in detailFaces" :key="f.left" :style="`position:absolute;left:${f.left};top:${f.top};width:${f.width};aspect-ratio:3/4;border:2px solid ${faceTone[f.tone] || '#fff'};border-radius:6px;box-shadow:0 0 0 1px rgba(27,37,54,.5)`"><span :style="`position:absolute;left:50%;top:-22px;transform:translateX(-50%);background:${faceTone[f.tone] || '#1B2536'};color:#fff;font-size:10px;padding:2px 7px;border-radius:4px;white-space:nowrap`">{{ f.label }}</span></div>
                  <span style="position:absolute;left:12px;bottom:12px;background:#1B2536;color:#fff;font-size:12px;padding:6px 10px;border-radius:7px">6 faces</span>
                  <span :style="mono + ';position:absolute;right:12px;bottom:12px;background:#1B2536;color:#fff;font-size:12px;padding:6px 10px;border-radius:7px'">− 100% +</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;min-width:0;min-height:0;overflow:hidden">
                  <p :style="label + ';margin:0 0 2px;display:flex'">Safeguarding checks<span style="margin-left:auto;color:#B4702F;text-transform:none;letter-spacing:0;font-family:Satoshi,sans-serif;font-size:12px">What do these mean?</span></p>
                  <span v-for="[kind, text] in detailChecks" :key="text" :style="`display:flex;align-items:center;gap:8px;border-radius:8px;padding:7px 10px;${checkStyle[kind].row}`"><span :style="`width:16px;height:16px;border-radius:50%;font-size:10px;display:grid;place-items:center;flex-shrink:0;${checkStyle[kind].dot}`">{{ checkStyle[kind].icon }}</span>{{ text }}<span :style="mono + ';margin-left:auto;font-size:10px'">{{ checkStyle[kind].tag }}</span></span>
                  <span style="display:flex;align-items:center;gap:8px;padding:6px 10px;color:#178049;font-size:12px"><span style="width:16px;height:16px;border-radius:50%;background:#E6F3EB;color:#178049;font-size:10px;display:grid;place-items:center;flex-shrink:0">✓</span>5 checks passed<span style="margin-left:auto;color:#8A8474">Show</span></span>
                  <div data-clip="" style="border-top:1px solid #E7E2D6;padding-top:10px;margin-top:4px;flex:1 1 auto;min-height:0;overflow:hidden">
                    <p :style="label + ';margin:0 0 8px;display:flex'">People · 6<span style="margin-left:auto;color:#C42E22;text-transform:none;letter-spacing:0;font-family:Satoshi,sans-serif;font-size:12px">4 untagged</span></p>
                    <span style="display:flex;align-items:center;gap:8px;padding:5px 0;color:#1B2536">H. Begum · Y3<span style="margin-left:auto;background:#C42E22;color:#fff;padding:3px 8px;border-radius:999px;font-size:11px">Refused</span></span>
                    <span style="display:flex;align-items:center;gap:8px;padding:5px 0;color:#1B2536">J. Patel · Y6<span style="margin-left:auto;background:#E6F3EB;color:#178049;padding:3px 8px;border-radius:999px;font-size:11px">Approved</span></span>
                    <span style="display:flex;align-items:center;gap:8px;padding:5px 0;color:#4A5468">⌕ 4 untagged faces<span style="margin-left:auto;background:#F1EDE3;color:#1B2536;padding:3px 8px;border-radius:999px;font-size:11px">Unknown</span></span>
                  </div>
                  <div style="margin-top:auto;border:1px solid #E7E2D6;border-radius:8px;padding:10px 12px;display:flex;flex-direction:column;gap:8px;font-size:12px"><p :style="label.replace('.12em', '.1em')">Safe version</p><div style="display:flex;align-items:center;gap:8px;min-width:0"><span style="width:14px;height:14px;border-radius:3px;background:#B4702F;color:#fff;font-size:10px;display:grid;place-items:center;flex-shrink:0">✓</span><span style="color:#1B2536;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Censor faces without permission</span><span style="margin-left:auto;border:1px solid #C9C2B2;border-radius:6px;padding:5px 9px;color:#1B2536;white-space:nowrap;flex-shrink:0">⤓ Download</span></div></div>
                </div>
              </div>
            </div>

            <!-- Panel 3: People & permissions -->
            <div data-panel="" :data-active="activeStep === 2 ? '' : null" :style="panelStyle">
              <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #E7E2D6;background:#F8F6F1;font-size:12px;flex-wrap:wrap">
                <span data-hide-md="" style="border:1px solid #E7E2D6;background:#fff;border-radius:7px;padding:6px 10px;color:#8A8474;min-width:150px">⌕ Search by name or year…</span>
                <span style="display:flex;gap:6px"><span style="background:#1B2536;color:#fff;padding:5px 9px;border-radius:999px">All 412</span><span style="border:1px solid #C9C2B2;color:#1B2536;padding:5px 9px;border-radius:999px">Pupils 389</span><span data-hide-md="" style="border:1px solid #C9C2B2;color:#1B2536;padding:5px 9px;border-radius:999px">Staff 23</span></span>
                <span style="margin-left:auto;display:flex;gap:6px"><span style="border:1px solid #B4702F;color:#B4702F;padding:6px 10px;border-radius:7px;font-weight:500">Import CSV</span><span style="background:#1B2536;color:#fff;padding:6px 10px;border-radius:7px;font-weight:500">+ Add person</span></span>
              </div>
              <div data-row="" :style="mono + ';padding:10px 16px;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#8A8474;border-bottom:1px solid #E7E2D6'"><span>Person</span><span>Year</span><span>Website</span><span>Social</span><span>Found in</span><span>Status</span></div>
              <div data-clip="" style="font-size:12px;flex:1;min-height:0;overflow:hidden">
                <div v-for="p in people" :key="p.name" data-row="" :style="'padding:10px 16px;border-bottom:1px solid #F1EDE3' + (p.rowStyle ? ';' + p.rowStyle : '')">
                  <span style="display:flex;align-items:center;gap:10px;min-width:0"><span :style="`width:30px;height:30px;border-radius:50%;background:radial-gradient(circle at 50% 40%,${p.avatar});filter:blur(1.5px);flex-shrink:0`"></span><span style="min-width:0"><span style="display:block;color:#1B2536;font-weight:500">{{ p.name }}</span><span style="color:#8A8474;font-size:11px">{{ p.sub }}</span></span></span>
                  <span>{{ p.year }}</span>
                  <span v-html="p.web"></span>
                  <span v-html="p.social"></span>
                  <span :style="p.foundStyle">{{ p.found }}</span>
                  <span><span :style="p.statusStyle + ';padding:3px 8px;border-radius:999px;font-size:11px'">{{ p.status }}</span></span>
                </div>
              </div>
              <div style="margin-top:auto;display:flex;align-items:center;gap:10px;padding:12px 16px;border-top:1px solid #E7E2D6;background:#FBE9E7;font-size:12px;color:#1B2536"><span style="width:8px;height:8px;border-radius:50%;background:#C42E22;flex-shrink:0"></span><span data-wrap=""><strong style="font-weight:700">Alert sent to DSL, 10:42.</strong> A pupil marked never publish was found in 3 images on the website. Emails never include names or photos.</span></div>
            </div>

            <!-- Panel 4: decision + audit -->
            <div data-panel="" :data-active="activeStep === 3 ? '' : null" :style="panelStyle">
              <div style="padding:12px 16px;border-bottom:1px solid #E7E2D6;display:flex;flex-direction:column;gap:8px">
                <div data-decisions="" style="display:flex;gap:8px;font-size:13px;flex-wrap:wrap">
                  <span style="flex:1;min-width:150px;display:flex;align-items:center;gap:8px;border:2px solid #B4702F;border-radius:8px;padding:9px 12px;color:#1B2536;font-weight:500;background:#FDF8F2"><span style="width:12px;height:12px;border-radius:50%;border:4px solid #B4702F;flex-shrink:0"></span>Remove from website<span :style="mono + ';margin-left:auto;font-size:10px;color:#B4702F'">REC</span><span :style="mono + ';font-size:10px;border:1px solid #C9C2B2;padding:1px 5px;border-radius:3px;color:#8A8474'">1</span></span>
                  <span style="flex:1;min-width:110px;display:flex;align-items:center;gap:8px;border:1px solid #E7E2D6;border-radius:8px;padding:9px 12px;color:#1B2536"><span style="width:12px;height:12px;border-radius:50%;border:1px solid #C9C2B2;flex-shrink:0"></span>Fix on site<span :style="mono + ';margin-left:auto;font-size:10px;border:1px solid #C9C2B2;padding:1px 5px;border-radius:3px;color:#8A8474'">2</span></span>
                  <span style="flex:1;min-width:110px;display:flex;align-items:center;gap:8px;border:1px solid #E7E2D6;border-radius:8px;padding:9px 12px;color:#1B2536"><span style="width:12px;height:12px;border-radius:50%;border:1px solid #C9C2B2;flex-shrink:0"></span>Keep as is<span :style="mono + ';margin-left:auto;font-size:10px;border:1px solid #C9C2B2;padding:1px 5px;border-radius:3px;color:#8A8474'">3</span></span>
                </div>
                <div style="display:flex;gap:8px;font-size:14px;flex-wrap:wrap">
                  <span style="flex:2;min-width:200px;background:#1B2536;color:#fff;border-radius:8px;padding:12px;text-align:center;font-weight:500">Record decision &amp; next ↵</span>
                  <span style="flex:1;min-width:110px;border:1px solid #C9C2B2;color:#B4702F;border-radius:8px;padding:12px;text-align:center;font-weight:500">Full detail →</span>
                  <span style="border:1px solid #C9C2B2;color:#1B2536;border-radius:8px;padding:12px 16px;text-align:center">Skip</span>
                </div>
              </div>
              <div style="padding:10px 16px 6px;display:flex;align-items:center"><p :style="label">Audit log</p><span style="margin-left:auto;border:1px solid #E7E2D6;border-radius:999px;padding:3px 9px;font-size:11px;color:#8A8474">Latest 50</span></div>
              <div data-audit="" :style="mono + ';display:grid;grid-template-columns:92px 56px minmax(0,1.1fr) minmax(0,1.6fr);gap:10px;padding:6px 16px;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#8A8474;border-bottom:1px solid #E7E2D6'"><span>When</span><span>Who</span><span>Event</span><span>Details</span></div>
              <div data-clip="" style="font-size:12px;color:#4A5468;flex:1;min-height:0;overflow:hidden">
                <div v-for="[when, who, event, details] in audit" :key="when + event" data-audit="" style="display:grid;grid-template-columns:92px 56px minmax(0,1.1fr) minmax(0,1.6fr);gap:10px;padding:9px 16px;border-bottom:1px solid #F1EDE3"><span>{{ when }}</span><span>{{ who }}</span><span style="color:#1B2536;font-weight:500">{{ event }}</span><span style="min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ details }}</span></div>
              </div>
              <div style="margin-top:auto;display:flex;align-items:center;gap:10px;padding:12px 16px;border-top:1px solid #E7E2D6;background:#F8F6F1;font-size:12px;color:#4A5468;flex-wrap:wrap"><span style="color:#1B2536;font-weight:500">Governors' report</span><span style="border:1px solid #C9C2B2;background:#fff;border-radius:6px;padding:4px 8px">This term ⌄</span><span style="margin-left:auto;background:#1B2536;color:#fff;border-radius:6px;padding:5px 10px;font-weight:500">Export PDF</span></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Proof: testimonials and partner logos (hidden until there are real ones) -->
      <section v-if="quotes.length" id="responsible" style="background:#1B2536;color:#fff;position:relative;overflow:hidden">
        <div aria-hidden="true" style="position:absolute;inset:0;background:radial-gradient(60% 80% at 85% 10%,rgba(180,112,47,.22),transparent 65%);pointer-events:none"></div>
        <div data-pad="" :style="`position:relative;max-width:var(--is-wide,1180px);margin:0 auto;padding:112px 32px ${partnerLogos.length ? 0 : 112}px`">
          <figure data-reveal="" style="margin:0;font-size:clamp(26px,2.4vw,40px)">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:32px;height:1.6em;margin-bottom:.2em">
              <span aria-hidden="true" style="display:block;font-size:4.2em;line-height:.7;height:.55em;overflow:visible;color:#B4702F;font-weight:700;margin-left:-.04em;letter-spacing:-.04em;transform:translateY(-.14em)">“</span>
              <div v-if="quotes.length > 1" style="display:flex;align-items:center;gap:10px;flex-shrink:0;margin-top:-22px">
                <button type="button" data-quote-btn="" aria-label="Previous quote" @click="quoteIndex--"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6" /></svg></button>
                <button type="button" data-quote-btn="" aria-label="Next quote" @click="quoteIndex++"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg></button>
              </div>
            </div>
            <blockquote style="margin:0;max-width:30em;font-size:1em;line-height:1.3;letter-spacing:-.02em;font-weight:500;text-wrap:pretty;min-height:3.9em">{{ quote().text }}</blockquote>
            <figcaption style="margin-top:48px;display:flex;align-items:center;gap:16px;font-size:17px">
              <span aria-hidden="true" style="width:32px;height:1px;background:#B4702F;flex-shrink:0"></span>
              <span><span style="font-weight:700;color:#fff">{{ quote().name }}</span><span style="color:#C9C2B2">, {{ quote().role }}</span></span>
            </figcaption>
          </figure>
          <div v-if="partnerLogos.length" data-reveal="" style="margin-top:96px;padding-top:56px;border-top:1px solid rgba(255,255,255,.12)">
            <p :style="mono + ';margin:0 0 48px;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#C9C2B2'">Working with schools and trusts across the UK</p>
          </div>
        </div>
        <div v-if="partnerLogos.length" data-marquee="" style="position:relative;overflow:hidden;padding:0 0 96px;-webkit-mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent);mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent)">
          <div data-marquee-track="" style="display:flex;align-items:center;gap:120px;width:max-content;padding-left:120px">
            <template v-for="copy in 3" :key="copy">
              <img v-for="logo in partnerLogos" :key="copy + logo.src" :src="logo.src" :alt="copy === 1 ? logo.alt : ''" style="height:64px;width:auto;flex-shrink:0;opacity:.9">
            </template>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section id="faq" style="background:#fff;border-top:1px solid #E7E2D6;border-bottom:1px solid #E7E2D6">
        <div data-pad="" data-cols="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:88px 32px;display:grid;grid-template-columns:minmax(0,2fr) minmax(0,3fr);gap:56px;align-items:start">
          <div data-reveal="">
            <h2 data-h2="" style="margin:0 0 14px;font-size:36px;line-height:1.12;letter-spacing:-.02em;font-weight:700;color:#1B2536;text-wrap:pretty">Questions safeguarding leads ask us</h2>
            <p style="margin:0;font-size:18px;line-height:1.6">Straight answers, including on what ImageSafe cannot do. Anything else, <Link href="/contact" style="font-weight:500">ask us directly</Link>.</p>
          </div>
          <div style="display:flex;flex-direction:column;gap:10px" @toggle.capture="closeOtherFaqs">
            <details v-for="f in faqs" :key="f.q" name="faq" data-reveal="" style="background:#fff;border:1px solid #E7E2D6;border-radius:12px;padding:0 24px">
              <summary style="list-style:none;cursor:pointer;display:flex;align-items:center;gap:16px;padding:20px 0;font-size:17px;font-weight:700;color:#1B2536">{{ f.q }}<span aria-hidden="true" style="margin-left:auto;color:#B4702F;font-size:20px;line-height:1;flex-shrink:0">+</span></summary>
              <p style="margin:0;padding:0 0 22px;font-size:16px;line-height:1.6;color:#4A5468;text-wrap:pretty">{{ f.a }}<template v-if="f.link">{{ ' ' }}<Link v-if="f.link.internal" :href="f.link.href" style="font-weight:500">{{ f.link.label }}</Link><a v-else :href="f.link.href" style="font-weight:500">{{ f.link.label }}</a>{{ f.link.after }}</template></p>
            </details>
          </div>
        </div>
      </section>

      <!-- Free preview -->
      <section id="preview" style="background:#1B2536;color:#fff">
        <div data-pad="" data-cols="" style="max-width:var(--is-wide,1180px);margin:0 auto;padding:96px 32px;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:56px;align-items:center">
          <div data-reveal="">
            <h2 data-h2="" style="margin:0 0 16px;font-size:40px;line-height:1.1;letter-spacing:-.02em;font-weight:700;color:#fff;text-wrap:balance">Curious what it would find on your site? Find out in under a minute.</h2>
            <p style="margin:0 0 22px;font-size:18px;line-height:1.6;color:#C9C2B2;text-wrap:pretty">Enter your school's website address. ImageSafe scans the first public pages straight away, counts the images containing people, and shows the safeguarding checks they would raise. Nobody is identified, your photos are never stored, and no account is needed.</p>
            <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px;font-size:16px;line-height:1.6;color:#E7C9A8">
              <li style="display:flex;gap:10px"><span>—</span>Results on screen in under a minute, from the first pages of your website</li>
              <li style="display:flex;gap:10px"><span>—</span>No faces identified, no permission records needed</li>
              <li style="display:flex;gap:10px"><span>—</span>Reads public pages only; changes nothing on your site</li>
            </ul>
          </div>
          <form data-reveal="" novalidate style="background:#F8F6F1;color:#4A5468;border-radius:14px;padding:32px;display:flex;flex-direction:column;gap:16px;box-shadow:0 30px 60px -30px rgba(0,0,0,.5)" @submit.prevent="requestPreview">
            <p style="margin:0;font-size:20px;font-weight:700;color:#1B2536">Get your instant free preview</p>
            <label :style="mono + ';display:flex;flex-direction:column;gap:8px;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#8A8474'">School website
              <input v-model="previewUrl" data-preview-input="" type="text" inputmode="url" autocomplete="url" placeholder="www.yourschool.org.uk" style="font-family:Satoshi,sans-serif;font-size:16px;letter-spacing:0;text-transform:none;color:#1B2536;background:#fff;border:1px solid #C9C2B2;border-radius:8px;padding:13px 14px;width:100%">
            </label>
            <label style="display:flex;gap:10px;align-items:flex-start;font-size:14px;line-height:1.5;color:#4A5468;cursor:pointer"><input v-model="previewConfirmed" type="checkbox" style="margin-top:3px;accent-color:#B4702F;width:16px;height:16px;flex-shrink:0">I work for this school or trust and have permission to have its website scanned.</label>
            <p v-if="previewError" role="alert" data-preview-error="">Please enter a full school website address, like <b>www.yourschool.org.uk</b>.</p>
            <p v-if="previewNeedsConfirm" role="alert" data-preview-error="">Please confirm you have permission to scan this website.</p>
            <p v-if="previewFailure" role="alert" data-preview-error="">{{ errorCopy[previewFailure] }}</p>
            <button type="submit" data-btn="" :disabled="previewStarting" :style="`font-family:Satoshi,sans-serif;background:#B4702F;color:#fff;border:0;border-radius:10px;padding:15px 20px;font-size:16px;font-weight:500;cursor:${previewStarting ? 'progress' : 'pointer'};margin-top:4px`">{{ previewStarting ? 'Scanning your first pages…' : 'Scan my website now →' }}</button>
            <p style="margin:0;font-size:13px;color:#8A8474;line-height:1.5">Already have an account? <a :href="signInHref" style="font-weight:500">Sign in</a> to run a full scan of your school. Prefer a conversation? <Link href="/demo" style="font-weight:500">Book a demo</Link></p>
          </form>
        </div>
      </section>
    </main>

    <SiteFooter />
  </div>
</template>

<style scoped>
a { color: #B4702F; text-decoration: none; }
a:hover { color: #8F5722; }
[data-reveal] { opacity: 0; transform: translateY(22px); transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1); }
[data-reveal][data-in] { opacity: 1; transform: none; }
[data-panel] { opacity: 0; transform: translateY(18px) scale(.985); transition: opacity .55s cubic-bezier(.2,.7,.2,1), transform .55s cubic-bezier(.2,.7,.2,1); pointer-events: none; }
[data-panel][data-active] { opacity: 1; transform: none; pointer-events: auto; }
[data-step] { opacity: .42; transition: opacity .4s; }
[data-step][data-active] { opacity: 1; }
[data-step][data-active] [data-stepnum] { color: #B4702F !important; }
[data-step][data-active] [data-stepline] { background: #B4702F !important; }
[data-parallax] { will-change: transform; }
@media (min-width: 1440px) { [data-sticky] { top: 88px !important; } [data-panel] { font-size: 13px; } }
@keyframes is-marquee { from { transform: translateX(0); } to { transform: translateX(-33.3333%); } }
[data-marquee-track] { animation: is-marquee 42s linear infinite; }
[data-marquee]:hover [data-marquee-track] { animation-play-state: paused; }
[data-clip] { -webkit-mask-image: linear-gradient(#000 70%, transparent); mask-image: linear-gradient(#000 70%, transparent); }
summary::-webkit-details-marker { display: none; }
summary span[aria-hidden] { transition: transform .2s; }
details[open] summary span[aria-hidden] { transform: rotate(45deg); }
[data-btn] { white-space: nowrap; transition: background-color .2s, color .2s, border-color .2s, transform .25s cubic-bezier(.2,.7,.2,1); }
[data-btn]:hover { transform: translateY(-1px); }
[data-panel] span, [data-panel] p, [data-mock-3] span { white-space: nowrap; }
[data-panel] span[data-wrap], [data-panel] p[data-wrap] { white-space: normal; }
[data-panel] > * { flex-shrink: 0; }
[data-row] { display: grid; grid-template-columns: minmax(0,2fr) 50px 64px 54px 64px minmax(0,1.3fr); gap: 10px; align-items: center; }
[data-row-inv] { display: grid; grid-template-columns: minmax(0,1.3fr) 90px 60px minmax(0,2fr); gap: 12px; align-items: center; }
[data-quote-btn] { width: 44px; height: 44px; border-radius: 50%; border: 1px solid rgba(255,255,255,.25); background: transparent; color: #fff; display: grid; place-items: center; cursor: pointer; padding: 0; transition: border-color .2s, color .2s; }
[data-quote-btn]:hover { border-color: #B4702F; color: #E7C9A8; }
[data-preview-input]:focus { outline: 2px solid #B4702F; outline-offset: 1px; }
[data-preview-error] { margin: 0; padding: 12px 14px; background: #F4E2DC; border-radius: 8px; font-size: 14px; line-height: 1.5; color: #9A3B2A; }
@media (max-width: 1180px) {
  [data-hide-md] { display: none !important; }
  [data-row] { grid-template-columns: minmax(0,2fr) 64px minmax(0,1.3fr); }
  [data-row-inv] { grid-template-columns: minmax(0,1.3fr) minmax(0,2fr); }
  [data-row] > :nth-child(2), [data-row] > :nth-child(3), [data-row] > :nth-child(4) { display: none; }
  [data-row-inv] > :nth-child(2), [data-row-inv] > :nth-child(3) { display: none !important; }
  [data-panel] [data-cols] { grid-template-columns: minmax(0,1.2fr) minmax(0,1fr) !important; }
  [data-panel] { aspect-ratio: 4/3 !important; }
  [data-panel] span, [data-panel] p { white-space: normal; }
  [data-mock-3] { grid-template-columns: minmax(0,1fr) 280px !important; }
  [data-mock-queue] { display: none !important; }
}
@media (max-width: 900px) {
  [data-clip] { -webkit-mask-image: none; mask-image: none; }
  [data-cols] { grid-template-columns: minmax(0,1fr) !important; }
  [data-sticky] { position: static !important; height: auto !important; display: flex !important; flex-direction: column; gap: 20px; }
  [data-panel] { position: static !important; opacity: 1 !important; transform: none !important; pointer-events: auto !important; height: auto !important; aspect-ratio: auto !important; }
  [data-step] { opacity: 1 !important; min-height: 0 !important; padding: 24px 0 !important; }
  [data-hide-sm] { display: none !important; }
  [data-half] { max-width: none !important; }
  [data-hero-p] { max-width: none !important; }
  [data-risk3] { grid-template-columns: minmax(0,1fr) !important; }
  [data-h1] br { display: none; }
  [data-h1] { font-size: 34px !important; }
  [data-decisions] > span { flex-basis: 100% !important; }
  [data-panel] span, [data-panel] p, [data-mock-3] span { white-space: normal; }
  [data-audit] { grid-template-columns: 92px minmax(0,1fr) !important; }
  [data-audit] > :nth-child(2), [data-audit] > :nth-child(4) { display: none; }
  [data-h2] { font-size: 1.9rem !important; }
  [data-pad] { padding-left: 20px !important; padding-right: 20px !important; }
  [data-mock-3] { grid-template-columns: minmax(0,1fr) !important; }
  [data-mock-queue] { display: none !important; }
}
@media (prefers-reduced-motion: reduce) {
  [data-marquee-track] { animation: none; }
  [data-reveal], [data-panel] { transition: none; transform: none; }
}
</style>
