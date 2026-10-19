<!--
  ChecksPanel.vue — the "Safeguarding checks" list (Designs/Imagesafe
  components/ChecksList.vue, real app check-row styles). Only "People detected"
  is real in a preview; the app's other checks are shown locked.
-->
<script setup>
defineProps({ people: { type: Number, default: 0 } })

// The real app's checks (SafeguardingReviewService + "Permission to use").
const locked = [
  'Permission to use',
  'Face visibility',
  'Close-up vs wide-angle',
  'Individual pupil focus',
  'Group / activity-led composition',
  'Image resolution',
  'Easy to crop / isolate a child',
  'Metadata present',
]
</script>

<template>
  <div class="flex flex-col gap-[3px]" data-testid="checks">
    <div class="flex items-center gap-[9px] rounded-lg border border-copper-pale bg-warn-pale px-2.5 py-[7px]">
      <span class="flex h-[17px] w-[17px] flex-none items-center justify-center rounded-full bg-copper text-[10px] font-bold text-white" aria-hidden="true">!</span>
      <span class="min-w-0 flex-1 text-xs font-semibold text-copper-dark">People detected: {{ people }}</span>
      <span class="flex-none text-[10px] font-extrabold text-copper-dark">CHECK</span>
    </div>
    <div v-for="label in locked" :key="label" class="flex items-center gap-[9px] rounded-lg border border-transparent px-2.5 py-[7px]" data-testid="locked-check">
      <span class="flex h-[17px] w-[17px] flex-none items-center justify-center rounded-full bg-hair text-stone" aria-hidden="true">
        <svg width="8" height="10" viewBox="0 0 11 13" fill="none"><rect x="1" y="5.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 5.5V3.8a2.5 2.5 0 015 0v1.7" stroke="currentColor" stroke-width="1.6"/></svg>
      </span>
      <span class="min-w-0 flex-1 text-xs text-stone">{{ label }}</span>
      <span class="flex-none text-[10px] font-semibold text-mist">Checked in your trial</span>
    </div>
  </div>
</template>
