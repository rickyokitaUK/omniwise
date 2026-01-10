const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

(async () => {
  const url = process.argv[2];
  const savePath = process.argv[3];

  if (!url || !savePath) {
    console.error('Usage: node fetch-image.js <url> <savePath>');
    process.exit(1);
  }

  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  try {
    const page = await browser.newPage();
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' +
      'AppleWebKit/537.36 (KHTML, like Gecko) ' +
      'Chrome/140.0.0.0 Safari/537.36'
    );

    // Go to the short link
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 120000 });

    // If redirected to the image directly, just download it
    const finalUrl = page.url();
    if (finalUrl.match(/\.(png|jpg|jpeg|webp)$/i)) {
      const viewSource = await page.goto(finalUrl);
      fs.writeFileSync(savePath, await viewSource.buffer());
      console.log('Saved direct image:', finalUrl);
    } else {
      // Otherwise, find the <img> and download its src
      const imgSrc = await page.$eval('img', img => img.src);
      const viewSource = await page.goto(imgSrc);
      fs.writeFileSync(savePath, await viewSource.buffer());
      console.log('Saved image from page:', imgSrc);
    }
  } catch (err) {
    console.error('Error:', err);
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
