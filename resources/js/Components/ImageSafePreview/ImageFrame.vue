<!--
  ImageFrame.vue — the photo with its detected-face boxes (the real app's queue
  frame and ZoomableImage). The frame is 16:9 on small screens and takes the
  height the controls leave from lg up. The photo is fitted inside it, and the
  boxes sit on a layer the exact size of the photo, so they are placed in
  percentages of its natural width and height. Boxes and tags take the colour
  of the face's permission, and named faces show a first name.

  Zoom works as in the app: zoomToFace (the people list's magnifying glass)
  fills a good part of the view with a face, −, + and Fit step and reset, and
  a zoomed photo can be dragged. Boxes and tags keep their size on screen.
-->
<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import InfoTip from './InfoTip.vue'
import { looksWrong } from './imageCheck.js'

const props = defineProps({
  photo: { type: Object, required: true }, // { image_url, width, height, faces: [{x,y,w,h}] }
  alt: { type: String, default: '' },
  tags: { type: Array, default: () => [] }, // per face: { text, line, named }
})
const emit = defineEmits(['tag'])

const broken = ref(false)
const loaded = ref(false)

function onLoad(e) {
  if (looksWrong(e.target, props.photo.width, props.photo.height)) broken.value = true
  else loaded.value = true
}

// The frame's size, so the photo layer can be fitted inside it.
const frame = ref(null)
const size = ref({ w: 0, h: 0 })
let observer = null
onMounted(() => {
  observer = new ResizeObserver(([entry]) => {
    size.value = { w: entry.contentRect.width, h: entry.contentRect.height }
    clamp()
  })
  observer.observe(frame.value)
})
onBeforeUnmount(() => observer?.disconnect())

const PLUM = '#5E5170' // the app's "Unknown person" colour
const tagOf = (i) => props.tags[i] || { text: 'Unknown', line: PLUM, named: false }
const faces = computed(() => props.photo.faces || [])
const w = computed(() => props.photo.width || 16)
const h = computed(() => props.photo.height || 9)

// The photo fitted inside the frame at 1x, then zoomed and moved by scale, tx and ty.
const MAX_SCALE = 6
const scale = ref(1)
const tx = ref(0)
const ty = ref(0)
const animating = ref(false)
const zoomed = computed(() => scale.value > 1.001)
const fit = computed(() => (size.value.w && size.value.h ? Math.min(size.value.w / w.value, size.value.h / h.value) : 0))
const layerStyle = computed(() => fit.value
  ? { width: `${w.value * fit.value}px`, height: `${h.value * fit.value}px`, transform: `translate(${tx.value}px, ${ty.value}px) scale(${scale.value})` }
  : { width: '100%', aspectRatio: `${w.value} / ${h.value}` })

// Centre the photo when it's smaller than the frame; otherwise keep its edges outside the frame.
function clamp() {
  const pw = w.value * fit.value * scale.value
  const ph = h.value * fit.value * scale.value
  tx.value = pw <= size.value.w ? (size.value.w - pw) / 2 : Math.min(0, Math.max(size.value.w - pw, tx.value))
  ty.value = ph <= size.value.h ? (size.value.h - ph) / 2 : Math.min(0, Math.max(size.value.h - ph, ty.value))
}

// Zoom keeping the point (x, y) in the frame still.
function zoomAt(next, x, y) {
  const target = Math.min(MAX_SCALE, Math.max(1, next))
  const ix = (x - tx.value) / scale.value
  const iy = (y - ty.value) / scale.value
  animating.value = true
  scale.value = target
  tx.value = x - ix * target
  ty.value = y - iy * target
  clamp()
}
const zoomBy = (factor) => zoomAt(scale.value * factor, size.value.w / 2, size.value.h / 2)

function reset() {
  animating.value = true
  scale.value = 1
  clamp()
}

// Zoom so face i fills a good part of the view, centred (the app's zoomToBox).
function zoomToFace(i) {
  const f = faces.value[i]
  if (!f || !fit.value) return
  const target = Math.min(MAX_SCALE, Math.max(1, Math.min((size.value.w * 0.3) / Math.max(1, f.w * fit.value), (size.value.h * 0.4) / Math.max(1, f.h * fit.value))))
  animating.value = true
  scale.value = target
  tx.value = size.value.w / 2 - (f.x + f.w / 2) * fit.value * target
  ty.value = size.value.h / 2 - (f.y + f.h / 2) * fit.value * target
  clamp()
}
defineExpose({ zoomToFace })

// Each photo starts fitted.
watch(() => props.photo.image_url, () => {
  broken.value = false
  loaded.value = false
  animating.value = false
  scale.value = 1
  clamp()
})

// Drag to move a zoomed photo. A drag doesn't count as a click on the face under it.
const drag = ref(null)
let suppressClick = false
function onPointerDown(e) {
  if (!zoomed.value || e.button !== 0) return
  drag.value = { x: e.clientX, y: e.clientY, tx: tx.value, ty: ty.value, moved: false }
  window.addEventListener('pointermove', onPointerMove)
  window.addEventListener('pointerup', onPointerUp)
}
function onPointerMove(e) {
  const d = drag.value
  const dx = e.clientX - d.x
  const dy = e.clientY - d.y
  if (!d.moved && Math.hypot(dx, dy) < 4) return
  d.moved = true
  animating.value = false
  tx.value = d.tx + dx
  ty.value = d.ty + dy
  clamp()
}
function onPointerUp() {
  suppressClick = drag.value?.moved ?? false
  drag.value = null
  window.removeEventListener('pointermove', onPointerMove)
  window.removeEventListener('pointerup', onPointerUp)
}
function onClickCapture(e) {
  if (suppressClick) { e.stopPropagation(); e.preventDefault() }
  suppressClick = false
}
onBeforeUnmount(onPointerUp)

// Named faces always show their tag; on busy photos "Unknown" tags show on hover only, as in the app.
const showTag = (i) => tagOf(i).named || faces.value.length <= 8
function box(f, i) {
  return {
    left: `${(f.x / w.value) * 100}%`,
    top: `${(f.y / h.value) * 100}%`,
    width: `${(f.w / w.value) * 100}%`,
    height: `${(f.h / h.value) * 100}%`,
    border: `${2 / scale.value}px solid ${tagOf(i).line}`,
    borderRadius: `${4 / scale.value}px`,
    boxShadow: `0 0 0 ${2 / scale.value}px rgba(255,255,255,.55)`,
  }
}
// The tag sits above the box, or below it when the box is near the top, and shrinks back on a zoomed photo.
const nearTop = (f) => f.y / h.value < 0.08
const tagStyle = (f, i) => ({
  background: tagOf(i).line,
  transform: `scale(${1 / scale.value})`,
  transformOrigin: `${nearTop(f) ? 'top' : 'bottom'} left`,
})
</script>

<template>
  <div ref="frame" class="relative isolate aspect-video w-full select-none overflow-hidden rounded-[10px] border border-edge bg-paper lg:aspect-auto lg:min-h-[240px] lg:flex-1"
       :class="zoomed ? (drag ? 'cursor-grabbing' : 'cursor-grab') : ''"
       data-testid="image-frame"
       @pointerdown="onPointerDown" @click.capture="onClickCapture">
    <div v-if="!broken" class="absolute left-0 top-0 origin-top-left" :class="[loaded ? '' : 'img-ph', animating ? 'transition-transform duration-200 ease-out' : '']"
         :style="layerStyle" data-testid="photo-layer" @transitionend="animating = false">
      <img :src="photo.image_url" :alt="alt" referrerpolicy="no-referrer" draggable="false"
           class="pointer-events-none block h-full w-full object-fill"
           @load="onLoad" @error="broken = true">
      <template v-if="loaded">
        <button v-for="(f, i) in faces" :key="i" type="button"
                class="group absolute z-20 cursor-pointer bg-transparent p-0 text-left"
                :style="box(f, i)" :aria-label="tagOf(i).named ? `Change the tag on ${tagOf(i).text}` : `Tag face ${i + 1}`"
                data-testid="face-box"
                @click="emit('tag', i, $event.currentTarget)">
          <span class="pointer-events-none absolute left-0 whitespace-nowrap rounded-[4px] px-1 py-px text-[10px] font-semibold leading-[14px] text-white"
                :class="[nearTop(f) ? 'top-full mt-0.5' : 'bottom-full mb-0.5', showTag(i) ? '' : 'hidden group-hover:block']"
                :style="tagStyle(f, i)">{{ tagOf(i).text }}</span>
        </button>
      </template>
    </div>
    <div v-else class="img-ph flex h-full w-full items-center justify-center" data-testid="photo-placeholder">
      <span class="rounded-full bg-white/80 px-3 py-1.5 text-[12px] font-medium text-slate">This photo couldn't be shown here.</span>
    </div>
    <div class="absolute bottom-3 left-3 z-30 flex items-center gap-1.5 rounded-[7px] bg-navy/[.78] px-2.5 py-1.5 text-[12px] font-medium leading-[18px] text-white backdrop-blur-[2px]" data-testid="faces-detected" @pointerdown.stop>
      {{ faces.length }} {{ faces.length === 1 ? 'face' : 'faces' }}
      <InfoTip label="Using the photo" placement="above" tone="light">
        <template v-if="faces.length">Click a face to tag it, and again to change its tag. The magnifying glass beside a name zooms to that face. </template>Use + and − to zoom, drag to move, and Fit to see the whole photo.
      </InfoTip>
    </div>
    <div class="absolute bottom-3 right-3 z-30 flex items-center gap-0.5 rounded-[7px] bg-navy/[.78] p-0.5 text-white backdrop-blur-[2px]" @pointerdown.stop>
      <button type="button" class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-[4px] border-0 bg-transparent text-[14px] text-white hover:bg-white/15 disabled:cursor-default disabled:opacity-40" aria-label="Zoom out" :disabled="!zoomed" @click="zoomBy(1 / 1.4)">−</button>
      <span class="w-11 text-center text-[11px] tabular-nums" data-testid="zoom-level">{{ Math.round(scale * 100) }}%</span>
      <button type="button" class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-[4px] border-0 bg-transparent text-[14px] text-white hover:bg-white/15 disabled:cursor-default disabled:opacity-40" aria-label="Zoom in" :disabled="scale >= MAX_SCALE" @click="zoomBy(1.4)">+</button>
      <button type="button" class="ml-0.5 cursor-pointer rounded-[4px] border-0 bg-transparent px-2 py-1 text-[11px] font-semibold text-white hover:bg-white/15 disabled:cursor-default disabled:opacity-40" :disabled="!zoomed" data-testid="zoom-fit" @click="reset">Fit</button>
    </div>
  </div>
</template>
