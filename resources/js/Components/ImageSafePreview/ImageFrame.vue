<!--
  ImageFrame.vue — the photo with its detected-face boxes (the real app's queue
  frame and ZoomableImage). The frame is 16:9 on small screens and takes the
  height the controls leave from lg up. The photo is fitted inside it, and the
  boxes sit on a layer the exact size of the photo, so they are placed in
  percentages of its natural width and height. The zoom controls open the
  trial prompt.
-->
<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import InfoTip from './InfoTip.vue'
import { looksWrong } from './imageCheck.js'

const props = defineProps({
  photo: { type: Object, required: true }, // { image_url, width, height, faces: [{x,y,w,h}] }
  alt: { type: String, default: '' },
})
const emit = defineEmits(['tag', 'zoom'])

const broken = ref(false)
const loaded = ref(false)
watch(() => props.photo.image_url, () => { broken.value = false; loaded.value = false })

function onLoad(e) {
  if (looksWrong(e.target, props.photo.width, props.photo.height)) broken.value = true
  else loaded.value = true
}

// The frame's size, so the photo layer can be fitted inside it.
const frame = ref(null)
const size = ref({ w: 0, h: 0 })
let observer = null
onMounted(() => {
  observer = new ResizeObserver(([entry]) => { size.value = { w: entry.contentRect.width, h: entry.contentRect.height } })
  observer.observe(frame.value)
})
onBeforeUnmount(() => observer?.disconnect())

const PLUM = '#5E5170' // the app's "Unknown person" colour
const faces = computed(() => props.photo.faces || [])
const w = computed(() => props.photo.width || 16)
const h = computed(() => props.photo.height || 9)
const layerStyle = computed(() => {
  const { w: fw, h: fh } = size.value
  if (!fw || !fh) return { aspectRatio: `${w.value} / ${h.value}`, width: '100%' }
  const scale = Math.min(fw / w.value, fh / h.value)
  return { width: `${w.value * scale}px`, height: `${h.value * scale}px` }
})
// Busy photos show their "Unknown" tags on hover only, as in the app.
const showTags = computed(() => faces.value.length <= 8)
function box(f) {
  return {
    left: `${(f.x / w.value) * 100}%`,
    top: `${(f.y / h.value) * 100}%`,
    width: `${(f.w / w.value) * 100}%`,
    height: `${(f.h / h.value) * 100}%`,
    border: `2px solid ${PLUM}`,
    borderRadius: '4px',
    boxShadow: '0 0 0 2px rgba(255,255,255,.55)',
  }
}
// The tag sits above the box, or below it when the box is near the top.
const nearTop = (f) => f.y / h.value < 0.08
</script>

<template>
  <div ref="frame" class="relative isolate flex aspect-video w-full items-center justify-center overflow-hidden rounded-[10px] border border-edge bg-paper lg:aspect-auto lg:min-h-[240px] lg:flex-1" data-testid="image-frame">
    <div v-if="!broken" class="relative flex-none" :class="loaded ? '' : 'img-ph'" :style="layerStyle">
      <img :src="photo.image_url" :alt="alt" referrerpolicy="no-referrer"
           class="pointer-events-none block h-full w-full object-fill"
           @load="onLoad" @error="broken = true">
      <template v-if="loaded">
        <button v-for="(f, i) in faces" :key="i" type="button"
                class="group absolute z-20 cursor-pointer bg-transparent p-0 text-left"
                :style="box(f)" :aria-label="`Tag face ${i + 1}`"
                data-testid="face-box"
                @click="emit('tag', $event.currentTarget)">
          <span class="pointer-events-none absolute left-0 whitespace-nowrap rounded-[4px] px-1 py-px text-[10px] font-semibold leading-[14px] text-white"
                :class="[nearTop(f) ? 'top-full mt-0.5' : 'bottom-full mb-0.5', showTags ? '' : 'hidden group-hover:block']"
                :style="{ background: PLUM }">Unknown</span>
        </button>
      </template>
    </div>
    <div v-else class="img-ph flex h-full w-full items-center justify-center" data-testid="photo-placeholder">
      <span class="rounded-full bg-white/80 px-3 py-1.5 text-[12px] font-medium text-slate">This photo couldn't be shown here.</span>
    </div>
    <div class="absolute bottom-3 left-3 z-30 flex items-center gap-1.5 rounded-[7px] bg-navy/[.78] px-2.5 py-1.5 text-[12px] font-medium leading-[18px] text-white backdrop-blur-[2px]" data-testid="faces-detected">
      {{ faces.length }} {{ faces.length === 1 ? 'face' : 'faces' }}
      <InfoTip label="Using the photo" placement="above" tone="light">
        <template v-if="faces.length">Click a face to tag it. </template>Your trial lets you zoom in, drag the photo around and jump to any face.
      </InfoTip>
    </div>
    <div class="absolute bottom-3 right-3 z-30 flex items-center gap-0.5 rounded-[7px] bg-navy/[.78] p-0.5 text-white backdrop-blur-[2px]">
      <button type="button" class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-[4px] border-0 bg-transparent text-[14px] text-white hover:bg-white/15" aria-label="Zoom out" @click="emit('zoom', $event.currentTarget)">−</button>
      <span class="w-11 text-center text-[11px] tabular-nums">100%</span>
      <button type="button" class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-[4px] border-0 bg-transparent text-[14px] text-white hover:bg-white/15" aria-label="Zoom in" @click="emit('zoom', $event.currentTarget)">+</button>
      <button type="button" class="ml-0.5 cursor-pointer rounded-[4px] border-0 bg-transparent px-2 py-1 text-[11px] font-semibold text-white hover:bg-white/15" @click="emit('zoom', $event.currentTarget)">Fit</button>
    </div>
  </div>
</template>
