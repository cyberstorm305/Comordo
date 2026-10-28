<!--
  TrialPrompt.vue — "{Feature} is part of your trial", in the app's modal style
  (real app Modal.vue + TagFaceModal.vue header). Esc and the backdrop close
  it; focus moves in, stays in, and returns to whatever opened it.
-->
<script setup>
import { computed, nextTick, ref, watch, onBeforeUnmount } from 'vue'
import { identityBase } from '@/identity.js'

const props = defineProps({
  feature: { type: String, default: null },
  returnTo: { type: null, default: null }, // the element that opened it
})
const emit = defineEmits(['close'])

const registerHref = `${identityBase}/register`
const FEATURES = {
  'People & Permissions': 'Add pupils and staff with their photo permissions, and ImageSafe matches them to the faces it finds.',
  'Recording decisions': 'Record what you decide for each photo, and why, so every image has an audit trail.',
  'Image detail': 'Every check, page and decision for a photo on one page.',
  'Safe versions': 'Download a copy that’s safe to re-publish, with faces without permission censored and location and camera data removed.',
  'Photo zoom': 'Zoom into any photo, or jump straight to a face, to check it up close.',
  'Dismissing faces': 'Dismiss a face that isn’t a real person, such as a poster, so it stops counting.',
  Inventory: 'Every image from your website, social media and uploads in one searchable list, with its status.',
  Notifications: 'Hear when a scan finds new photos of people, or when permissions are due to renew.',
  'Multiple schools': 'Check every school in your trust from one account, and switch between them here.',
  'Settings & users': 'Invite colleagues, set their roles and choose how often each website is scanned.',
}
const blurb = computed(() => FEATURES[props.feature] || 'It’s included in your 14-day free trial.')

const panel = ref(null)
const primary = ref(null)
let opener = null

watch(() => props.feature, async (f, old) => {
  if (f && !old) {
    opener = props.returnTo || document.activeElement
    document.addEventListener('keydown', onKey)
    await nextTick()
    primary.value?.focus()
  } else if (!f && old) {
    document.removeEventListener('keydown', onKey)
    opener?.focus?.()
    opener = null
  }
})
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))

function onKey(e) {
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
      <div v-if="feature" class="isp fixed inset-0 z-50 flex items-center justify-center !bg-transparent px-4 py-6" data-testid="trial-prompt">
        <div class="absolute inset-0 bg-navy/[.42]" data-testid="trial-backdrop" @click="emit('close')"></div>
        <div ref="panel" role="dialog" aria-modal="true" aria-labelledby="isp-trial-title" aria-describedby="isp-trial-desc"
             class="relative w-full max-w-md overflow-hidden rounded-xl bg-white shadow-isp-modal">
          <div class="flex items-start justify-between gap-3 border-b border-hair px-5 py-[18px]">
            <div>
              <div id="isp-trial-title" class="text-[15px] font-bold text-navy">{{ feature }} is part of your trial</div>
              <div id="isp-trial-desc" class="mt-1 text-[12.5px] leading-[1.5] text-stone">{{ blurb }}</div>
            </div>
            <button type="button" aria-label="Close" class="h-[30px] w-[30px] flex-none cursor-pointer rounded-[7px] border-0 bg-hair text-sm text-slate hover:bg-sand" @click="emit('close')">✕</button>
          </div>
          <div class="flex flex-wrap items-center justify-end gap-2.5 px-5 py-4">
            <button type="button" class="cursor-pointer rounded-[7px] border border-line bg-white px-4 py-[9px] text-[13px] font-semibold text-slate hover:bg-tint-hover" @click="emit('close')">Not now</button>
            <a ref="primary" :href="registerHref" class="inline-flex items-center justify-center rounded-[7px] bg-navy px-4 py-[9px] text-[13px] font-semibold text-white no-underline hover:bg-navy-light" data-testid="trial-prompt-link">Start your 14-day free trial</a>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
