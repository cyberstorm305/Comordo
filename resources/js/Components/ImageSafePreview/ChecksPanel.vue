<!--
  ChecksPanel.vue — the "Safeguarding checks" list (the real app's
  Review/SafeguardingChecks). Checks that need a look show in full; the passed
  ones fold into one line until shown, as in the app. The checks a preview
  can't run fold into a second line. "What do these mean?" opens the
  explainer, and clicking a check opens it at that check. Give it a :key of
  the photo so each one starts folded.
-->
<script setup>
import { computed, ref } from 'vue'
import ChecksExplainer from './ChecksExplainer.vue'
const props = defineProps({
  checks: { type: Array, default: () => [] },
  notChecked: { type: Array, default: () => [] }, // the app's checks a preview doesn't run, from the API: [{ key, name }]
})

const showPassed = ref(false)
const showLocked = ref(false)
const flagged = computed(() => props.checks.filter((c) => c.state === 'fail' || c.state === 'warn'))
const passed = computed(() => props.checks.filter((c) => c.state !== 'fail' && c.state !== 'warn'))
const passedLabel = computed(() => {
  const n = passed.value.length
  const noun = n === 1 ? 'check' : 'checks'
  return passed.value.every((c) => c.state === 'na') ? `${n} ${noun} not applicable` : `${n} ${noun} passed`
})

const explaining = ref(false)
const highlight = ref(null)
const explainReturn = ref(null)
function explain(key, el) {
  highlight.value = key
  explainReturn.value = el
  explaining.value = true
}
// A check's own note (such as where an AI label came from), otherwise the app's hint.
const rowTitle = (c) => c.note || `What does “${c.label}” mean?`
</script>

<template>
  <div data-testid="checks">
    <div class="flex items-center gap-1.5 px-4 pb-2 pt-3.5">
      <span class="text-[11px] font-bold uppercase leading-4 tracking-[0.04em] text-stone">Safeguarding checks</span>
      <button type="button" class="ml-auto cursor-pointer border-0 bg-transparent p-0 text-[11px] font-semibold leading-4 text-copper-dark hover:underline"
              data-testid="explain-checks" @click="explain(null, $event.currentTarget)">What do these mean?</button>
    </div>
    <div class="flex flex-col gap-1 border-b border-shell px-2.5 pb-3">
      <div v-for="c in flagged" :key="c.key" role="button" tabindex="0" class="check-row cursor-pointer hover:brightness-[.98]" :class="`check-row--${c.state}`" :title="rowTitle(c)" data-testid="check"
           @click="explain(c.key, $event.currentTarget)" @keydown.enter.prevent="explain(c.key, $event.currentTarget)">
        <span class="check-row__icon" aria-hidden="true">{{ c.icon }}</span>
        <span class="check-row__label">{{ c.label }}</span>
        <span class="check-row__word">{{ c.word }}</span>
      </div>
      <template v-if="passed.length">
        <button type="button" class="flex cursor-pointer items-center gap-2 rounded-[7px] border-0 bg-transparent px-2.5 py-1.5 text-left hover:bg-paper"
                :aria-expanded="showPassed" @click="showPassed = !showPassed">
          <span class="check-row__icon bg-good-pale text-good" aria-hidden="true">✓</span>
          <span class="min-w-0 flex-1 text-[12px] font-semibold leading-[18px] text-good">{{ passedLabel }}</span>
          <span class="flex-none text-[11px] leading-4 text-stone">{{ showPassed ? 'Hide' : 'Show' }}</span>
        </button>
        <template v-if="showPassed">
          <div v-for="c in passed" :key="c.key" role="button" tabindex="0" class="check-row cursor-pointer hover:bg-paper" :class="`check-row--${c.state}`" :title="rowTitle(c)" data-testid="check"
               @click="explain(c.key, $event.currentTarget)" @keydown.enter.prevent="explain(c.key, $event.currentTarget)">
            <span class="check-row__icon" aria-hidden="true">{{ c.icon }}</span>
            <span class="check-row__label">{{ c.label }}</span>
            <span class="check-row__word">{{ c.word }}</span>
          </div>
        </template>
      </template>
      <button v-if="notChecked.length" type="button" class="flex cursor-pointer items-center gap-2 rounded-[7px] border-0 bg-transparent px-2.5 py-1.5 text-left hover:bg-paper"
              :aria-expanded="showLocked" data-testid="locked-toggle"
              @click="showLocked = !showLocked">
        <span class="check-row__icon bg-shell text-stone" aria-hidden="true">
          <svg width="8" height="10" viewBox="0 0 11 13" fill="none"><rect x="1" y="5.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 5.5V3.8a2.5 2.5 0 015 0v1.7" stroke="currentColor" stroke-width="1.6"/></svg>
        </span>
        <span class="min-w-0 flex-1 text-[12px] font-semibold leading-[18px] text-slate">{{ notChecked.length }} more checks in your trial</span>
        <span class="flex-none text-[11px] leading-4 text-stone">{{ showLocked ? 'Hide' : 'Show' }}</span>
      </button>
      <template v-if="showLocked">
        <div v-for="c in notChecked" :key="c.key" role="button" tabindex="0" class="check-row check-row--locked cursor-pointer hover:bg-paper" :title="`What does “${c.name}” mean?`" data-testid="locked-check"
             @click="explain(c.key, $event.currentTarget)" @keydown.enter.prevent="explain(c.key, $event.currentTarget)">
          <span class="check-row__icon" aria-hidden="true">
            <svg width="8" height="10" viewBox="0 0 11 13" fill="none"><rect x="1" y="5.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 5.5V3.8a2.5 2.5 0 015 0v1.7" stroke="currentColor" stroke-width="1.6"/></svg>
          </span>
          <span class="check-row__label">{{ c.name }}</span>
          <span class="check-row__word">In your trial</span>
        </div>
      </template>
    </div>
    <ChecksExplainer :open="explaining" :highlight="highlight" :locked="notChecked.map((c) => c.key)" :return-to="explainReturn" @close="explaining = false" />
  </div>
</template>
