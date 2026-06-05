// node generate-icons.mjs
import { writeFileSync } from 'fs';
import zlib from 'zlib';

function u32be(n) {
  const b = Buffer.alloc(4); b.writeUInt32BE(n); return b;
}
function crc32(buf) {
  const t = [];
  for (let i = 0; i < 256; i++) {
    let c = i;
    for (let j = 0; j < 8; j++) c = (c & 1) ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
    t[i] = c;
  }
  let crc = 0xffffffff;
  for (const b of buf) crc = t[(crc ^ b) & 0xff] ^ (crc >>> 8);
  return (crc ^ 0xffffffff) >>> 0;
}
function chunk(type, data) {
  const tb = Buffer.from(type, 'ascii');
  const db = Buffer.isBuffer(data) ? data : Buffer.from(data);
  return Buffer.concat([u32be(db.length), tb, db, u32be(crc32(Buffer.concat([tb, db])))]);
}

// ── Rasterise a path defined by filled rectangles (pixel coords) ──────────
// Each shape: { x, y, w, h, r, g, b, a }
function rasterise(size, shapes) {
  const out = [];
  for (let y = 0; y < size; y++) {
    out.push(0); // PNG filter byte
    for (let x = 0; x < size; x++) {
      let r = 0, g = 0, b = 0, a = 0;
      for (const s of shapes) {
        if (x >= s.x && x < s.x + s.w && y >= s.y && y < s.y + s.h) {
          r = s.r; g = s.g; b = s.b; a = s.a ?? 255;
        }
      }
      out.push(r, g, b, a);
    }
  }
  return out;
}

function makePNG(size) {
  const S = size;
  const f = S / 128; // scale factor relative to 128px master

  const px = (v) => Math.round(v * f);

  // Rounded square background — draw as circle-cornered rect via per-pixel check
  const radius = px(24);
  const pixels = [];

  // Indigo gradient approximation: top=#6366f1, bottom=#4f46e5
  // <  >  /  glyphs drawn as simple rectangles at 128px, scaled down

  // At 128px master:
  //   Canvas: 128×128, corner radius 24
  //   "<" glyph:  two rects forming a chevron pointing left
  //   ">" glyph:  mirrored
  //   "/" glyph:  diagonal bar

  for (let y = 0; y < S; y++) {
    pixels.push(0); // filter
    for (let x = 0; x < S; x++) {
      // Rounded square test
      const cx = Math.max(radius, Math.min(S - radius, x));
      const cy = Math.max(radius, Math.min(S - radius, y));
      const dx = x - cx, dy = y - cy;
      const inBg = dx * dx + dy * dy <= radius * radius;

      if (!inBg) { pixels.push(0, 0, 0, 0); continue; }

      // Background: indigo, slightly lighter at top
      const t = y / S;
      const bgR = Math.round(99 + (79 - 99) * t);   // 99→79
      const bgG = Math.round(102 + (70 - 102) * t);  // 102→70
      const bgB = Math.round(241 + (229 - 241) * t); // 241→229

      // Glyph pixels — defined at 128px, scaled
      // Stroke weight
      const sw = px(9);
      // Arm length of chevrons
      const arm = px(18);
      // "/" slash
      const slashX1 = px(57), slashX2 = px(71);
      const slashY1 = px(28), slashY2 = px(100);

      // "<" chevron: left half at x≈28–48
      const lx = px(28);
      const midY = px(64);
      // upper arm of "<": goes from (lx+arm, midY-arm) to (lx, midY), width sw
      // lower arm of "<": goes from (lx, midY) to (lx+arm, midY+arm), width sw
      // We draw as anti-aliased distance to line segment

      function distToSeg(px2, py2, ax, ay, bx, by) {
        const abx = bx - ax, aby = by - ay;
        const len2 = abx * abx + aby * aby;
        const t2 = Math.max(0, Math.min(1, ((px2 - ax) * abx + (py2 - ay) * aby) / len2));
        const nx = ax + t2 * abx - px2, ny = ay + t2 * aby - py2;
        return Math.sqrt(nx * nx + ny * ny);
      }

      // "<" arms
      const ltx = lx + arm, lty1 = midY - arm, lty2 = midY + arm;
      const dL1 = distToSeg(x, y, ltx, lty1, lx, midY);
      const dL2 = distToSeg(x, y, lx, midY, ltx, lty2);
      const dL = Math.min(dL1, dL2);

      // ">" arms (mirrored)
      const rx2 = px(100);
      const rtx = rx2 - arm;
      const dR1 = distToSeg(x, y, rtx, lty1, rx2, midY);
      const dR2 = distToSeg(x, y, rx2, midY, rtx, lty2);
      const dR = Math.min(dR1, dR2);

      // "/" slash: diagonal line
      const dS = distToSeg(x, y, slashX2, slashY1, slashX1, slashY2);

      const minD = Math.min(dL, dR, dS);
      const half = sw / 2;
      const alpha = Math.max(0, Math.min(1, half + 0.8 - minD));

      if (alpha > 0) {
        // White glyph blended over bg
        const a = Math.round(alpha * 255);
        pixels.push(
          Math.round(bgR + (255 - bgR) * alpha),
          Math.round(bgG + (255 - bgG) * alpha),
          Math.round(bgB + (255 - bgB) * alpha),
          255
        );
      } else {
        pixels.push(bgR, bgG, bgB, 255);
      }
    }
  }

  const raw = Buffer.from(pixels);
  const deflated = zlib.deflateSync(raw, { level: 9 });
  const sig = Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]);
  const ihdr = chunk('IHDR', Buffer.concat([u32be(S), u32be(S), Buffer.from([8, 6, 0, 0, 0])]));
  const idat = chunk('IDAT', deflated);
  const iend = chunk('IEND', Buffer.alloc(0));
  return Buffer.concat([sig, ihdr, idat, iend]);
}

for (const size of [16, 48, 128]) {
  writeFileSync(`icons/icon${size}.png`, makePNG(size));
  console.log(`icons/icon${size}.png  (${size}×${size})`);
}
