import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useSite() {
  const page = usePage()
  const site = computed(() => page.props.site)
  const isComordo = computed(() => site.value === 'comordo')
  return { site, isComordo }
}

// ImageSafe's own marketing site (imagesafe.co.uk in production). deploy.sh bakes each target's
// URL in at build time (IMAGESAFE_SITE_URL, else MARKETING_URL), so a staging build links to
// staging and a comordo.com build links to imagesafe.co.uk rather than to itself.
export const imagesafeSiteUrl = (import.meta.env.VITE_IMAGESAFE_SITE_URL || 'https://imagesafe.co.uk').replace(/\/$/, '')

// The Comordo corporate site (comordo.com), linked from ImageSafe's site. deploy.sh bakes it in
// (COMORDO_SITE_URL, default https://comordo.com).
export const comordoSiteUrl = (import.meta.env.VITE_COMORDO_SITE_URL || 'https://comordo.com').replace(/\/$/, '')

// True in a build made with VITE_STATIC_EXPORT=1 (the standalone comordo.com export, served without Laravel):
// links are plain anchors and forms post JSON to the small PHP endpoints (/contact.php, /subscribe.php).
export const isStaticExport = import.meta.env.VITE_STATIC_EXPORT === '1'
