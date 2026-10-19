// ImageSafe base URL. Set VITE_IMAGESAFE_URL in .env for local/staging; defaults to production.
export const imagesafeBase = (import.meta.env.VITE_IMAGESAFE_URL || 'https://imagesafe.comordo.com').replace(/\/$/, '')
