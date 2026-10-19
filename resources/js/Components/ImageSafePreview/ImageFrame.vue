<!--
  ImageFrame.vue — the photo with its detected-face boxes (Designs/Imagesafe
  components/ImageFrame.vue + the real app's queue frame). The photo is fitted
  inside a 16:9 frame; the boxes sit on a layer the exact size of the photo, so
  they are placed in percentages of its natural width and height.
-->
<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  photo: { type: Object, required: true }, // { image_url, width, height, faces: [{x,y,w,h}] }
  alt: { type: String, default: '' },
})
const emit = defineEmits(['tag'])

const broken = ref(false)
const loaded = ref(false)
watch(() => props.photo.image_url, () => { broken.value = false; loaded.value = false })

const UNKNOWN_LINE = '#8A7A9E'
const faces = computed(() => props.photo.faces || [])
const w = computed(() => props.photo.width || 16)
const h = computed(() => props.photo.height || 9)
// Wider than 16:9 → fill the width, otherwise fill the height.
const layerStyle = computed(() => ({
  aspectRatio: `${w.value} / ${h.value}`,
  ...(w.value / h.value >= 16 / 9 ? { width: '100%' } : { height: '100%' }),
}))
function box(f) {
  return {
    left: `${(f.x / w.value) * 100}%`,
    top: `${(f.y / h.value) * 100}%`,
    width: `${(f.w / w.value) * 100}%`,
    height: `${(f.h / h.value) * 100}%`,
    border: `2px solid ${UNKNOWN_LINE}`,
    borderRadius: '4px',
    boxShadow: '0 0 0 2px rgba(255,255,255,.55)',
  }
}
</script>

<template>
  <div class="relative isolate flex aspect-video w-full items-center justify-center overflow-hidden rounded-[10px] border border-[#CFC8B8] bg-paper" data-testid="image-frame">
    <div v-if="!broken" class="relative max-h-full max-w-full" :class="loaded ? '' : 'img-ph'" :style="layerStyle">
      <img :src="photo.image_url" :alt="alt" referrerpolicy="no-referrer"
           class="pointer-events-none block h-full w-full object-fill"
           @load="loaded = true" @error="broken = true">
      <button v-for="(f, i) in faces" :key="i" type="button"
              class="absolute z-20 cursor-pointer bg-transparent p-0"
              :style="box(f)" :aria-label="`Tag face ${i + 1}`"
              data-testid="face-box"
              @click="emit('tag', $event.currentTarget)">
        <span class="pointer-events-none absolute left-0 top-0 -translate-y-[120%] whitespace-nowrap rounded-[3px] px-[5px] py-px text-[9px] font-semibold text-white"
              :style="{ background: UNKNOWN_LINE }">Unknown</span>
      </button>
    </div>
    <div v-else class="img-ph flex h-full w-full items-center justify-center" data-testid="photo-placeholder">
      <span class="rounded-full bg-white/80 px-3 py-1.5 text-[12px] font-medium text-slate">This photo couldn't be shown here.</span>
    </div>
    <div class="pointer-events-none absolute bottom-3 left-3 z-30 rounded-[20px] bg-navy/[.72] px-2.5 py-1 text-[11px] font-medium text-white backdrop-blur-[2px]" data-testid="faces-detected">
      {{ faces.length }} {{ faces.length === 1 ? 'face' : 'faces' }} detected<template v-if="faces.length && !broken"> · click a box to tag</template>
    </div>
  </div>
</template>
