<!--
  DecisionPanel.vue — decision options and buttons (Designs/Imagesafe
  components/DecisionPanel.vue, compact queue variant). Shown as in the app;
  each one opens the trial prompt.
-->
<script setup>
const emit = defineEmits(['trial', 'detail', 'skip'])

const opts = [
  { key: 'remove', title: 'Remove from website', sub: 'Take it down, tick off here', num: 1 },
  { key: 'fix', title: 'Fix on site', sub: 'Crop, blur or edit caption', num: 2 },
  { key: 'keep', title: 'Keep as is', sub: '⚠ note required', num: 3 },
]
</script>

<template>
  <div class="flex flex-1 flex-col" data-testid="decision">
    <div class="mb-3 flex flex-col gap-[7px]">
      <button v-for="o in opts" :key="o.key" type="button"
              class="flex w-full cursor-pointer items-start gap-[9px] rounded-[9px] border-[1.5px] border-sand bg-white px-3 py-2.5 text-left hover:border-copper-pale"
              data-testid="decision-option"
              @click="emit('trial', $event.currentTarget)">
        <span class="mt-px h-[15px] w-[15px] flex-none rounded-full border-[1.5px] border-edge bg-white"></span>
        <div class="min-w-0 flex-1">
          <div class="text-[12.5px] font-semibold">{{ o.title }}</div>
          <div class="mt-0.5 text-[10.5px] text-stone">{{ o.sub }} · {{ o.num }}</div>
        </div>
      </button>
    </div>
    <div class="mt-auto flex flex-wrap items-center gap-[9px] sm:flex-nowrap">
      <button type="button" class="basis-full cursor-pointer sm:basis-auto sm:flex-1 rounded-lg border-0 bg-navy p-3 text-[13.5px] font-semibold text-white hover:bg-navy-light" @click="emit('trial', $event.currentTarget)">
        Record decision &amp; next ⏎</button>
      <button type="button" class="flex-1 cursor-pointer whitespace-nowrap rounded-lg border border-line bg-white px-3.5 py-3 text-[13px] font-semibold text-copper sm:flex-none hover:bg-tint-hover" @click="emit('detail', $event.currentTarget)">Full detail →</button>
      <button type="button" class="flex-1 cursor-pointer whitespace-nowrap rounded-lg border border-line bg-white px-3.5 py-3 text-[13px] font-semibold text-slate sm:flex-none hover:bg-tint-hover" @click="emit('skip', $event.currentTarget)">Skip ↓</button>
    </div>
    <div class="mt-3 text-center text-[11px] text-mist">Recording adds to this image's log — it doesn't change your website. ↑↓ move · 1–3 pick · ⏎ record &amp; advance</div>
  </div>
</template>
