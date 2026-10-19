import { ref, onBeforeUnmount } from 'vue'
import { imagesafeBase } from '@/imagesafe.js'

const POLL_MS = 2000
const STALL_MS = 90000
const FETCH_TIMEOUT_MS = 15000
const MAX_POLL_FAILURES = 3 // consecutive network/timeouts before giving up
const json = { 'Content-Type': 'application/json', Accept: 'application/json' }

async function fetchWithTimeout(url, options = {}) {
  const controller = new AbortController()
  const t = setTimeout(() => controller.abort(), FETCH_TIMEOUT_MS)
  try {
    return await fetch(url, { ...options, signal: controller.signal })
  } finally {
    clearTimeout(t)
  }
}

// Starts a preview. Resolves to { id } or { error: 'blocked' | 'limited' | 'busy' }.
export async function createPreview(url) {
  try {
    const res = await fetchWithTimeout(`${imagesafeBase}/api/v1/previews`, {
      method: 'POST',
      headers: json,
      body: JSON.stringify({ url, confirmed: true }),
    })
    if (res.status === 429) return { error: 'limited' }
    if (res.status === 422) return { error: 'blocked' }
    if (!res.ok) return { error: 'busy' }
    const { id } = await res.json()
    return id ? { id } : { error: 'busy' }
  } catch {
    return { error: 'busy' }
  }
}

export function usePreview() {
  const state = ref('queued') // queued | scanning | done | error
  const progress = ref({ pages: 0, images: 0, found: 0 })
  const result = ref({ pages_checked: 0, images_checked: 0, found: [] })
  const error = ref(null) // unreachable | busy | slow | expired

  let timer = null
  let run = 0 // bumps on watch/stop so stale responses are ignored

  function stop() {
    run++
    clearTimeout(timer)
    timer = null
  }

  function fail(code) {
    stop()
    error.value = code
    state.value = 'error'
  }

  function watch(id) {
    stop()
    const mine = run
    state.value = 'queued'
    error.value = null

    let last = ''
    let lastChange = Date.now()
    let failures = 0

    const poll = async () => {
      try {
        const res = await fetchWithTimeout(`${imagesafeBase}/api/v1/previews/${encodeURIComponent(id)}`, { headers: { Accept: 'application/json' } })
        if (mine !== run) return
        if (res.status === 404) return fail('expired')
        if (!res.ok) return fail('busy')
        const d = await res.json()
        if (mine !== run) return
        failures = 0

        const found = d.found || []
        progress.value = { pages: d.pages_checked || 0, images: d.images_checked || 0, found: found.length }
        result.value = { pages_checked: d.pages_checked || 0, images_checked: d.images_checked || 0, found }

        if (d.status === 'done') {
          stop()
          state.value = 'done'
          return
        }
        if (d.status === 'failed') {
          return fail(['unreachable', 'slow', 'busy'].includes(d.message) ? d.message : 'busy')
        }

        if (d.status === 'scanning') {
          // The stall clock only runs once the scan has started.
          if (state.value !== 'scanning') { state.value = 'scanning'; lastChange = Date.now() }
          const sig = `${d.pages_checked}:${d.images_checked}:${found.length}`
          if (sig !== last) { last = sig; lastChange = Date.now() }
          else if (Date.now() - lastChange > STALL_MS) return fail('busy')
        } else {
          state.value = 'queued'
        }
      } catch {
        if (mine !== run) return
        if (++failures >= MAX_POLL_FAILURES) return fail('busy')
      }
      timer = setTimeout(poll, POLL_MS)
    }
    poll()
  }

  onBeforeUnmount(stop)

  return { state, progress, result, error, watch, stop, create: createPreview }
}
