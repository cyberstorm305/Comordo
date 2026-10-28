<!--
  QueueCard.vue — the dashboard's review queue rail (the real app's
  Dashboard.vue): rows in their risk colour with their permission line, and
  the All / High / Med / No perm filters. Photos beyond the preview's limit
  follow, greyed out: hovering one says it's in the trial, and clicking opens
  the trial prompt. On small screens the queue becomes a horizontal strip.
-->
<script setup>
import { computed, inject, reactive } from 'vue'
import { looksWrong } from './imageCheck.js'
import { matchesFilter } from './appReview.js'

const props = defineProps({
  items: { type: Array, default: () => [] }, // the photos the preview opens: [{ key, image, name, thumb, width, height, riskMeta, chip }]
  locked: { type: Array, default: () => [] }, // the ones beyond the limit, greyed out
  shown: { type: Array, default: () => [] }, // the ones the filter lets through
  selected: { type: String, default: null }, // key
  filter: { type: String, default: 'all' }, // all | high | medium | noperm
  state: { type: String, default: 'queued' }, // queued | scanning | done
  progress: { type: Object, default: () => ({ pages: 0, images: 0 }) },
})
const emit = defineEmits(['select', 'filter'])
const openTrial = inject('openTrial')

const broken = reactive({})
const loaded = reactive({}) // placeholder pattern shows behind a thumbnail until it loads
// The app's filter pills: the active one filled, the rest outlined in their colour.
const filters = [
  { key: 'all', label: 'All', on: '#1B2536', border: '#DAD3C4', color: '#4A5468' },
  { key: 'high', label: 'High', on: '#B23A2E', border: '#F3C9C5', color: '#B23A2E' },
  { key: 'medium', label: 'Med', on: '#B4702F', border: '#E7C9A8', color: '#8F5722' },
  { key: 'noperm', label: 'No perm', on: '#B23A2E', border: '#F3C9C5', color: '#B23A2E' },
]
const count = (key) => props.items.filter((x) => matchesFilter(x.image, key)).length
function filterStyle(f) {
  return f.key === props.filter
    ? { borderColor: f.on, background: f.on, color: '#fff' }
    : { borderColor: f.border, background: '#fff', color: f.color }
}

// Only the selected row is filled in its risk colour; the rest stay white and outline on hover.
function rowStyle(x) {
  const meta = x.riskMeta
  const selected = x.key === props.selected
  return {
    background: selected ? meta.tint : 'transparent',
    boxShadow: selected ? `inset 0 0 0 1px ${meta.line}` : 'none',
    '--row-hover': `inset 0 0 0 1px ${selected ? meta.solid : meta.line}`,
  }
}
// Screen readers hear only state changes and new photos, not every page count.
const announcement = computed(() => {
  const n = props.items.length
  const photos = `${n} ${n === 1 ? 'photo' : 'photos'} with people`
  if (props.state === 'queued') return 'Waiting to start.'
  if (props.state === 'scanning') return `Scanning. ${photos} found so far.`
  return `Scan finished. ${photos} found.`
})
const plural = (n, one, many) => `${n} ${n === 1 ? one : many}`
</script>

