<!--
  PreviewPage.vue — /preview/{id}. A live website preview shown as a copy of
  the ImageSafe dashboard, built from the design handoff in Designs/Imagesafe
  and checked against the real app. Polls ImageSafe's preview API.
-->
<script setup>
import { computed, onBeforeUnmount, onMounted, provide, ref, watch as vueWatch } from 'vue'
import { Head } from '@inertiajs/vue3'
import { identityBase } from '@/identity.js'
import { usePreview } from '@/usePreview.js'
import AppShell from '../Components/ImageSafePreview/AppShell.vue'
import QueueCard from '../Components/ImageSafePreview/QueueCard.vue'
import ImageFrame from '../Components/ImageSafePreview/ImageFrame.vue'
import ChecksPanel from '../Components/ImageSafePreview/ChecksPanel.vue'
import DecisionPanel from '../Components/ImageSafePreview/DecisionPanel.vue'
import SafeVersion from '../Components/ImageSafePreview/SafeVersion.vue'
import PeoplePanel from '../Components/ImageSafePreview/PeoplePanel.vue'
import TrialPrompt from '../Components/ImageSafePreview/TrialPrompt.vue'

const props = defineProps({ id: { type: String, required: true } })

const registerHref = `${identityBase}/register`
const { state, progress, result, error, watch: watchPreview } = usePreview()

// Trial prompt, opened by every locked tab and action.
const trialFeature = ref(null)
const trialReturn = ref(null)
function openTrial(feature, el = null) {
  trialReturn.value = el
  trialFeature.value = feature
}
provide('openTrial', openTrial)

// The address the visitor typed is kept by the homepage form for this tab;
// otherwise fall back to the site the first photo came from.
const savedHost = ref('')
const hostOf = (u) => { try { return new URL(u).host.replace(/^www\./i, '') } catch { return '' } }
const found = computed(() => result.value.found || [])
const host = computed(() => savedHost.value || (found.value[0] ? hostOf(found.value[0].page_url) : ''))

