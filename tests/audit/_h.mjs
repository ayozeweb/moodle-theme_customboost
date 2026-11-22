import {chromium} from 'playwright';
const b = await chromium.launch();
const ctx = await b.newContext({viewport:{width:360,height:800}});
const p = await ctx.newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/my/courses.php', {waitUntil:'networkidle'});
for (const k of [1,2]) {
await p.evaluate((k) => { document.documentElement.style.removeProperty('font-size'); const s=parseFloat(getComputedStyle(document.documentElement).fontSize); document.documentElement.style.setProperty('font-size', s*k+'px','important'); }, k);
await p.waitForTimeout(200);
console.log('x'+k, await p.evaluate(() => {
  const nav = document.querySelector('nav.navbar'); const out = ['nav w='+Math.round(nav.getBoundingClientRect().width)+' sw='+nav.scrollWidth+' doc sw='+document.documentElement.scrollWidth];
  const walk = (e, d) => { for (const c of e.children) { const r=c.getBoundingClientRect(); if (!r.width) continue; out.push(' '.repeat(d)+c.tagName+'.'+[...c.classList].slice(0,4).join('.')+' l='+Math.round(r.left)+' w='+Math.round(r.width)); if (d<4) walk(c, d+1); } };
  let e = document.querySelector('.popover-region-notifications .all-notifications');
  const o2 = [];
  for (let i=0;i<6&&e;i++,e=e.parentElement){const s=getComputedStyle(e);o2.push(e.className.slice(0,40)+' w='+s.width+' minw='+s.minWidth+' pos='+s.position+' l='+s.left+' r='+s.right);}
  return o2;
}));
}
await b.close();
