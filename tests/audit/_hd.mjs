import {chromium} from 'playwright';
const b = await chromium.launch();
for (const w of [320, 360]) {
  const p = await (await b.newContext({viewport:{width:w,height:700}, reducedMotion:'reduce'})).newPage();
  await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','admin'); await p.fill('#password','Demo1234!');
  await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
  await p.goto('http://localhost:8080/course/view.php?id=8', {waitUntil:'networkidle'});
  console.log(w, await p.evaluate(() => {
    const nav = document.querySelector('nav.navbar'); const vw = document.documentElement.clientWidth;
    const items = [...nav.querySelectorAll('a, button, input, [role=button]')].filter(e => e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden');
    const out = items.map(e => { const r=e.getBoundingClientRect(); return (e.getAttribute('aria-label')||e.title||e.textContent.trim()||e.className).slice(0,22)+'@'+Math.round(r.left)+'-'+Math.round(r.right)+'x'+Math.round(r.height); });
    // Spacing between adjacent targets (MOV-03).
    const rs = items.map(e=>e.getBoundingClientRect()).filter(r=>r.width>4).sort((a,b)=>a.left-b.left);
    const gaps = []; for (let i=1;i<rs.length;i++) { if (rs[i].left >= rs[i-1].right - 1) gaps.push(Math.round(rs[i].left - rs[i-1].right)); }
    return {docsw: document.documentElement.scrollWidth, navsw: nav.scrollWidth, vw, brandVisible: !!document.querySelector('.ct-brand')?.getClientRects().length, items: out, minGap: Math.min(...gaps)};
  }));
  await p.screenshot({path:`/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/crowded-${w}.png`, clip:{x:0,y:0,width:w,height:70}});
}
await b.close();
