// How the preview shows a photo's safeguarding checks and risk. The checks and the
// risk come from ImageSafe: its preview API runs each photo through the app's own
// SafeguardingReviewService and returns the results, so no check rules live here.
// Only permission depends on the demo tags a visitor adds, and the API gives the
// risk for each permission state to look up.

export const RISK = {
  high: { word: 'HIGH', long: 'High', solid: '#B23A2E', tint: '#FCE8E6', line: '#F3C9C5' },
  medium: { word: 'MED', long: 'Medium', solid: '#B4702F', tint: '#F8F4EC', line: '#E7C9A8' },
  low: { word: 'LOW', long: 'Low', solid: '#2F7D5B', tint: '#E7F4EC', line: '#CFE6D6' },
}

// The app's PERMISSION_META: a face's pill, box and tag colours.
export const PERMISSION = {
  approved: { label: 'Approved – web & social', bg: '#E7F4EC', fg: '#2F7D5B', line: '#2F7D5B' },
  refused: { label: 'Refused – do not use', bg: '#FCE8E6', fg: '#B23A2E', line: '#B23A2E' },
  nodata: { label: 'No perm data', bg: '#F1EAD9', fg: '#8F5722', line: '#B4702F' },
  unknown: { label: 'Unknown person', bg: '#F3EFF7', fg: '#5E5170', line: '#5E5170' },
}

// A tagged person's permission → the face's key in PERMISSION.
export function permissionKey(person) {
  if (!person) return 'unknown'
  if (person.permission === 'granted') return 'approved'
  return person.permission === 'denied' ? 'refused' : 'nodata'
}

// YES: everyone tagged and approved. NO: someone refused. UNKNOWN: untagged faces or missing data.
export function permissionVerdict(keys) {
  if (keys.includes('refused')) return 'NO'
  return keys.some((k) => k !== 'approved') ? 'UNKNOWN' : 'YES'
}

const STATE = {
  high: { state: 'fail', icon: '✕', word: 'FLAG' },
  medium: { state: 'warn', icon: '!', word: 'CHECK' },
  low: { state: 'pass', icon: '✓', word: 'OK' },
}

// The API's risk for each permission state, by permission verdict.
const RISK_FOR = { YES: 'clear', UNKNOWN: 'unknown', NO: 'refused' }

// { overall: 'high' | 'medium' | 'low', verdict, checks: [{ key, label, state, icon, word, note }] }
// photo.checks and photo.risk are the app's (see above); keys are each face's PERMISSION key.
// "Permission to use" joins them and they're ordered flag → check → ok, as the app's checkRows does.
// Previews from before the API returned checks show permission only until they expire.
export function review(photo, keys) {
  const verdict = permissionVerdict(keys)
  const overall = photo.risk?.[RISK_FOR[verdict]] ?? 'medium'

  const permission = {
    key: 'permission',
    label: 'Permission to use',
    ...(verdict === 'NO' ? STATE.high : verdict === 'UNKNOWN' ? STATE.medium : STATE.low),
  }
  const checks = (photo.checks || []).map((c) => ({ key: c.key, label: c.label, note: c.note, ...(STATE[c.status] || STATE.low) }))

  // When someone refused, the other checks don't matter and show as N/A.
  if (verdict === 'NO') {
    return { overall, verdict, checks: [permission, ...checks.map((c) => ({ ...c, state: 'na', icon: '–', word: 'N/A' }))] }
  }

  const order = { fail: 0, warn: 1, pass: 2 }
  return { overall, verdict, checks: [permission, ...checks].sort((a, b) => order[a.state] - order[b.state]) }
}

// The line under a queue row's name, as the app's permissionChip.
export function permissionChip(keys, overall) {
  const verdict = permissionVerdict(keys)
  if (verdict === 'YES') return `${RISK[overall].long.toLowerCase()} risk`
  if (verdict === 'NO') return 'No perm'
  const untagged = keys.filter((k) => k === 'unknown').length
  return untagged > 0 ? `${untagged} untagged` : 'No perm data'
}

// The queue filters: all, a risk level, or "noperm" (someone refused).
export function matches(item, filter) {
  if (filter === 'all') return true
  return filter === 'noperm' ? item.verdict === 'NO' : item.risk === filter
}

// Queue order from the app: high → medium → low, then refused → unknown → approved.
export function queueRank(overall, verdict) {
  return { high: 0, medium: 10, low: 20 }[overall] + { NO: 0, UNKNOWN: 1, YES: 2 }[verdict]
}

// The decision the app pre-selects for a photo of this risk.
export function recommended(overall, verdict) {
  if (verdict === 'NO' || overall === 'high') return 'remove'
  return overall === 'medium' ? 'fix' : 'keep'
}
