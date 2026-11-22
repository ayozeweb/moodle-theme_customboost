import {chromium} from 'playwright';
const b = await chromium.launch();
const ctx = await b.newContext({viewport:{width:360,height:800}, });
const p = await ctx.newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
const quiz = JSON.parse((await import('node:fs')).readFileSync('pages.json','utf8')).find(x=>x.id==='quiz').url;
for (const u of ['/my/courses.php', quiz]) {
  await p.goto('http://localhost:8080'+u, {waitUntil:'networkidle'});
  await p.evaluate(() => { const s=parseFloat(getComputedStyle(document.documentElement).fontSize); document.documentElement.style.setProperty('font-size', s*2+'px','important'); });
  await p.waitForTimeout(300); await p.screenshot({path:'/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/t200-'+(u.includes('quiz')?'quiz':'mc')+'.png'});
  console.log(await p.evaluate(()=>{const c=document.querySelector('.popover-region-container'); const s=getComputedStyle(c); return 'popover '+s.width+' vis='+s.visibility+' disp='+s.display+' pos='+s.position+' right='+s.right+' left='+s.left+' h='+s.height; }));
  console.log(u, await p.evaluate(() => { const w=document.documentElement.clientWidth; const all=[...document.body.querySelectorAll('*')].filter(e=>{const r=e.getBoundingClientRect(); return r.width && r.right>w+1;});
   return all.filter(e=>![...e.children].some(c=>all.includes(c))).slice(0,6).map(e=>e.tagName+'.'+[...e.classList].slice(0,4).join('.')+' r='+Math.round(e.getBoundingClientRect().right)+' w='+Math.round(e.getBoundingClientRect().width)+' "'+e.textContent.trim().slice(0,30)+'" par='+e.parentElement.className.slice(0,50)); }));
}
await b.close();
