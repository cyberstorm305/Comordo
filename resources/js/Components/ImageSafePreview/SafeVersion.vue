<!--
  SafeVersion.vue — the "Safe version" row under the photo (the real app's
  SafeVersionDownload). The box and Download open the trial prompt.
-->
<script setup>
import InfoTip from './InfoTip.vue'

const emit = defineEmits(['trial'])
</script>

<template>
  <div class="rounded-[10px] border border-line bg-paper px-4 py-2.5" data-testid="safe-version">
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
      <div class="flex flex-none items-center gap-1.5">
        <span class="text-[11px] font-bold uppercase leading-4 tracking-[0.04em] text-stone">Safe version</span>
        <InfoTip label="About the safe version" placement="above">
          A copy that's safe to re-publish: it hides faces without permission, resizes to at most 1200px and removes location and camera data.
        </InfoTip>
      </div>
      <button type="button" role="checkbox" aria-checked="true"
              class="flex min-w-[210px] flex-1 cursor-pointer items-center gap-2 border-0 bg-transparent p-0 text-left text-[13px] leading-5 text-navy"
              @click="emit('trial', $event.currentTarget)">
        <span class="flex h-4 w-4 flex-none items-center justify-center rounded-[4px] bg-copper-dark text-white" aria-hidden="true">
          <svg class="h-3 w-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7" /></svg>
        </span>
        <span>Censor faces without permission</span>
      </button>
      <button type="button"
              class="flex flex-none cursor-pointer items-center gap-1.5 rounded-[7px] border border-line bg-white px-3 py-1.5 text-[12px] font-semibold leading-[18px] text-navy hover:bg-shell"
              @click="emit('trial', $event.currentTarget)">
        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M10 3v10M5.5 8.5 10 13l4.5-4.5M4 17h12" />
        </svg>
        Download
      </button>
    </div>
  </div>
</template>
