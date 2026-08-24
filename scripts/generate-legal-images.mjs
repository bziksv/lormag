/**
 * PNG из standalone legal-html (без шапки/подвала сайта).
 * Usage: npm run legal:images
 */

import { chromium } from 'playwright';
import { mkdirSync, readFileSync, writeFileSync } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '..');
const htmlDir = path.join(repoRoot, 'legal-html');
const uploadDir = path.join(repoRoot, 'upload');
const publicBase = 'https://lormag.ru';

const documents = JSON.parse(
  readFileSync(path.join(__dirname, 'legal-assets.json'), 'utf8')
);

mkdirSync(uploadDir, { recursive: true });

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1200, height: 900 },
  deviceScaleFactor: 2,
});
const page = await context.newPage();

for (const doc of documents) {
  const htmlPath = path.join(htmlDir, doc.html);
  const pngPath = path.join(uploadDir, doc.png);
  const fileUrl = `file://${htmlPath}`;

  await page.goto(fileUrl, { waitUntil: 'networkidle' });
  await page.screenshot({
    path: pngPath,
    fullPage: true,
    type: 'png',
  });

  console.log(`PNG: ${pngPath}`);
}

await browser.close();

const links = {
  generatedAt: new Date().toISOString(),
  documents: documents.map((doc) => ({
    key: doc.key,
    title: doc.title,
    pageUrl: `${publicBase}${doc.pageUrl}`,
    imageUrl: `${publicBase}/upload/${doc.png}`,
    localImage: path.join(uploadDir, doc.png),
  })),
};

const linksPath = path.join(htmlDir, 'links.json');
writeFileSync(linksPath, JSON.stringify(links, null, 2) + '\n');
console.log(`Links: ${linksPath}`);
console.log('Done.');
