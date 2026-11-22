import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:360,height:800}})).newPage();
await p.goto('http://localhost:8080/login/index.php', {waitUntil:'networkidle'});
console.log(await p.evaluate(() => [...document.querySelectorAll('.ct-login-brand')].map(e => { const s=getComputedStyle(e); const r=e.getBoundingClientRect(); return e.parentElement.className+' | bg='+s.backgroundColor+' color='+s.color+' rect='+[r.left,r.top,r.width,r.height].map(Math.round)+' vis='+!!e.getClientRects().length; })));
await b.close();
