<!--
  PeoplePanel.vue — "People in this image" rows (Designs/Imagesafe
  components/PeopleList.vue, real app Dashboard.vue). Every face is an unknown
  person in a preview; tagging opens the trial prompt.
-->
<script setup>
defineProps({ count: { type: Number, default: 0 } })
const emit = defineEmits(['tag'])
</script>

<template>
  <div class="flex flex-col gap-0.5" data-testid="people">
    <button v-for="n in count" :key="n" type="button"
            class="flex w-full cursor-pointer items-center gap-[9px] rounded-lg border-0 bg-transparent px-2.5 py-1.5 text-left hover:bg-tint-hover"
            title="Tag this face"
            @click="emit('tag', $event.currentTarget)">
      <div class="min-w-0 flex-1">
        <div class="truncate text-[12.5px] text-mist">Untagged face</div>
      </div>
      <span class="flex-none whitespace-nowrap rounded-[10px] px-2 py-0.5 text-[10px] font-semibold" style="background:#EFEBF3; color:#5E5170">Unknown person</span>
    </button>
    <p v-if="!count" class="m-0 px-2.5 py-1.5 text-xs text-slate">No faces detected yet.</p>
  </div>
</template>
