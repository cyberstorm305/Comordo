import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useSite() {
  const page = usePage()
  const site = computed(() => page.props.site)
  const isComordo = computed(() => site.value === 'comordo')
  return { site, isComordo }
}

// ImageSafe's own marketing site (imagesafe.co.uk in production). deploy.sh bakes each target's
// URL in at build time, so a staging build links to staging rather than to the live site.
export const imagesafeSiteUrl = (import.meta.env.VITE_MARKETING_URL || 'https://imagesafe.co.uk').replace(/\/$/, '')
