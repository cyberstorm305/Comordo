import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useSite() {
  const page = usePage()
  const site = computed(() => page.props.site)
  const isComordo = computed(() => site.value === 'comordo')
  return { site, isComordo }
}
