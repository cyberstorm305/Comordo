// A photo that loads but isn't the one ImageSafe analysed: a hotlink or
// lazy-load stand-in (tiny), or an image whose shape is far from the one the
// face boxes were measured on. Treat it as broken so the placeholder shows.
export function looksWrong(img, width, height) {
  const nw = img.naturalWidth
  const nh = img.naturalHeight
  if (nw <= 2 || nh <= 2) return true
  if (!width || !height) return false
  const expected = width / height
  return Math.abs(nw / nh - expected) / expected > 0.15
}
