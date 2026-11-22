import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:360,height:800}, hasTouch:true, isMobile:true, reducedMotion:'reduce'})).newPage();
p.on('console', m => console.log('PAGE', m.text()));
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
await p.evaluate(() => {
  const d = () => { const e=document.activeElement; return e ? e.tagName+'.'+(e.className||'').toString().slice(0,30) : 'none'; };
  for (const t of ['theme_boost/drawers:hide','theme_boost/drawers:hidden','focusin','mousedown','click']) document.addEventListener(t, (e) => console.log(performance.now().toFixed(0), t, (e.target.id||e.target.className||'').toString().slice(0,40), 'active=', d()), true);
});
await p.click('.ct-navbar-toggler'); await p.waitForTimeout(500);
await p.mouse.click(340, 400); await p.waitForTimeout(800);
await b.close();
