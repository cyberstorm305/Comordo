<script setup>
import { useSite } from '@/site.js'
import SiteHeader from './SiteHeader.vue'
import SiteNav from './SiteNav.vue'

defineProps({
  active: { type: String, default: '' },
})
const { isComordo } = useSite()
</script>

<template>
  <SiteNav v-if="isComordo" :active="active" />
  <SiteHeader v-else />
</template>
