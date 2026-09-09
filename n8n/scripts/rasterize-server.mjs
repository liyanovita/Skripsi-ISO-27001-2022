
import http from 'http';
import * as mupdf from 'mupdf';

const PORT = process.env.RASTERIZE_PORT || 4790;
const DPI = 150;
const MAX_BYTES = 20 * 1024 * 1024;

const server = http.createServer((req, res) => {
  if (req.method === 'GET' && req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'ok' }));
    return;
  }

  if (req.method !== 'POST' || req.url !== '/rasterize') {
    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'not_found' }));
    return;
  }

  const chunks = [];
  let total = 0;
  req.on('data', (chunk) => {
    total += chunk.length;
    if (total > MAX_BYTES) {
      res.writeHead(413, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: 'file_too_large' }));
      req.destroy();
      return;
    }
    chunks.push(chunk);
  });

  req.on('end', () => {
    try {
      const pdfBytes = Buffer.concat(chunks);
      const doc = mupdf.Document.openDocument(pdfBytes, 'application/pdf');
      const pageCount = doc.countPages();

      if (pageCount === 0) {
        res.writeHead(422, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: 'no_pages_found' }));
        return;
      }

      const zoom = DPI / 72;
      const matrix = mupdf.Matrix.scale(zoom, zoom);
      const pages = [];

      for (let i = 0; i < pageCount; i++) {
        const page = doc.loadPage(i);
        const pixmap = page.toPixmap(matrix, mupdf.ColorSpace.DeviceRGB, false, true);
        const pngBuffer = Buffer.from(pixmap.asPNG());
        pages.push({ page_number: i + 1, image_base64: pngBuffer.toString('base64') });
      }

      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ pages }));
    } catch (err) {
      res.writeHead(500, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: 'rasterize_failed', message: String(err.message || err) }));
    }
  });
});

server.listen(PORT, '127.0.0.1', () => {
  console.log(`PDF rasterize server listening on http://127.0.0.1:${PORT}`);
  console.log('POST a PDF to /rasterize — leave this running while n8n needs it.');
});
