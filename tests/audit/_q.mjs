import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:360,height:800}})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/mod/quiz/view.php?id=37', {waitUntil:'networkidle'});
await p.evaluate(() => { const s=parseFloat(getComputedStyle(document.documentElement).fontSize); document.documentElement.style.setProperty('font-size', s*2+'px','important'); });
console.log(await p.evaluate(() => { const td=[...document.querySelectorAll('td.cell')].find(t=>t.textContent.includes('In progress')); const t=td.closest('table'); const out=['table '+t.className+' w='+Math.round(t.getBoundingClientRect().width)+' rows='+t.rows.length+' cols='+t.rows[0].cells.length];
 for (const c of t.rows[0].cells) { const s=getComputedStyle(c); out.push(c.tagName+' "'+c.textContent.trim().slice(0,20)+'" w='+Math.round(c.getBoundingClientRect().width)+' ws='+s.whiteSpace+' wb='+s.wordBreak);}
 for (let e=t.parentElement,i=0;i<4&&e;i++,e=e.parentElement){const s=getComputedStyle(e); out.push('parent '+e.tagName+'.'+e.className.slice(0,40)+' w='+Math.round(e.getBoundingClientRect().width)+' ox='+s.overflowX);}
 return out; }));
await p.screenshot({path:'/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/q200.png', fullPage:true});
await b.close();
