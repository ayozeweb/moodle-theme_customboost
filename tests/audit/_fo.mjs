import {chromium} from 'playwright';
const b = await chromium.launch();
for (const w of [360, 1280]) {
  const p = await (await b.newContext({viewport:{width:w,height:700}, reducedMotion:'reduce'})).newPage();
  await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
  await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
  await p.goto('http://localhost:8080/course/view.php?id=8', {waitUntil:'networkidle'});
  // Start at the end of the page and walk back.
  await p.evaluate(() => { const all=[...document.querySelectorAll('#region-main a[href], #region-main button')].filter(e=>e.getClientRects().length); all[all.length-1].focus(); });
  let hidden = 0, total = 0, example = '';
  for (let i = 0; i < 60; i++) {
    await p.keyboard.press('Shift+Tab');
    const r = await p.evaluate(() => { const e=document.activeElement; if (!e || e===document.body) return null; const r=e.getBoundingClientRect(); const h=document.querySelector('nav.navbar').getBoundingClientRect().bottom; const inDrawer=!!e.closest('.drawer,nav.navbar'); return {top:r.top, bottom:r.bottom, h, inDrawer, name:(e.textContent||e.getAttribute('aria-label')||'').trim().slice(0,30)}; });
    if (!r || r.inDrawer) continue;
    total++;
    if (r.bottom <= r.h + 1) { hidden++; example ||= `${r.name} top=${Math.round(r.top)} header=${Math.round(r.h)}`; }
    else if (r.top < r.h) { example ||= `partly: ${r.name} top=${Math.round(r.top)} header=${Math.round(r.h)}`; }
  }
  console.log(w, {total, hidden, example, scroller: await p.evaluate(()=> document.scrollingElement.scrollTop + ' / page.scrollTop ' + document.getElementById('page').scrollTop)});
}
await b.close();
