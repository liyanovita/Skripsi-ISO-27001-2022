// Rasterize every page of a PDF to PNG images, entirely via mupdf's WASM build —
// no Poppler/Ghostscript system install required.
//
// Usage: node rasterize-pdf.mjs <input.pdf> <output_prefix>
// Produces: <output_prefix>-1.png, <output_prefix>-2.png, ...

import fs from 'fs';
import * as mupdf from 'mupdf';

const [, , inputPath, outputPrefix] = process.argv;

if (!inputPath || !outputPrefix) {
  console.error('Usage: node rasterize-pdf.mjs <input.pdf> <output_prefix>');
  process.exit(1);
}

const DPI = 150;

try {
  const bytes = fs.readFileSync(inputPath);
  const doc = mupdf.Document.openDocument(bytes, 'application/pdf');
  const pageCount = doc.countPages();

  if (pageCount === 0) {
    console.error('No pages found in PDF.');
    process.exit(1);
  }

  const zoom = DPI / 72;
  const matrix = mupdf.Matrix.scale(zoom, zoom);

  for (let i = 0; i < pageCount; i++) {
    const page = doc.loadPage(i);
    const pixmap = page.toPixmap(matrix, mupdf.ColorSpace.DeviceRGB, false, true);
    const pngBuffer = pixmap.asPNG();
    const outPath = `${outputPrefix}-${i + 1}.png`;
    fs.writeFileSync(outPath, pngBuffer);
    console.log(`page ${i + 1}/${pageCount} -> ${outPath}`);
  }

  console.log(`OK: rendered ${pageCount} page(s)`);
} catch (err) {
  console.error('RASTERIZE_FAILED:', err.message || err);
  process.exit(1);
}
