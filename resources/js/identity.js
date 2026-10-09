// Comordo Identity base URL. Set VITE_IDENTITY_URL in .env for local/staging; defaults to production.
export const identityBase = (import.meta.env.VITE_IDENTITY_URL || 'https://identity.comordo.com').replace(/\/$/, '')
