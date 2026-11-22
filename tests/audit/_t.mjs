import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:360,height:800}})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/course/view.php?id=8', {waitUntil:'networkidle'});
await p.locator('.course-content table').first().scrollIntoViewIfNeeded(); await p.screenshot({path:'/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/table360.png'});
console.log(await p.evaluate(() => {
  const t = document.querySelector('.course-content table'); const out=[];
  for (let e=t, i=0; e && i<6; e=e.parentElement, i++) { const s=getComputedStyle(e); out.push(e.tagName+'.'+[...e.classList].slice(0,3).join('.')+' w='+Math.round(e.getBoundingClientRect().width)+' ox='+s.overflowX+' ow='+s.overflowWrap+' wb='+s.wordBreak+' tl='+s.tableLayout); }
  const td = t.querySelector('th,td'); const s=getComputedStyle(td); out.push('cell ow='+s.overflowWrap+' wb='+s.wordBreak+' minw='+s.minWidth+' hyph='+s.hyphens);
  return out;
}));
await b.close();
