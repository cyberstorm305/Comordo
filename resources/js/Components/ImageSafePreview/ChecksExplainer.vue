<!--
  ChecksExplainer.vue — "What the safeguarding checks mean" (the real app's
  Review/ChecksExplainer, same wording). Checks the preview can't run are
  marked "In your trial". highlight opens it at one check. Esc and the
  backdrop close it; focus moves in, stays in, and returns to whatever opened
  it.
-->
<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { identityBase } from '@/identity.js'
import { LOCKED_CHECKS } from './risk.js'

const props = defineProps({
  open: { type: Boolean, default: false },
  highlight: { type: String, default: null },
  returnTo: { type: null, default: null }, // the element that opened it
})
const emit = defineEmits(['close'])

const registerHref = `${identityBase}/register`
const lockedKeys = LOCKED_CHECKS.map((c) => c.key)

// Keys match the app's SafeguardingReviewService (and "permission" from its checkRows).
const CHECKS = [
  {
    key: 'permission',
    name: 'Permission to use',
    looks: 'Whether everyone tagged in the photo has permission for where it’s published: the website or social media.',
    why: 'A family’s choice not to have their child online must be respected. A refusal on either channel is treated as a refusal for both, and overrides every other check.',
    fix: 'Remove the photo, or download the safe version with faces without permission blurred. Tag any untagged faces so their permission can be checked.',
  },
  {
    key: 'protected_person',
    name: 'Never-publish pupil',
    looks: 'A pupil marked "Never publish" (for example a looked-after child or one under a court order) tagged in the photo.',
    why: 'Being found online could put these children at risk. This is always urgent.',
    fix: 'Take the photo off the website and social media straight away. The alert closes once ImageSafe confirms it’s gone.',
  },
  {
    key: 'state_of_dress',
    name: 'Appropriate clothing',
    looks: 'What people in the photo are wearing, using an image model. Nudity or swimwear (a bare chest) is flagged; a bare midriff on its own, often PE kit, is a caution for you to look at.',
    why: 'Guidance is never to publish children in swimwear or undressed: these images are the most likely to be misused.',
    fix: 'Don’t publish a flagged photo. Use one where everyone is fully dressed. For a caution, check whether it’s swimwear before publishing.',
  },
  {
    key: 'named_person',
    name: 'Name in or near the photo',
    looks: 'A pupil’s name in the caption, alt text, text near the photo or the file name, or written in the picture itself (a sign, certificate or name badge).',
    why: 'A face with a name is what lets a stranger, or a face-search engine, identify and find a child.',
    fix: 'Remove the name, or use first names only and never with a photo of that child alone. Rename files before uploading.',
  },
  {
    key: 'face_visibility',
    name: 'Recognisable faces',
    looks: 'Faces clear enough to identify: front-on, a reasonable size, and detected with confidence.',
    why: 'A clearly visible face can be recognised. One child alone is the easiest to identify, so it’s flagged; clear faces in a group are a caution.',
    fix: 'Prefer shots where children are side-on, further away or busy with an activity. Blur faces without permission in the safe version.',
  },
  {
    key: 'close_up_vs_wide_angle',
    name: 'Close-up of a child',
    looks: 'How much of the picture the largest face fills. Up to 2% is a wide shot, up to 3.5% is a caution, more is a close-up.',
    why: 'The bigger a face is in the frame, the easier one child is to single out or crop into a portrait.',
    fix: 'Use a wider shot, or crop so the face is smaller in the frame.',
  },
  {
    key: 'group_activity_composition',
    name: 'Single child in shot',
    looks: 'Exactly one person, with their face clearly visible. It counts faces only: it can’t see what’s happening in the picture.',
    why: 'Group and activity photos are safer than photos of one child on their own, who is easier to identify and single out.',
    fix: 'Use a group or activity shot instead. A lone child whose face can’t be seen (turned away, small, blurred) isn’t flagged.',
  },
  {
    key: 'image_resolution',
    name: 'High-resolution file',
    looks: 'The size of the file: over 2000 pixels on the long edge is flagged, 1400 to 2000 is a caution.',
    why: 'A large file can be zoomed into or cropped to a clear portrait of one child.',
    fix: 'Resize to about 1200 pixels before publishing. The safe version does this for you.',
  },
  {
    key: 'full_size_original',
    name: 'Full-size original online',
    looks: 'Whether the uncompressed original of a resized photo can still be downloaded from the website.',
    why: 'Websites such as WordPress often keep the original alongside the smaller copy, so anyone can get the high-resolution version.',
    fix: 'Delete the original from the website’s media library, keeping only the resized copy.',
  },
  {
    key: 'metadata_present',
    name: 'Location or camera data',
    looks: 'Data saved inside the file (EXIF), such as GPS location, the date and the camera owner.',
    why: 'Location data can show where a photo was taken, which can lead to a child’s home or school.',
    fix: 'Strip the data before publishing. The safe version removes it.',
  },
  {
    key: 'ai_provenance',
    name: 'Made or edited with AI',
    looks: 'Labels inside the file saying AI made or changed it: Content Credentials (C2PA), which tools such as Adobe Firefly and ChatGPT add, the IPTC "digital source type", or an AI tool’s name in the file’s details. Made with AI is flagged; edited with AI (such as Generative Fill) is a caution. Shown as "No AI labels found" when there are none.',
    why: 'A picture made with AI may not show a real child, and an edited one may change a real child’s photo in a way their family never agreed to. Either can mislead parents and the school community.',
    fix: 'Check whether it shows a real pupil and what was changed. Use the original photo instead, or remove it. No label doesn’t prove a photo is genuine: labels are lost when a file is re-saved, and watermarks hidden in the pixels (such as Google’s SynthID) aren’t checked.',
  },
]

const panel = ref(null)
const closeButton = ref(null)
let opener = null

watch(() => props.open, async (o, was) => {
  if (o && !was) {
    opener = props.returnTo || document.activeElement
    document.addEventListener('keydown', onKey)
    await nextTick()
    closeButton.value?.focus()
    if (props.highlight) document.getElementById(`isp-check-${props.highlight}`)?.scrollIntoView({ block: 'center' })
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
            <div class="rounded-[10px] border border-line bg-paper px-4 py-3 text-[13px] leading-normal text-slate">
              <div class="mb-1 font-semibold text-navy">How they add up</div>
              Any one flag (✕) makes the photo <b class="text-bad">High</b>. Four or more cautions (!) together also make it High.
              One or more cautions, or anyone without known permission, makes it <b class="text-copper-dark">Medium</b>.
              A refusal of permission overrides everything else. Checks about children are relaxed for photos of staff only, once a person has confirmed who they are.
              The checks are AI-assisted: your judgement decides.
            </div>

            <div v-for="check in CHECKS" :id="`isp-check-${check.key}`" :key="check.key"
                 class="rounded-[10px] border px-4 py-3"
                 :class="highlight === check.key ? 'border-copper bg-copper-wash' : 'border-shell'"
                 data-testid="explainer-check">
              <div class="mb-1.5 flex items-center gap-2 text-[14px] font-semibold leading-5 text-navy">
                {{ check.name }}
                <span v-if="lockedKeys.includes(check.key)" class="rounded-[7px] bg-shell px-2 py-0.5 text-[11px] font-semibold leading-4 text-stone">In your trial</span>
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
            <a :href="registerHref" class="inline-flex items-center justify-center rounded-[7px] bg-navy px-4 py-[9px] text-[13px] font-semibold text-white no-underline hover:bg-navy-light">Start your 14-day free trial</a>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
