// Comordo Identity base URL. Set VITE_IDENTITY_URL in .env for local/staging; defaults to production.
export const identityBase = (import.meta.env.VITE_IDENTITY_URL || 'https://identity.comordo.com').replace(/\/$/, '')

// This is ImageSafe's marketing site. Identity is Comordo's, and ?product= tells it to
// name ImageSafe and link back here.
export const identityProduct = 'imagesafe'
export const identityHref = path => `${identityBase}${path}?product=${identityProduct}`
export const identityStartHref = identityHref('/start')
export const identityLoginHref = identityHref('/login')
