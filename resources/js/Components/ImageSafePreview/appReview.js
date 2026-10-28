// The ImageSafe app's review display rules (its resources/js/lib/review.js), imported from
// ImageSafe at /api/v1/review.js, so the preview shows checks, risk, permission, face tags and
// decisions exactly as the app does and keeps no copy of them. The check results and the risk
// behind them come from the preview API (the app's SafeguardingReviewService).
import { shallowRef } from 'vue'
import { imagesafeBase } from '@/imagesafe.js'

// The module once loaded: appReview.value.checkRows(image) and so on.
export const appReview = shallowRef(null)

let request = null
export function loadAppReview() {
  request ??= import(/* @vite-ignore */ `${imagesafeBase}/api/v1/review.js`)
    .then((module) => { appReview.value = module; return module })
    .catch((e) => { request = null; throw e }) // a failure can be tried again
  return request
}

// The API's risk for each permission state, by the app's permission verdict.
const RISK_FOR = { YES: 'clear', UNKNOWN: 'unknown', NO: 'refused' }

/**
 * A preview photo, with the people the visitor has demo-tagged on its faces, in the shape the
 * app's functions take: faces with [left, top, right, bottom] boxes and their known person, and
 * the API's checks with the overall risk for the resulting permission.
 *
 * @param photo  a photo from the preview API: { width, height, faces: [{ x, y, w, h }], checks, risk }
 * @param people the demo-tagged person on each face, or null: [{ name, permission: granted | pending | denied }]
 */
export function toAppImage(photo, people) {
  const image = {
    width: photo.width,
    height: photo.height,
    faces: (photo.faces || []).map((f, n) => ({
      index: n + 1,
      box: [f.x, f.y, f.x + f.w, f.y + f.h],
      known_person: people[n] ? { name: people[n].name, permission_status: people[n].permission } : null,
    })),
    safeguarding_review: { items: photo.checks || [] },
  }
  // Previews from before the API returned risks fall back to medium until they expire.
  image.safeguarding_review.overall = photo.risk?.[RISK_FOR[appReview.value.permissionVerdict(image)]] ?? 'medium'

  return image
}

// The queue filters, as the app's Dashboard: all, a risk level, or "noperm" (someone refused).
export function matchesFilter(image, filter) {
  if (filter === 'all') return true
  const app = appReview.value
  return filter === 'noperm' ? app.permissionVerdict(image) === 'NO' : app.riskOf(image) === filter
}
