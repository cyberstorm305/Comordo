<!--
  DecisionPanel.vue — the three decisions as cards side by side, and the
  buttons under them (the real app's DecisionPicker + Dashboard.vue). The
  options and the recommended one (REC) are the app's own (decisionOptions
  and recommendedDecision), so "Keep as is" is unavailable once someone has
  refused, as in the app. Picking a decision works as in the app; recording
  it and Full detail open the trial prompt, and Skip moves to the next photo.
-->
<script setup>
import InfoTip from './InfoTip.vue'

defineProps({
  options: { type: Array, default: () => [] }, // the app's decisionOptions: [{ value, title, sub, disabled, num }]
  recommended: { type: String, default: null },
})

const decision = defineModel('decision', { type: String, default: null })
const emit = defineEmits(['trial', 'detail', 'skip'])

function choose(o) {
  if (!o.disabled) decision.value = o.value
}
</script>

<template>
  <div class="flex flex-col gap-3" data-testid="decision">
    <div class="grid gap-2 sm:grid-cols-3" role="radiogroup" aria-label="Decision">
      <div v-for="o in options" :key="o.value" role="radio" :tabindex="o.disabled ? -1 : 0"
           :aria-checked="decision === o.value" :aria-disabled="o.disabled"
           :title="`${o.title}: ${o.sub}`"
           class="flex min-w-0 items-center gap-2.5 rounded-[10px] px-3 py-3"
           :class="o.disabled
             ? 'cursor-not-allowed border border-dashed border-line bg-paper opacity-60'
             : decision === o.value ? 'cursor-pointer border-[1.5px] border-copper bg-copper-wash' : 'cursor-pointer border-[1.5px] border-sand bg-white'"
           data-testid="decision-option"
           @click="choose(o)"
           @keydown.space.prevent="choose(o)">
        <span class="h-4 w-4 flex-none rounded-full bg-white"
              :class="decision === o.value && !o.disabled ? 'border-[4.5px] border-copper' : 'border-[1.5px] border-edge'"></span>
        <span class="min-w-0 flex-1 truncate text-[13px] font-semibold leading-5">{{ o.title }}</span>
        <span v-if="o.value === recommended && !o.disabled" class="flex-none text-[10px] font-extrabold leading-[14px] text-copper-dark" title="Recommended">REC</span>
        <span class="flex h-5 min-w-5 flex-none items-center justify-center rounded-[4px] border border-line text-[10px] leading-[14px] text-stone">{{ o.num }}</span>
      </div>
    </div>
    <input v-if="decision === 'ok'" type="text"
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
