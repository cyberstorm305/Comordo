<!--
  ChecksExplainer.vue — "What the safeguarding checks mean" (the real app's
  Review/ChecksExplainer). The wording comes from ImageSafe (GET /api/v1/checks,
  the file the app's explainer is built from), so the two can't drift; it's
  fetched the first time this opens. Checks the preview can't run are marked
  "In your trial". highlight opens it at one check. Esc and the
  backdrop close it; focus moves in, stays in, and returns to whatever opened
  it.
-->
<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { identityBase } from '@/identity.js'
import { imagesafeBase } from '@/imagesafe.js'

const props = defineProps({
  open: { type: Boolean, default: false },
  highlight: { type: String, default: null },
  locked: { type: Array, default: () => [] }, // keys of the checks a preview doesn't run
  returnTo: { type: null, default: null }, // the element that opened it
})
const emit = defineEmits(['close'])

const registerHref = `${identityBase}/start`

// { summary: [lines], checks: [{ key, name, looks, why, fix }] }, fetched once per page.
// {high} and {medium} in the summary are shown as the coloured risk words.
let request = null
function fetchExplainer() {
  request ??= fetch(`${imagesafeBase}/api/v1/checks`, { headers: { Accept: 'application/json' } })
    .then((res) => { if (!res.ok) throw new Error(String(res.status)); return res.json() })
    .catch((e) => { request = null; throw e }) // a failure can be tried again
  return request
}

const explainer = ref(null)
const failed = ref(false)
async function load() {
  failed.value = false
  try {
    explainer.value = await fetchExplainer()
    await nextTick()
    if (props.highlight) document.getElementById(`isp-check-${props.highlight}`)?.scrollIntoView({ block: 'center' })
  } catch {
    failed.value = true
  }
}

const RISK_WORD = { '{high}': { text: 'High', class: 'text-bad' }, '{medium}': { text: 'Medium', class: 'text-copper-dark' } }
const summary = computed(() => (explainer.value?.summary || []).map((line) => line.split(/(\{high\}|\{medium\})/).filter(Boolean).map((part) => RISK_WORD[part] || { text: part })))
const CHECKS = computed(() => explainer.value?.checks || [])

const panel = ref(null)
const closeButton = ref(null)
let opener = null

watch(() => props.open, async (o, was) => {
  if (o && !was) {
    opener = props.returnTo || document.activeElement
    document.addEventListener('keydown', onKey)
    await nextTick()
    closeButton.value?.focus()
    load()
  } else if (!o && was) {
    document.removeEventListener('keydown', onKey)
    opener?.focus?.()
    opener = null
  }
})
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))

function onKey(e) {
  // The page's shortcuts (arrows, 1–3, Enter) listen on window: keep them off while this is open.
  e.stopPropagation()
  if (e.key === 'Escape') { e.preventDefault(); emit('close'); return }
  if (e.key !== 'Tab' || !panel.value) return
  const els = [...panel.value.querySelectorAll('a[href], button')]
  if (!els.length) return
  const first = els[0]
  const last = els[els.length - 1]
  if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus() }
  else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus() }
  else if (!panel.value.contains(document.activeElement)) { e.preventDefault(); first.focus() }
}
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" leave-active-class="ease-in duration-150" leave-to-class="opacity-0">
      <div v-if="open" class="isp fixed inset-0 z-50 flex items-center justify-center !bg-transparent px-4 py-6" data-testid="checks-explainer">
        <div class="absolute inset-0 bg-navy/[.42]" @click="emit('close')"></div>
        <div ref="panel" role="dialog" aria-modal="true" aria-labelledby="isp-explainer-title"
             class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-[14px] bg-white shadow-isp-modal">
          <div class="flex flex-none items-center justify-between gap-3 border-b border-shell px-6 py-4">
            <h2 id="isp-explainer-title" class="m-0 text-[15px] font-bold leading-[22px] text-navy">What the safeguarding checks mean</h2>
            <button ref="closeButton" type="button" aria-label="Close" class="cursor-pointer border-0 bg-transparent p-0 text-[20px] leading-none text-stone hover:text-navy" @click="emit('close')">×</button>
          </div>

          <div class="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto px-6 py-5">
            <p v-if="failed" class="m-0 flex flex-wrap items-center gap-2 rounded-[10px] border border-line bg-paper px-4 py-3 text-[13px] text-slate" data-testid="explainer-failed">
              The explanations couldn't be loaded.
              <button type="button" class="cursor-pointer border-0 bg-transparent p-0 font-semibold text-copper-dark hover:underline" @click="load">Try again</button>
            </p>
            <p v-else-if="!explainer" class="m-0 px-1 text-[13px] text-stone" data-testid="explainer-loading">Loading…</p>

            <div v-if="explainer" class="rounded-[10px] border border-line bg-paper px-4 py-3 text-[13px] leading-normal text-slate">
              <div class="mb-1 font-semibold text-navy">How they add up</div>
              <template v-for="(line, i) in summary" :key="i">
                <template v-for="(part, j) in line" :key="j"><b v-if="part.class" :class="part.class">{{ part.text }}</b><template v-else>{{ part.text }}</template></template>{{ ' ' }}
              </template>
            </div>

            <div v-for="check in CHECKS" :id="`isp-check-${check.key}`" :key="check.key"
                 class="rounded-[10px] border px-4 py-3"
                 :class="highlight === check.key ? 'border-copper bg-copper-wash' : 'border-shell'"
                 data-testid="explainer-check">
              <div class="mb-1.5 flex items-center gap-2 text-[14px] font-semibold leading-5 text-navy">
                {{ check.name }}
                <span v-if="locked.includes(check.key)" class="rounded-[7px] bg-shell px-2 py-0.5 text-[11px] font-semibold leading-4 text-stone">In your trial</span>
              </div>
              <dl class="m-0 grid gap-x-4 gap-y-1 text-[13px] leading-normal sm:grid-cols-[110px_1fr]">
                <dt class="font-semibold text-stone">Looks for</dt>
                <dd class="m-0 text-slate">{{ check.looks }}</dd>
                <dt class="font-semibold text-stone">Why it matters</dt>
                <dd class="m-0 text-slate">{{ check.why }}</dd>
                <dt class="font-semibold text-stone">How to fix</dt>
                <dd class="m-0 text-slate">{{ check.fix }}</dd>
              </dl>
            </div>
          </div>

          <div class="flex flex-none flex-wrap items-center justify-between gap-3 border-t border-shell px-6 py-4">
            <span class="text-[12px] leading-[18px] text-stone">Checks marked "In your trial" run on every photo once you start.</span>
            <a :href="registerHref" class="inline-flex items-center justify-center rounded-[7px] bg-navy px-4 py-[9px] text-[13px] font-semibold text-white no-underline hover:bg-navy-light">Start your 7-day free trial</a>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