<template>
  <div class="flex min-h-0 flex-col overflow-hidden rounded-[14px] border border-sand bg-white lg:sticky lg:top-[85px] lg:max-h-[calc(100vh-145px)]" data-testid="queue">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 border-b border-shell px-3.5 py-3">
      <span class="text-[11px] font-bold uppercase leading-4 tracking-[0.04em] text-stone">Queue</span>
      <div class="flex flex-wrap gap-1">
        <button v-for="f in filters" :key="f.key" type="button" :aria-pressed="f.key === filter"
                :disabled="f.key !== 'all' && !count(f.key)"
                class="cursor-pointer rounded-[14px] border px-2.5 py-1 text-[11px] font-bold leading-4 disabled:cursor-default disabled:opacity-50"
                :style="filterStyle(f)"
                data-testid="queue-filter"
                @click="emit('filter', f.key)">{{ f.label }} {{ count(f.key) }}</button>
      </div>
    </div>
    <p class="sr-only" aria-live="polite" data-testid="queue-announcement">{{ announcement }}</p>
    <div v-if="state !== 'done'" class="flex items-center gap-2 border-b border-shell px-3.5 py-2.5 text-[12px] leading-[18px] text-slate" data-testid="queue-status">
      <span class="h-3 w-3 flex-none animate-spin rounded-full border-2 border-sand border-t-copper" aria-hidden="true"></span>
      <span v-if="state === 'queued'">Waiting to start…</span>
      <span v-else>Scanning… {{ plural(progress.pages, 'page', 'pages') }} · {{ plural(progress.images, 'image', 'images') }}</span>
    </div>

    <!-- rows: a vertical list from lg, a horizontal strip below -->
    <div class="flex min-h-0 flex-1 flex-row gap-1.5 overflow-x-auto p-1.5 lg:flex-col lg:gap-0.5 lg:overflow-x-hidden lg:overflow-y-auto">
      <button v-for="x in shown" :key="x.key" type="button"
              class="queue-row flex w-[230px] flex-none cursor-pointer items-center gap-2.5 rounded-[10px] border-0 px-2.5 py-2 text-left outline-none lg:w-full"
              :style="rowStyle(x)" :aria-current="x.key === selected ? 'true' : undefined"
              data-testid="queue-row"
              @click="emit('select', x.key)">
        <img v-if="!broken[x.key]" :src="x.thumb" alt="" referrerpolicy="no-referrer" loading="lazy"
             class="h-[38px] w-[52px] flex-none rounded-[4px] border border-edge bg-paper object-cover" :class="loaded[x.key] ? '' : 'img-ph-sm'"
             @load="e => { if (looksWrong(e.target, x.width, x.height)) broken[x.key] = true; else loaded[x.key] = true }"
             @error="broken[x.key] = true">
        <div v-else class="img-ph-sm h-[38px] w-[52px] flex-none rounded-[4px] border border-edge"></div>
        <div class="min-w-0 flex-1">
          <div class="truncate font-mono text-[12px] font-semibold leading-[18px] text-navy">{{ x.name }}</div>
          <div class="mt-0.5 flex items-center gap-1.5 text-[11px] leading-4 text-stone">
            <span class="h-[7px] w-[7px] flex-none rounded-full" :style="{ background: x.riskMeta.solid }" :title="`${x.riskMeta.long} risk`"></span>
            <span class="truncate">{{ x.chip }}</span>
          </div>
        </div>
      </button>
      <template v-if="filter === 'all'">
        <button v-for="x in locked" :key="x.key" type="button"
                class="group flex w-[230px] flex-none cursor-pointer items-center gap-2.5 rounded-[10px] border-0 bg-transparent px-2.5 py-2 text-left outline-none hover:bg-copper-wash focus-visible:bg-copper-wash lg:w-full"
                :aria-label="`${x.name}: start your 14-day free trial to review this photo`"
                data-testid="queue-row-locked"
                @click="openTrial('Reviewing every photo', $event.currentTarget)">
          <span class="relative h-[38px] w-[52px] flex-none overflow-hidden rounded-[4px] border border-edge">
            <img v-if="!broken[x.key]" :src="x.thumb" alt="" referrerpolicy="no-referrer" loading="lazy"
                 class="h-full w-full object-cover opacity-50 grayscale" :class="loaded[x.key] ? '' : 'img-ph-sm'"
                 @load="e => { if (looksWrong(e.target, x.width, x.height)) broken[x.key] = true; else loaded[x.key] = true }"
                 @error="broken[x.key] = true">
            <span v-else class="img-ph-sm block h-full w-full opacity-50 grayscale"></span>
            <span class="absolute inset-0 flex items-center justify-center text-navy/70 group-hover:text-copper-dark group-focus-visible:text-copper-dark" aria-hidden="true">
              <svg width="11" height="13" viewBox="0 0 11 13" fill="none"><rect x="1" y="5.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 5.5V3.8a2.5 2.5 0 015 0v1.7" stroke="currentColor" stroke-width="1.6"/></svg>
            </span>
          </span>
          <span class="min-w-0 flex-1">
            <span class="block truncate font-mono text-[12px] font-semibold leading-[18px] text-stone">{{ x.name }}</span>
            <span class="mt-0.5 block truncate text-[11px] leading-4 text-stone group-hover:hidden group-focus-visible:hidden">In your trial</span>
            <span class="mt-0.5 hidden truncate text-[11px] font-semibold leading-4 text-copper-dark group-hover:block group-focus-visible:block">Start your free trial to review →</span>
          </span>
        </button>
      </template>
      <template v-if="state !== 'done'">
        <div v-for="n in 2" :key="'ph' + n" class="isp-pulse flex w-[230px] flex-none items-center gap-2.5 rounded-[10px] px-2.5 py-2 lg:w-full" aria-hidden="true">
          <div class="img-ph-sm h-[38px] w-[52px] flex-none rounded-[4px] border border-edge opacity-60"></div>
          <div class="min-w-0 flex-1">
            <div class="h-[9px] w-4/5 rounded bg-sand-light"></div>
            <div class="mt-[7px] h-[9px] w-12 rounded bg-hair"></div>
          </div>
        </div>
      </template>
    </div>

    <div class="mt-auto hidden border-t border-shell px-3.5 py-3 text-[12px] leading-[18px] text-stone lg:block">
      {{ shown.length }} to review<template v-if="locked.length"> · {{ locked.length }} more in your trial</template> · {{ plural(progress.images, 'image', 'images') }} checked on {{ plural(progress.pages, 'page', 'pages') }}{{ state === 'done' ? '' : ' so far' }}
    </div>
  </div>
</template>
