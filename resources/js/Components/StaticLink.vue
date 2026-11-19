<script setup>
// Stands in for Inertia's Link in a static export build: a plain anchor. The Inertia-only props are accepted and ignored.
defineProps({
  href: { type: String, default: '' },
  method: { type: String, default: undefined },
  preserveScroll: { type: [Boolean, String], default: undefined },
  as: { type: String, default: undefined },
  data: { type: Object, default: undefined },
})
</script>

<template>
  <a :href="href"><slot /></a>
</template>
