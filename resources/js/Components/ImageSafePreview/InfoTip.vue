<!--
  InfoTip.vue — the small ⓘ with a navy pop-over (the real app's InfoTip +
  InfoPopover). Opens on hover or keyboard focus and toggles on tap, so it
  also works on touch screens. The default slot is the text.
-->
<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'

defineProps({
  label: { type: String, required: true },
  align: { type: String, default: 'left' },
  // "above" for triggers near the bottom of a clipped box.
  placement: { type: String, default: 'below' },
  // "light" for a dark background, such as a pill on the photo.
  tone: { type: String, default: 'default' },
})

const open = ref(false)
const root = ref(null)

function closeOnOutside(e) {
  if (root.value && !root.value.contains(e.target)) open.value = false
}
watch(open, (v) => {
  if (v) document.addEventListener('pointerdown', closeOnOutside)
  else document.removeEventListener('pointerdown', closeOnOutside)
})
onBeforeUnmount(() => document.removeEventListener('pointerdown', closeOnOutside))
</script>

<template>
  <span ref="root" class="relative inline-flex" @mouseenter="open = true" @mouseleave="open = false" @click.stop @focusout="open = false">
    <button type="button" class="inline-flex cursor-pointer items-center border-0 bg-transparent p-0" :aria-label="label" :aria-expanded="open"
            @click="open = !open" @focus="open = true" @keydown.escape="open = false">
      <span class="flex h-4 w-4 flex-none items-center justify-center rounded-full border-[1.5px] font-serif text-[10px] font-bold italic leading-none"
            :class="tone === 'light' ? 'border-white/60 text-white hover:border-white' : 'border-edge text-stone hover:border-copper hover:text-copper-dark'">i</span>
    </button>
    <span v-if="open" role="tooltip" class="absolute z-40"
          :class="[align === 'right' ? 'right-0' : 'left-0', placement === 'above' ? 'bottom-full pb-1.5' : 'top-full pt-1.5']">
      <span class="block w-max min-w-[150px] max-w-[260px] whitespace-normal rounded-[7px] bg-navy px-3 py-2 text-left text-[11px] font-normal normal-case leading-[1.45] tracking-normal text-white shadow-isp-pop">
        <slot />
      </span>
    </span>
  </span>
</template>
