// The safeguarding checks a preview can run, and the photo's risk from them. A
// copy of the rules in ImageSafe's SafeguardingReviewService, limited to what
// the preview API returns (face boxes and confidence, the photo's size and any AI
// label in the file). The app's other checks need the page around the photo or the
// file's EXIF, so they only run in the trial. Faces start untagged (permission
// unknown); the demo tags a visitor adds feed the permission rules, as tags do in the app.

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

// The app's checks a preview can't run, by the app's key (for the explainer).
export const LOCKED_CHECKS = [
  { key: 'named_person', label: 'Name in or near the photo' },
  { key: 'full_size_original', label: 'Full-size original online' },
  { key: 'metadata_present', label: 'Location or camera data' },
  { key: 'state_of_dress', label: 'Appropriate clothing' },
]

const STATE = {
  high: { state: 'fail', icon: '✕', word: 'FLAG' },
  medium: { state: 'warn', icon: '!', word: 'CHECK' },
  low: { state: 'pass', icon: '✓', word: 'OK' },
}

// How much of the picture the largest face fills: up to 2% is a wide shot, up to 3.5% a caution, more a close-up.
function faceSizeStatus(ratio) {
  if (ratio <= 0.02) return 'low'
  return ratio <= 0.035 ? 'medium' : 'high'
}

// A face clear enough to recognise: detected with confidence and at least 48px across (the app's
// clearFaceCount). Previews from before the API returned confidence count on size alone.
const isClear = (f) => (f.confidence ?? 1) >= 0.85 && f.w >= 48 && f.h >= 48

// "Made or edited with AI", from the label the preview API found in the file (the app's
// aiProvenanceItem). Made with AI is a flag, edited a check; no label passes, but proves nothing.
// The label says what was found, so a pass never reads as "Made with AI · OK".
const AI_SOURCE = {
  c2pa: 'Its Content Credentials (C2PA) say',
  iptc: 'Its IPTC metadata says',
  png: 'It carries AI image-generation settings, which say',
}
function aiCheck(ai) {
  if (!ai || !['generated', 'edited'].includes(ai.kind)) {
    return { status: 'low', label: 'No AI labels found', note: 'No AI labels found in the file. These labels are often lost when an image is re-saved, so this doesn’t prove it wasn’t made or edited with AI.' }
  }
  const how = ai.kind === 'generated' ? 'made with AI' : 'edited with AI'
  return {
    status: ai.kind === 'generated' ? 'high' : 'medium',
    label: ai.kind === 'generated' ? 'Made with AI' : 'Edited with AI',
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
  const clear = (photo.faces || []).filter(isClear).length
  const statuses = {
    // A clearly visible face can be recognised: one alone is flagged, clear faces in a group are a caution.
    face_visibility: clear === 0 ? 'low' : count === 1 ? 'high' : 'medium',
    close_up_vs_wide_angle: faceSizeStatus(ratio),
    group_activity_composition: count === 1 && clear === 1 ? 'high' : 'low',
    image_resolution: longEdge > 2000 ? 'high' : longEdge >= 1400 ? 'medium' : 'low',
  }
  // Previews from before the API returned AI labels have no "ai" field: the check is skipped.
  const ai = 'ai' in photo ? aiCheck(photo.ai) : null
  if (ai) statuses.ai_provenance = ai.status
  const notes = ai ? { ai_provenance: ai.note } : {}
  const labels = {
    face_visibility: 'Recognisable faces',
    close_up_vs_wide_angle: 'Close-up of a child',
    group_activity_composition: 'Single child in shot',
    image_resolution: 'High-resolution file',
    ai_provenance: ai?.label,
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
