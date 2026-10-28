// The safeguarding checks a preview can run, and the photo's risk from them. A
// copy of the rules in ImageSafe's SafeguardingReviewService, limited to what
// the preview API returns (face boxes and the photo's size). Face visibility
// needs detection confidence and metadata needs the file's EXIF, so those two
// only run in the trial. Every face is untagged, so permission is unknown.

export const RISK = {
  high: { word: 'HIGH', long: 'High', solid: '#B23A2E', tint: '#FCE8E6', line: '#F3C9C5' },
  medium: { word: 'MED', long: 'Medium', solid: '#B4702F', tint: '#F8F4EC', line: '#E7C9A8' },
  low: { word: 'LOW', long: 'Low', solid: '#2F7D5B', tint: '#E7F4EC', line: '#CFE6D6' },
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

function largestFaceRatio(photo) {
  const area = (photo.width || 0) * (photo.height || 0)
  if (!area) return 0
  return Math.max(0, ...(photo.faces || []).map((f) => (Math.max(0, f.w) * Math.max(0, f.h)) / area))
}

// { overall: 'high' | 'medium' | 'low', checks: [{ key, label, state, icon, word }] }
// with "Permission to use" first, then flag → check → ok, as the app lists them.
export function review(photo) {
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
  const labels = {
    close_up_vs_wide_angle: 'Close-up vs wide-angle',
    individual_pupil_focus: 'Individual pupil focus',
    group_activity_composition: 'Group / activity-led composition',
    image_resolution: 'Image resolution',
    easy_to_crop: 'Easy to crop / isolate a child',
  }

  const values = Object.values(statuses)
  const cautions = values.filter((s) => s === 'medium').length
  // Any flag or four cautions is high; otherwise untagged faces (unknown permission) make it medium.
  const overall = values.includes('high') || cautions >= 4 ? 'high' : 'medium'

  const order = { fail: 0, warn: 1, pass: 2 }
  const checks = Object.entries(statuses)
    .map(([key, status]) => ({ key, label: labels[key], ...STATE[status] }))
    .sort((a, b) => order[a.state] - order[b.state])

  return { overall, checks: [{ key: 'permission', label: 'Permission to use', ...STATE.medium }, ...checks] }
}

// The decision the app pre-selects for a photo of this risk.
export function recommended(overall) {
  return overall === 'high' ? 'remove' : overall === 'medium' ? 'fix' : 'keep'
}
