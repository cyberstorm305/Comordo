<!--
  QueueCard.vue — the dashboard's review queue rail (the real app's
  Dashboard.vue). The app colours rows by risk; the preview doesn't rate
  risk, so the selected row uses the copper tint and the risk filters open the
  trial prompt. Every face is untagged in a preview. On small screens the
  queue becomes a horizontal strip.
-->
<script setup>
import { computed, inject, reactive } from 'vue'
import { looksWrong } from './imageCheck.js'

const props = defineProps({
  items: { type: Array, default: () => [] }, // [{ key, name, thumb, faces, width, height }]
  selected: { type: Number, default: 0 },
  state: { type: String, default: 'queued' }, // queued | scanning | done
  progress: { type: Object, default: () => ({ pages: 0, images: 0 }) },
})
const emit = defineEmits(['select'])
const openTrial = inject('openTrial')

const broken = reactive({})
const loaded = reactive({}) // placeholder pattern shows behind a thumbnail until it loads
const ROW = { tint: '#F8F4EC', line: '#E7C9A8', solid: '#B4702F' }

// The app's filter pills: the active one filled, the rest outlined in their colour.
const filters = [
  { label: 'High', border: '#F3C9C5', color: '#B23A2E' },
  { label: 'Med', border: '#E7C9A8', color: '#8F5722' },
  { label: 'No perm', border: '#F3C9C5', color: '#B23A2E' },
]

// Only the selected row is filled; the rest stay white and outline on hover.
function rowStyle(i) {
  const selected = i === props.selected
  return {
    background: selected ? ROW.tint : 'transparent',
    boxShadow: selected ? `inset 0 0 0 1px ${ROW.line}` : 'none',
    '--row-hover': `inset 0 0 0 1px ${selected ? ROW.solid : ROW.line}`,
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
        <button type="button" class="cursor-default rounded-[14px] border border-navy bg-navy px-2.5 py-1 text-[11px] font-bold leading-4 text-white" aria-pressed="true">All {{ items.length }}</button>
        <button v-for="f in filters" :key="f.label" type="button" aria-pressed="false"
                class="cursor-pointer rounded-[14px] border bg-white px-2.5 py-1 text-[11px] font-bold leading-4"
                :style="{ borderColor: f.border, color: f.color }"
                data-testid="queue-filter"
                @click="openTrial('Risk ratings', $event.currentTarget)">{{ f.label }}</button>
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
      <button v-for="(x, i) in items" :key="x.key" type="button"
              class="queue-row flex w-[230px] flex-none cursor-pointer items-center gap-2.5 rounded-[10px] border-0 px-2.5 py-2 text-left outline-none lg:w-full"
              :style="rowStyle(i)" :aria-current="i === selected ? 'true' : undefined"
              data-testid="queue-row"
              @click="emit('select', i)">
        <img v-if="!broken[x.key]" :src="x.thumb" alt="" referrerpolicy="no-referrer" loading="lazy"
             class="h-[38px] w-[52px] flex-none rounded-[4px] border border-edge bg-paper object-cover" :class="loaded[x.key] ? '' : 'img-ph-sm'"
             @load="e => { if (looksWrong(e.target, x.width, x.height)) broken[x.key] = true; else loaded[x.key] = true }"
             @error="broken[x.key] = true">
        <div v-else class="img-ph-sm h-[38px] w-[52px] flex-none rounded-[4px] border border-edge"></div>
        <div class="min-w-0 flex-1">
          <div class="truncate font-mono text-[12px] font-semibold leading-[18px] text-navy">{{ x.name }}</div>
          <div class="mt-0.5 flex items-center gap-1.5 text-[11px] leading-4 text-stone">
            <span class="h-[7px] w-[7px] flex-none rounded-full bg-plum"></span>
            <span class="truncate">{{ x.faces }} untagged</span>
          </div>
        </div>
      </button>
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
      {{ items.length }} to review · {{ plural(progress.images, 'image', 'images') }} checked on {{ plural(progress.pages, 'page', 'pages') }}{{ state === 'done' ? '' : ' so far' }}
    </div>
  </div>
</template>
