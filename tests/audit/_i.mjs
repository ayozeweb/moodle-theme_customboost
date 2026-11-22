import {chromium} from 'playwright';
const b = await chromium.launch();
const ctx = await b.newContext({viewport:{width:360,height:800}, hasTouch:true, isMobile:true, reducedMotion:'reduce'});
const p = await ctx.newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
const focused = () => p.evaluate(() => { const e=document.activeElement; return e ? e.tagName+'.'+[...e.classList].slice(0,3).join('.')+' "'+(e.getAttribute('aria-label')||e.textContent.trim()).slice(0,30)+'"' : 'none'; });
const drawerOpen = () => p.evaluate(() => document.querySelector('#theme_boost-drawers-primary').classList.contains('show'));
// Skip link.
await p.keyboard.press('Tab');
console.log('1st tab:', await focused());
await p.keyboard.press('Enter'); await p.waitForTimeout(200);
console.log('after skip:', await focused(), 'scrollY', await p.evaluate(()=>scrollY));
// Drawer with keyboard.
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
await p.focus('.ct-navbar-toggler'); await p.keyboard.press('Enter'); await p.waitForTimeout(400);
console.log('open:', await drawerOpen(), 'focus in drawer:', await focused());
await p.keyboard.press('Escape'); await p.waitForTimeout(400);
console.log('after Esc open:', await drawerOpen(), 'focus:', await focused());
// Backdrop.
await p.click('.ct-navbar-toggler'); await p.waitForTimeout(400);
console.log('at 340,400:', await p.evaluate(()=>{const e=document.elementFromPoint(340,400); return e.tagName+'.'+e.className;}), 'drawer right', await p.evaluate(()=>Math.round(document.querySelector('#theme_boost-drawers-primary').getBoundingClientRect().right)));
await p.mouse.click(340, 400); await p.waitForTimeout(400);
console.log('after outside tap open:', await drawerOpen(), 'focus:', await focused());
// Close button.
await p.click('.ct-navbar-toggler'); await p.waitForTimeout(400);
await p.click('#theme_boost-drawers-primary [data-action="closedrawer"]'); await p.waitForTimeout(400);
console.log('after close btn open:', await drawerOpen(), 'focus:', await focused());
// Tab trap inside the open drawer?
await p.click('.ct-navbar-toggler'); await p.waitForTimeout(400);
const seen = [];
for (let i=0;i<15;i++){ await p.keyboard.press('Tab'); seen.push(await p.evaluate(()=>!!document.activeElement.closest('#theme_boost-drawers-primary'))); }
console.log('tab stays in drawer:', seen.join(','));
await p.keyboard.press('Escape');
// Card row.
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
await p.focus('.ct-course-scroller');
const cards = [];
for (let i=0;i<8;i++){ await p.keyboard.press('Tab'); cards.push(await p.evaluate(() => { const a=document.activeElement; if(!a.closest('.ct-course-grid')) return 'out:'+a.tagName; const r=a.closest('li').getBoundingClientRect(); return Math.round(r.left)+'-'+Math.round(r.right); })); }
console.log('card focus positions:', cards.join(' '));
// Vertical swipe over the row scrolls the page.
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
const box = await p.locator('.ct-course-scroller').boundingBox();
const before = await p.evaluate(()=>scrollY);
await p.mouse.move(box.x+150, box.y+100); await p.mouse.wheel(0, 300); await p.waitForTimeout(400);
console.log('wheel over row scrollY', before, '->', await p.evaluate(()=>scrollY));
await b.close();
