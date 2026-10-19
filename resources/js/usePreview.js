import { ref, onBeforeUnmount } from 'vue'
import { imagesafeBase } from '@/imagesafe.js'

const POLL_MS = 2000
const STALL_MS = 90000
const headers = { 'Content-Type': 'application/json', Accept: 'application/json' }

export function usePreview() {
  const state = ref('idle') // idle | scanning | done | error
  const progress = ref({ pages: 0, images: 0, found: 0 })
  const result = ref(null)
  const error = ref(null) // blocked | busy | limited | unreachable

  let timer = null
  let run = 0 // bumps on start/cancel so stale responses are ignored

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

  async function start(url) {
    stop()
    const mine = run
    state.value = 'scanning'
    error.value = null
    result.value = null
    progress.value = { pages: 0, images: 0, found: 0 }

    let id
    try {
      const res = await fetch(`${imagesafeBase}/api/v1/previews`, {
        method: 'POST',
        headers,
        body: JSON.stringify({ url, confirmed: true }),
      })
      if (mine !== run) return
      if (res.status === 429) return fail('limited')
      if (res.status === 422) return fail('blocked')
      if (!res.ok) return fail('busy')
      id = (await res.json()).id
    } catch {
      if (mine === run) fail('busy')
      return
    }

    let last = ''
    let lastChange = Date.now()

    const poll = async () => {
      try {
        const res = await fetch(`${imagesafeBase}/api/v1/previews/${id}`, { headers: { Accept: 'application/json' } })
        if (mine !== run) return
        if (!res.ok) return fail('busy')
        const d = await res.json()
        if (mine !== run) return

        const found = d.found || []
        progress.value = { pages: d.pages_checked || 0, images: d.images_checked || 0, found: found.length }

        if (d.status === 'done') {
          result.value = { pages_checked: d.pages_checked || 0, images_checked: d.images_checked || 0, found }
          clearTimeout(timer)
          state.value = 'done'
          return
        }
        if (d.status === 'failed') return fail(d.message === 'unreachable' ? 'unreachable' : 'busy')

        const sig = `${d.pages_checked}:${d.images_checked}`
        if (sig !== last) { last = sig; lastChange = Date.now() }
        else if (Date.now() - lastChange > STALL_MS) return fail('busy')
      } catch {
        if (mine === run) return fail('busy')
        return
      }
      timer = setTimeout(poll, POLL_MS)
    }
    poll()
  }

  function cancel() {
    stop()
    state.value = 'idle'
  }

  onBeforeUnmount(stop)

  return { state, progress, result, error, start, cancel }
}
