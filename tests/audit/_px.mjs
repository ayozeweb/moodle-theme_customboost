import {chromium} from 'playwright';
import {PNG} from 'pngjs';
const WHITE = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1100"><rect width="100%" height="100%" fill="#f4f1ea"/></svg>');
const lum = (r,g,b) => { const f = c => { c/=255; return c <= .03928 ? c/12.92 : Math.pow((c+.055)/1.055, 2.4); }; return .2126*f(r)+.7152*f(g)+.0722*f(b); };
const ratio = (a, b) => (Math.max(a,b)+.05)/(Math.min(a,b)+.05);
const b = await chromium.launch();
for (const layout of ['left', 'cover']) {
  for (const w of [360, 1280]) {
    for (const img of ['default', 'white']) {
      const p = await (await b.newContext({viewport:{width:w,height:800}, reducedMotion:'reduce'})).newPage();
      await p.goto('http://localhost:8080/login/index.php', {waitUntil:'networkidle'});
      await p.evaluate(([l, src]) => { const root=document.querySelector('[class*="ct-login-layout-"]'); root.className = root.className.replace(/ct-login-layout-\w+/, 'ct-login-layout-'+l); if (src) { const im=document.querySelector('.ct-login-image'); im.removeAttribute('srcset'); im.src = src; } }, [layout, img === 'white' ? WHITE : null]);
      await p.waitForTimeout(500);
      const targets = await p.evaluate(() => [...document.querySelectorAll('.ct-login-title, .ct-login-tagline, .ct-login-brand')].filter(e=>e.getClientRects().length).map(e => { const tn=[...e.querySelectorAll('*'), e].flatMap(n=>[...n.childNodes]).find(n=>n.nodeType===3&&n.textContent.trim()); const rg=document.createRange(); rg.selectNodeContents(tn); const r=rg.getBoundingClientRect(); const c=getComputedStyle(e).color; return {cls: e.className, x:r.left, y:r.top, w:r.width, h:r.height, color:c}; }));
      // Hide the text, keep everything else, and read what is behind it.
      await p.addStyleTag({content: '.ct-login-title, .ct-login-tagline, .ct-login-brand { color: transparent !important; text-shadow: none !important; } .ct-login-brand * { visibility: hidden !important; }'});
      const png = PNG.sync.read(await p.screenshot());
      const out = [];
      for (const t of targets) {
        const [fr, fg, fb] = t.color.match(/\d+/g).map(Number);
        const fl = lum(fr, fg, fb);
        let worst = 99;
        for (let y = Math.max(0, Math.floor(t.y)); y < Math.min(png.height, t.y + t.h); y += 2) {
          for (let x = Math.max(0, Math.floor(t.x)); x < Math.min(png.width, t.x + t.w); x += 2) {
            const i = (y * png.width + x) * 4;
            worst = Math.min(worst, ratio(fl, lum(png.data[i], png.data[i+1], png.data[i+2])));
          }
        }
        out.push(`${t.cls.replace('ct-login-','')} ${worst.toFixed(2)}`);
      }
      console.log(layout, w, img, out.join(' | '));
      await p.close();
    }
  }
}
await b.close();
