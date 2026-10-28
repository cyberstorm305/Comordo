// The safeguarding checks a preview can run, and the photo's risk from them. A
// copy of the rules in ImageSafe's SafeguardingReviewService, limited to what
// the preview API returns (face boxes, the photo's size and any AI label in the
// file). Face visibility
// needs detection confidence and metadata needs the file's EXIF, so those two
// only run in the trial. Faces start untagged (permission unknown); the demo
// tags a visitor adds feed the permission rules, as tags do in the app.

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

export const LOCKED_CHECKS = ['Face visibility', 'Metadata present']

const STATE = {
  high: { state: 'fail', icon: '✕', word: 'FLAG' },
  medium: { state: 'warn', icon: '!', word: 'CHECK' },
  low: { state: 'pass', icon: '✓', word: 'OK' },
}

// Over 2% of the frame is high, except a 3–3.5% band that is medium (as in the app).
function faceSizeStatus(ratio) {
  if (ratio <= 0.02) return 'low'
  if (ratio >= 0.03 && ratio <= 0.035) return 'medium'
  return 'high'
}

// "Made or edited with AI", from the label the preview API found in the file (the app's
// aiProvenanceItem). Made with AI is a flag, edited a check; no label passes, but proves nothing.
const AI_SOURCE = {
  c2pa: 'Its Content Credentials (C2PA) say',
  iptc: 'Its IPTC metadata says',
  png: 'It carries AI image-generation settings, which say',
}
function aiCheck(ai) {
  if (!ai || !['generated', 'edited'].includes(ai.kind)) {
    return { status: 'low', note: 'No AI labels found in the file. These labels are often lost when an image is re-saved, so this doesn’t prove it wasn’t made or edited with AI.' }
  }
  const how = ai.kind === 'generated' ? 'made with AI' : 'edited with AI'
  return {
    status: ai.kind === 'generated' ? 'high' : 'medium',
    note: `${AI_SOURCE[ai.source] || 'Its metadata says'} it was ${how}${ai.tool ? ` (${ai.tool})` : ''}.`,
  }
}

function largestFaceRatio(photo) {
  const area = (photo.width || 0) * (photo.height || 0)
  if (!area) return 0
  return Math.max(0, ...(photo.faces || []).map((f) => (Math.max(0, f.w) * Math.max(0, f.h)) / area))
}

// { overall: 'high' | 'medium' | 'low', verdict, checks: [{ key, label, state, icon, word, note? }] }
// with "Permission to use" first, then flag → check → ok, as the app lists them.
// keys: each face's PERMISSION key, in face order.
export function review(photo, keys) {
  const count = (photo.faces || []).length
  const ratio = largestFaceRatio(photo)
  const longEdge = Math.max(photo.width || 0, photo.height || 0)
  const statuses = {
    close_up_vs_wide_angle: faceSizeStatus(ratio),
    individual_pupil_focus: faceSizeStatus(ratio),
    group_activity_composition: count === 0 ? 'low' : count >= 2 ? 'low' : 'high',
    image_resolution: longEdge > 2000 ? 'high' : longEdge >= 1400 ? 'medium' : 'low',
    easy_to_crop: faceSizeStatus(ratio),
  }
  // Previews from before the API returned AI labels have no "ai" field: the check is skipped.
  const ai = 'ai' in photo ? aiCheck(photo.ai) : null
  if (ai) statuses.ai_provenance = ai.status
  const notes = ai ? { ai_provenance: ai.note } : {}
  const labels = {
    close_up_vs_wide_angle: 'Close-up vs wide-angle',
    individual_pupil_focus: 'Individual pupil focus',
    group_activity_composition: 'Group / activity-led composition',
    image_resolution: 'Image resolution',
    easy_to_crop: 'Easy to crop / isolate a child',
    ai_provenance: 'Made or edited with AI',
  }

  const verdict = permissionVerdict(keys)
  const values = Object.values(statuses)
  const cautions = values.filter((s) => s === 'medium').length
  // A refusal, any flag or four cautions is high; other cautions or unknown permission are medium.
  const overall = verdict === 'NO' || values.includes('high') || cautions >= 4
    ? 'high'
    : cautions > 0 || verdict === 'UNKNOWN' ? 'medium' : 'low'

  const permission = {
    key: 'permission',
    label: 'Permission to use',
    ...(verdict === 'NO' ? STATE.high : verdict === 'UNKNOWN' ? STATE.medium : STATE.low),
  }
  const checks = Object.entries(statuses).map(([key, status]) => ({ key, label: labels[key], ...STATE[status], note: notes[key] }))

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
