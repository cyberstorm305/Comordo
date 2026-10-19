<!--
  QueueCard.vue — the dashboard's review queue rail (Designs/Imagesafe
  DashboardScreen.vue + the real app's Dashboard.vue). Rows are neutral, not
  risk-coloured: the preview doesn't rate risk. On small screens the queue
  becomes a horizontal strip.
-->
<script setup>
import { reactive } from 'vue'

const props = defineProps({
  items: { type: Array, default: () => [] }, // [{ key, name, thumb, faces }]
  selected: { type: Number, default: 0 },
  state: { type: String, default: 'queued' }, // queued | scanning | done
  progress: { type: Object, default: () => ({ pages: 0, images: 0 }) },
})
const emit = defineEmits(['select'])

const broken = reactive({})
const ROW = { tint: '#FBFAF6', line: '#E7E2D6', solid: '#1B2536' }

function rowStyle(i) {
  return {
    background: ROW.tint,
    boxShadow: `inset 0 0 0 1px ${i === props.selected ? ROW.solid : ROW.line}`,
    '--row-hover': `inset 0 0 0 1px ${ROW.solid}`,
  }
}
const peopleWord = (n) => `${n} ${n === 1 ? 'PERSON' : 'PEOPLE'}`
const plural = (n, one, many) => `${n} ${n === 1 ? one : many}`
</script>

<template>
  <div class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-sand bg-white lg:sticky lg:top-[112px] lg:max-h-[calc(100vh-160px)]" data-testid="queue">
    <div class="flex items-center justify-between border-b border-hair px-3.5 py-3">
      <span class="text-[11px] font-bold uppercase tracking-[0.4px] text-stone">Queue</span>
      <span class="text-[11.5px] text-stone">{{ items.length }} to review</span>
    </div>
    <div class="flex flex-wrap gap-[5px] border-b border-hair px-3 py-2.5">
      <button type="button" class="cursor-default rounded-[14px] border border-navy bg-navy px-2.5 py-1 text-[11px] font-bold text-white" aria-pressed="true">All {{ items.length }}</button>
    </div>
    <div v-if="state !== 'done'" class="flex items-center gap-2 border-b border-hair px-3.5 py-2.5 text-[11.5px] text-slate" aria-live="polite" data-testid="queue-status">
      <span class="h-3 w-3 flex-none animate-spin rounded-full border-2 border-sand border-t-copper" aria-hidden="true"></span>
      <span v-if="state === 'queued'">Waiting to start…</span>
      <span v-else>Scanning… {{ plural(progress.pages, 'page', 'pages') }} · {{ plural(progress.images, 'image', 'images') }}</span>
    </div>

    <!-- rows: a vertical list from lg, a horizontal strip below -->
    <div class="flex min-h-0 flex-1 flex-row gap-[6px] overflow-x-auto p-1.5 lg:flex-col lg:gap-[3px] lg:overflow-x-hidden lg:overflow-y-auto">
      <button v-for="(x, i) in items" :key="x.key" type="button"
              class="queue-row flex w-[230px] flex-none cursor-pointer items-center gap-[9px] rounded-[9px] border-0 px-2.5 py-2 text-left outline-none lg:w-full"
              :style="rowStyle(i)" :aria-current="i === selected ? 'true' : undefined"
              data-testid="queue-row"
              @click="emit('select', i)">
        <img v-if="!broken[x.key]" :src="x.thumb" alt="" referrerpolicy="no-referrer" loading="lazy"
             class="img-ph-sm h-[38px] w-[52px] flex-none rounded-[5px] border border-[#CFC8B8] object-cover"
             @error="broken[x.key] = true">
        <div v-else class="img-ph-sm h-[38px] w-[52px] flex-none rounded-[5px] border border-[#CFC8B8]"></div>
        <div class="min-w-0 flex-1">
          <div class="truncate font-mono text-xs font-semibold text-navy">{{ x.name }}</div>
          <div class="mt-[3px] flex gap-1">
            <span class="rounded-[5px] border border-copper-pale bg-white px-1.5 py-px text-[9px] font-bold text-copper-dark">{{ peopleWord(x.faces) }}</span>
          </div>
        </div>
      </button>
      <template v-if="state !== 'done'">
        <div v-for="n in 2" :key="'ph' + n" class="isp-pulse flex w-[230px] flex-none items-center gap-[9px] rounded-[9px] px-2.5 py-2 lg:w-full" style="background:#FBFAF6; box-shadow: inset 0 0 0 1px #F2EEE3" aria-hidden="true">
          <div class="img-ph-sm h-[38px] w-[52px] flex-none rounded-[5px] border border-[#CFC8B8] opacity-60"></div>
          <div class="min-w-0 flex-1">
            <div class="h-[9px] w-4/5 rounded bg-sand-light"></div>
            <div class="mt-[7px] h-[9px] w-12 rounded bg-hair"></div>
          </div>
        </div>
      </template>
    </div>

    <div class="mt-auto hidden border-t border-hair px-3.5 py-3 text-[11.5px] text-slate lg:block">
      <span class="mr-1.5 inline-block h-2 w-2 rounded-sm bg-good"></span><b>{{ plural(progress.images, 'image', 'images') }}</b> checked on {{ plural(progress.pages, 'page', 'pages') }}{{ state === 'done' ? '' : ' so far' }}
    </div>
  </div>
</template>
