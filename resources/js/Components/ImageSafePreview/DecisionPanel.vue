<!--
  DecisionPanel.vue — the three decisions as cards side by side, and the
  buttons under them (the real app's DecisionPicker + Dashboard.vue). The
  decision recommended for the photo's risk is marked REC. Picking a decision
  works as in the app; recording it and Full detail open the trial
  prompt, and Skip moves to the next photo.
-->
<script setup>
import InfoTip from './InfoTip.vue'

defineProps({ recommended: { type: String, default: null } })

const decision = defineModel('decision', { type: String, default: null })
const emit = defineEmits(['trial', 'detail', 'skip'])

const opts = [
  { value: 'remove', title: 'Remove from website', sub: 'Take it down, tick off here', num: 1 },
  { value: 'fix', title: 'Fix on site', sub: 'Crop, blur or edit caption', num: 2 },
  { value: 'keep', title: 'Keep as is', sub: '⚠ note required', num: 3 },
]
</script>

<template>
  <div class="flex flex-col gap-3" data-testid="decision">
    <div class="grid gap-2 sm:grid-cols-3" role="radiogroup" aria-label="Decision">
      <div v-for="o in opts" :key="o.value" role="radio" tabindex="0"
           :aria-checked="decision === o.value"
           :title="`${o.title}: ${o.sub}`"
           class="flex min-w-0 cursor-pointer items-center gap-2.5 rounded-[10px] border-[1.5px] px-3 py-3"
           :class="decision === o.value ? 'border-copper bg-copper-wash' : 'border-sand bg-white'"
           data-testid="decision-option"
           @click="decision = o.value"
           @keydown.space.prevent="decision = o.value">
        <span class="h-4 w-4 flex-none rounded-full bg-white"
              :class="decision === o.value ? 'border-[4.5px] border-copper' : 'border-[1.5px] border-edge'"></span>
        <span class="min-w-0 flex-1 truncate text-[13px] font-semibold leading-5">{{ o.title }}</span>
        <span v-if="o.value === recommended" class="flex-none text-[10px] font-extrabold leading-[14px] text-copper-dark" title="Recommended">REC</span>
        <span class="flex h-5 min-w-5 flex-none items-center justify-center rounded-[4px] border border-line text-[10px] leading-[14px] text-stone">{{ o.num }}</span>
      </div>
    </div>
    <input v-if="decision === 'keep'" type="text"
           class="w-full rounded-[7px] border border-copper-pale bg-white px-2.5 py-2 text-[13px] leading-5 text-navy outline-none placeholder:text-stone focus:border-copper"
           placeholder="Why is it OK to keep? This is required when permission or risk is flagged.">
    <div class="flex flex-wrap items-center gap-2 sm:flex-nowrap">
      <button type="button" class="basis-full cursor-pointer rounded-[7px] border-0 bg-navy p-3 sm:flex-1 sm:basis-auto text-[14px] font-semibold leading-5 text-white hover:bg-navy-light" @click="emit('trial', $event.currentTarget)">
        Record decision &amp; next <span class="text-[12px] text-white/60">⏎</span>
      </button>
      <button type="button" class="flex-1 cursor-pointer whitespace-nowrap rounded-[7px] border border-line bg-white px-3.5 py-3 text-[13px] font-semibold leading-5 text-copper-dark sm:flex-none hover:bg-shell" @click="emit('detail', $event.currentTarget)">Full detail →</button>
      <button type="button" class="flex-1 cursor-pointer whitespace-nowrap rounded-[7px] border border-line bg-white px-3.5 py-3 text-[13px] font-semibold leading-5 text-navy sm:flex-none hover:bg-shell" @click="emit('skip')">Skip</button>
      <InfoTip label="About recording a decision" align="right" placement="above">
        Recording adds to this image's log — it doesn't change your website.
        Shortcuts: ↑↓ move · 1–3 pick · ⏎ record &amp; next.
      </InfoTip>
    </div>
  </div>
</template>
