<!--
  ChecksPanel.vue — the "Safeguarding checks" list (the real app's
  Review/SafeguardingChecks). Every face is untagged in a preview, so
  "Permission to use" needs a check; the app's other checks run in the trial,
  and fold into one line until shown, as passed checks do in the app.
-->
<script setup>
import { ref } from 'vue'
import InfoTip from './InfoTip.vue'

defineProps({ people: { type: Number, default: 0 } })

// The real app's checks (SafeguardingReviewService).
const locked = [
  'Face visibility',
  'Close-up vs wide-angle',
  'Individual pupil focus',
  'Group / activity-led composition',
  'Image resolution',
  'Easy to crop / isolate a child',
  'Metadata present',
]
const showLocked = ref(false)
</script>

<template>
  <div data-testid="checks">
    <div class="flex items-center gap-1.5 px-4 pb-2 pt-3.5">
      <span class="text-[11px] font-bold uppercase leading-4 tracking-[0.04em] text-stone">Safeguarding checks</span>
      <InfoTip label="How the checks work">
        Your trial matches every face against your permission records and runs every check.
        Any flag = high risk. AI-assisted — your judgement decides.
      </InfoTip>
    </div>
    <div class="flex flex-col gap-1 border-b border-shell px-2.5 pb-3">
      <div class="check-row check-row--warn" :title="`${people} untagged ${people === 1 ? 'face' : 'faces'}`">
        <span class="check-row__icon" aria-hidden="true">!</span>
        <span class="check-row__label">Permission to use</span>
        <span class="check-row__word">CHECK</span>
      </div>
      <button type="button" class="flex cursor-pointer items-center gap-2 rounded-[7px] border-0 bg-transparent px-2.5 py-1.5 text-left hover:bg-paper"
              :aria-expanded="showLocked" data-testid="locked-toggle"
              @click="showLocked = !showLocked">
        <span class="check-row__icon bg-shell text-stone" aria-hidden="true">
          <svg width="8" height="10" viewBox="0 0 11 13" fill="none"><rect x="1" y="5.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 5.5V3.8a2.5 2.5 0 015 0v1.7" stroke="currentColor" stroke-width="1.6"/></svg>
        </span>
        <span class="min-w-0 flex-1 text-[12px] font-semibold leading-[18px] text-slate">{{ locked.length }} more checks in your trial</span>
        <span class="flex-none text-[11px] leading-4 text-stone">{{ showLocked ? 'Hide' : 'Show' }}</span>
      </button>
      <template v-if="showLocked">
        <div v-for="label in locked" :key="label" class="check-row check-row--locked" data-testid="locked-check">
          <span class="check-row__icon" aria-hidden="true">
            <svg width="8" height="10" viewBox="0 0 11 13" fill="none"><rect x="1" y="5.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 5.5V3.8a2.5 2.5 0 015 0v1.7" stroke="currentColor" stroke-width="1.6"/></svg>
          </span>
          <span class="check-row__label">{{ label }}</span>
          <span class="check-row__word">In your trial</span>
        </div>
      </template>
    </div>
  </div>
</template>