function fileName(u) {
  let seg = ''
  try { seg = new URL(u).pathname.split('/').filter(Boolean).pop() || '' } catch { seg = String(u || '') }
  try { seg = decodeURIComponent(seg) } catch { /* keep it encoded */ }
  return seg || hostOf(u) || 'image'
}
const withoutScheme = (u) => String(u || '').replace(/^[a-z][a-z0-9+.-]*:\/\//i, '')
// The page's last path segment as words, or its host for the home page (the app's pageLabel).
function pageLabel(u) {
  const [h, ...path] = withoutScheme(u).replace(/^www\./i, '').split(/[?#]/)[0].split('/').filter(Boolean)
  let last = path.at(-1) || ''
  try { last = decodeURIComponent(last) } catch { /* keep it as written */ }
  const words = last.replace(/\.[a-z0-9]+$/i, '').replace(/[-_+]+/g, ' ').trim()
  return words ? words.charAt(0).toUpperCase() + words.slice(1) : (h || 'Page')
}
function pagePath(u) {
  try { const p = new URL(u).pathname; return p === '/' ? 'the home page' : p } catch { return 'your website' }
}

const items = computed(() => found.value.map((f, i) => ({
  key: `${i}:${f.image_url}`,
  name: fileName(f.image_url),
  thumb: f.image_url,
  faces: (f.faces || []).length,
  width: f.width,
  height: f.height,
})))

const selected = ref(0)
const decision = ref(null) // picked as in the app; recording it opens the trial prompt
vueWatch(selected, () => { decision.value = null })
const sel = computed(() => found.value[selected.value] || null)
const selName = computed(() => items.value[selected.value]?.name || '')
const selFaces = computed(() => (sel.value?.faces || []).length)
const selAlt = computed(() => sel.value ? `Photo from ${pagePath(sel.value.page_url)} with ${selFaces.value} ${selFaces.value === 1 ? 'person' : 'people'}` : '')

function step(dir) {
  if (!items.value.length) return
  selected.value = Math.min(items.value.length - 1, Math.max(0, selected.value + dir))
}

const errorCopy = {
  unreachable: "We couldn't reach that website.",
  busy: 'Previews are busy right now. Try again shortly, or book a demo.',
  slow: 'That website is slow to respond. Try again, or start your trial for a full scan.',
  expired: 'This preview has expired.',
}

function onKey(e) {
  if (trialFeature.value || state.value === 'error') return
  if (e.target && /INPUT|TEXTAREA|SELECT/.test(e.target.tagName)) return
  if (e.metaKey || e.ctrlKey || e.altKey) return
  if (e.key === 'ArrowDown' || e.key === 'j') { if (items.value.length) { e.preventDefault(); step(1) } }
  else if (e.key === 'ArrowUp' || e.key === 'k') { if (items.value.length) { e.preventDefault(); step(-1) } }
  else if (sel.value && ['1', '2', '3'].includes(e.key)) {
    e.preventDefault()
    decision.value = ['remove', 'fix', 'keep'][Number(e.key) - 1]
  } else if (sel.value && e.key === 'Enter' && e.target === document.body) {
    e.preventDefault()
    openTrial('Recording decisions')
  }
}

onMounted(() => {
  try { savedHost.value = sessionStorage.getItem(`isp-preview-host:${props.id}`) || '' } catch { /* storage blocked */ }
  window.addEventListener('keydown', onKey)
  watchPreview(props.id)
})
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
vueWatch(() => items.value.length, (n) => { if (selected.value >= n) selected.value = Math.max(0, n - 1) })
</script>

<template>
  <Head title="Your ImageSafe preview">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://api.fontshare.com">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  </Head>

  <AppShell :host="host">
    <!-- ERROR STATES -->
    <div v-if="state === 'error'" class="mx-auto my-10 flex max-w-[560px] flex-col items-center rounded-[14px] border border-sand bg-white px-8 py-8 text-center" role="alert" data-testid="preview-error">
      <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full text-xl font-bold"
           :class="error === 'expired' ? 'bg-hair text-stone' : 'bg-bad-pale text-bad'" aria-hidden="true">{{ error === 'expired' ? '⏱' : '!' }}</div>
      <h1 class="m-0 mb-1.5 text-base font-bold" data-testid="error-message">{{ errorCopy[error] || errorCopy.busy }}</h1>
      <p v-if="error === 'expired'" class="m-0 text-[13px] leading-relaxed text-stone">Previews last 24 hours. Run a new one from the homepage.</p>
      <div class="mt-5 flex flex-wrap justify-center gap-2.5">
        <a href="/#preview" class="inline-flex items-center rounded-[7px] border border-line bg-white px-4 py-[9px] text-[13px] font-semibold text-slate no-underline hover:bg-tint-hover" data-testid="error-home-link">{{ error === 'expired' ? 'Run a new preview' : 'Try again' }}</a>
        <a v-if="error === 'busy'" href="/demo" class="inline-flex items-center rounded-[7px] border border-line bg-white px-4 py-[9px] text-[13px] font-semibold text-copper no-underline hover:bg-tint-hover">Book a demo</a>
        <a :href="registerHref" class="inline-flex items-center rounded-[7px] bg-navy px-4 py-[9px] text-[13px] font-semibold text-white no-underline hover:bg-navy-light">Start your 14-day free trial</a>
      </div>
    </div>

    <template v-else>
      <!-- The nav shows the section from lg up; below that the title stands in for it, as in the app. -->
      <div class="mb-[22px] flex min-w-0 items-center gap-2 lg:hidden">
        <h1 class="m-0 text-[21px] font-bold tracking-[-0.4px] text-navy">Dashboard</h1>
        <span class="flex h-[18px] w-[18px] flex-none items-center justify-center rounded-full border-[1.5px] border-edge font-serif text-[11px] font-bold italic leading-none text-stone" aria-hidden="true">i</span>
      </div>

      <div class="grid items-stretch gap-4 lg:grid-cols-[minmax(290px,1fr)_minmax(0,3fr)]">
        <QueueCard :items="items" :selected="selected" :state="state" :progress="progress" @select="i => selected = i" />

        <!-- focus panel: fills the screen height like the queue, so the photo takes the depth the controls leave.
             145px, not the app's 113px: the preview strip above the header is 32px. -->
        <div v-if="sel" class="flex min-w-0 flex-col overflow-hidden rounded-[14px] border border-sand bg-white lg:h-[calc(100vh-145px)]" data-testid="focus">
          <div class="flex items-center justify-between gap-3 bg-navy px-5 py-2.5">
            <div class="flex min-w-0 items-center gap-3">
              <span class="flex-none whitespace-nowrap text-[12px] leading-[18px] tabular-nums text-white/75" data-testid="item-position">{{ selected + 1 }} / {{ items.length }}</span>
              <button type="button" class="min-w-0 cursor-pointer truncate border-0 bg-transparent p-0 text-left font-mono text-[14px] font-bold leading-5 text-white hover:underline" title="Open full detail" data-testid="item-name"
                      @click="openTrial('Image detail', $event.currentTarget)">{{ selName }}</button>
              <a :href="sel.page_url" target="_blank" rel="noopener noreferrer"
                 class="hidden min-w-0 flex-none items-center gap-1.5 rounded-[7px] bg-white/15 px-2 py-0.5 text-[12px] leading-[18px] text-white/90 no-underline hover:bg-white/25 sm:flex"
                 :title="withoutScheme(sel.page_url)" data-testid="item-page">
                <svg class="h-3.5 w-3.5 flex-none" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 3h6v6M17 3l-8 8M8 5H4v11h11v-4" /></svg>
                <span class="truncate">{{ pageLabel(sel.page_url) }}</span>
              </a>
            </div>
            <div class="flex flex-none gap-2">
              <button type="button" aria-label="Previous image" class="flex h-[34px] w-[34px] cursor-pointer items-center justify-center rounded-[7px] border border-white/50 bg-transparent text-white hover:bg-white/15 disabled:cursor-default disabled:opacity-40" :disabled="selected === 0" data-testid="prev" @click="step(-1)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 16V4M5 9l5-5 5 5" /></svg>
              </button>
              <button type="button" aria-label="Next image" class="flex h-[34px] w-[34px] cursor-pointer items-center justify-center rounded-[7px] border border-white/50 bg-transparent text-white hover:bg-white/15 disabled:cursor-default disabled:opacity-40" :disabled="selected >= items.length - 1" data-testid="next" @click="step(1)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 4v12M5 11l5 5 5-5" /></svg>
              </button>
            </div>
          </div>
          <div class="grid min-h-0 flex-1 items-stretch lg:grid-cols-[minmax(420px,1.9fr)_minmax(320px,1.1fr)] lg:grid-rows-[minmax(0,1fr)]">
            <div class="flex min-w-0 flex-col border-shell lg:min-h-0 lg:overflow-y-auto lg:border-r">
              <div class="flex flex-1 flex-col gap-3 px-4 py-4">
                <ImageFrame :photo="sel" :alt="selAlt" @tag="el => openTrial('People & Permissions', el)" @zoom="el => openTrial('Photo zoom', el)" />
                <SafeVersion @trial="el => openTrial('Safe versions', el)" />
                <DecisionPanel v-model:decision="decision" @trial="el => openTrial('Recording decisions', el)" @detail="el => openTrial('Image detail', el)" @skip="step(1)" />
              </div>
            </div>
            <div class="flex min-h-0 flex-col border-t border-shell lg:border-t-0">
              <ChecksPanel :key="selected" :people="selFaces" />
              <PeoplePanel class="flex-1" :count="selFaces"
                           @tag="el => openTrial('People & Permissions', el)"
                           @zoom="el => openTrial('Photo zoom', el)"
                           @dismiss="el => openTrial('Dismissing faces', el)" />
              <div class="border-t border-shell px-4 py-3 text-[11px] leading-[1.55] text-stone">Your trial matches every face against your permission records and runs every check. AI-assisted — your judgement decides.</div>
            </div>
          </div>
        </div>

        <!-- no photos with people -->
        <div v-else-if="state === 'done'" class="flex flex-col items-start gap-5 rounded-[14px] border border-sand bg-white px-[26px] py-6 sm:flex-row sm:items-center" data-testid="no-photos">
          <div class="flex h-[52px] w-[52px] flex-none items-center justify-center rounded-full bg-good-pale text-2xl text-good" aria-hidden="true">✓</div>
          <div class="min-w-0 flex-1">
            <div class="mb-1 text-lg font-bold tracking-[-0.3px]">No photos with people in the first pages we checked</div>
            <div class="text-[13px] text-stone">{{ progress.images }} images checked on {{ progress.pages }} pages. Your trial checks every page.</div>
          </div>
          <a :href="registerHref" class="flex-none whitespace-nowrap rounded-[7px] border border-copper-light bg-copper-wash px-[15px] py-[9px] text-[12.5px] font-semibold text-navy no-underline hover:bg-[#EEE3D0]">Start your 14-day free trial</a>
        </div>

        <!-- waiting for the first photo -->
        <div v-else class="flex min-w-0 flex-col overflow-hidden rounded-[14px] border border-sand bg-white" data-testid="waiting">
          <div class="flex items-center gap-2.5 bg-navy px-5 py-3 text-xs text-white/75">
            <span class="h-3 w-3 flex-none animate-spin rounded-full border-2 border-white/30 border-t-white" aria-hidden="true"></span>
            {{ state === 'queued' ? 'Waiting to start…' : 'Looking for photos with people…' }}
          </div>
          <div class="px-4 py-[18px] sm:px-5">
            <div class="img-ph isp-pulse flex aspect-video w-full items-center justify-center rounded-[10px] border border-[#CFC8B8]">
              <span class="rounded-full bg-white/80 px-3 py-1.5 text-[12px] font-medium text-slate">The first photo with people will appear here.</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AppShell>

  <TrialPrompt :feature="trialFeature" :return-to="trialReturn" @close="trialFeature = null" />
</template>
