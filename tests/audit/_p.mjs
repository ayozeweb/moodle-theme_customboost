import {chromium} from 'playwright';
import fs from 'node:fs';
const pages = JSON.parse(fs.readFileSync('pages.json','utf8'));
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:320,height:800}, reducedMotion:'reduce'})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','prof'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080'+pages.find(x=>x.id==='participants').url, {waitUntil:'networkidle'});
console.log('participants sw', await p.evaluate(()=>document.documentElement.scrollWidth));
await p.goto('http://localhost:8080'+pages.find(x=>x.id==='course-edit').url, {waitUntil:'networkidle'});
const t = p.locator('.editmode-switch-form input[type=checkbox]').first();
if (!(await t.isChecked())) { await t.evaluate(e=>e.click()); await p.waitForFunction(()=>document.body.classList.contains('editing')); await p.waitForLoadState('networkidle'); }
const a = p.locator('span[data-itemid="36"] > .aalink.stretched-link');
await a.scrollIntoViewIfNeeded();
console.log(await a.evaluate(e => { const r=e.getBoundingClientRect(); const out=['rect '+[r.left,r.top,r.width,r.height].map(Math.round)];
  for (const [x,y] of [[r.left+3,r.top+3],[r.left+r.width/2,r.top+r.height/2],[r.right-3,r.bottom-3],[r.left+3,r.bottom-2]]) { const h=document.elementFromPoint(x,y); out.push(Math.round(x)+','+Math.round(y)+' -> '+(h?h.tagName+'.'+[...h.classList].slice(0,3).join('.'):'null')); } return out; }));
const box = await p.locator('span[data-itemid="36"]').locator('xpath=ancestor::li[1]').boundingBox();
await p.screenshot({path:'/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/choice320.png', clip:{x:0,y:Math.max(0,box.y-10),width:320,height:Math.min(260, box.height+20)}});
await b.close();
