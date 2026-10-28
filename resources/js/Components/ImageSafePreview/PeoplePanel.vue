<!--
  PeoplePanel.vue — the faces in one image (the real app's Review/PeopleList).
  Clicking a name opens the demo tagging, the magnifying glass zooms the photo
  to the face, and the ✕ opens the trial prompt.
-->
<script setup>
import InfoTip from './InfoTip.vue'

import { computed } from 'vue'

const props = defineProps({
  faces: { type: Array, default: () => [] }, // per face: { person, pill: { label, bg, fg } }
})
const emit = defineEmits(['tag', 'zoom', 'dismiss'])

const count = computed(() => props.faces.length)
const untagged = computed(() => props.faces.filter((f) => !f.person).length)
</script>

<template>
  <div class="flex min-h-0 flex-col" data-testid="people">
    <div class="flex items-center gap-1.5 px-4 pb-1.5 pt-3.5">
      <span class="text-[11px] font-bold uppercase leading-4 tracking-[0.04em] text-stone">People</span>
      <span class="text-[11px] leading-4 text-stone">{{ count }}</span>
      <InfoTip label="About the people list">
        Click a name to tag the face, or to change or remove its tag. The magnifying glass zooms to the face; × dismisses a face that isn't a real person.
      </InfoTip>
      <span v-if="untagged" class="ml-auto text-[12px] font-semibold leading-[18px] text-bad">{{ untagged }} untagged</span>
    </div>
    <div class="flex min-h-0 flex-1 flex-col gap-0.5 overflow-y-auto px-2.5 py-1">
      <div v-for="(f, i) in faces" :key="i" class="flex items-center gap-1 rounded-[7px] hover:bg-shell">
        <button type="button" class="flex h-7 w-7 flex-none cursor-pointer items-center justify-center rounded-[7px] border-0 bg-transparent p-0 text-navy-light hover:text-navy"
                aria-label="Zoom to this face" title="Zoom to this face"
                @click="emit('zoom', i)">
          <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <circle cx="8.5" cy="8.5" r="5.75" />
            <path d="M13 13l4.25 4.25" stroke-linecap="round" />
          </svg>
        </button>
        <button type="button" class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 rounded-[7px] border-0 bg-transparent py-1.5 pl-0.5 text-left"
                :title="f.person ? 'Change or remove the tag' : 'Tag this face'"
                data-testid="person-row"
                @click="emit('tag', i, $event.currentTarget)">
          <span class="min-w-0 flex-1 truncate text-[13px] leading-5" :class="f.person ? 'font-semibold text-navy' : 'text-slate'">{{ f.person?.name ?? 'Untagged face' }}</span>
          <span class="flex-none whitespace-nowrap rounded-[7px] px-2 py-0.5 text-[11px] font-semibold leading-4" :style="{ background: f.pill.bg, color: f.pill.fg }">{{ f.pill.label }}</span>
        </button>
        <button type="button" class="flex h-7 w-7 flex-none cursor-pointer items-center justify-center rounded-[7px] border-0 bg-transparent p-0 text-[13px] text-stone hover:text-navy"
                aria-label="Dismiss this face" title="Dismiss this face"
                @click="emit('dismiss', $event.currentTarget)">✕</button>
      </div>
      <p v-if="!count" class="m-0 px-2.5 py-1.5 text-[12px] text-slate">No faces detected yet.</p>
    </div>
  </div>
</template>
