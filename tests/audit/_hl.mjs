import {chromium} from 'playwright';
const b = await chromium.launch();
const ctx = await b.newContext({viewport:{width:1280,height:800}});
const p = await ctx.newPage();
const report = async (label) => console.log(label, JSON.stringify(await p.evaluate(() => {
  const hs = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6,[role=heading]')].filter(h => !h.closest('[hidden], template'));
  const levels = hs.map(h => +(h.getAttribute('aria-level') || h.tagName[1]));
  const jumps = []; for (let i=1;i<levels.length;i++) if (levels[i] > levels[i-1] + 1) jumps.push(`${levels[i-1]}->${levels[i]} "${hs[i].textContent.trim().slice(0,25)}"`);
  const name = (e) => e.getAttribute('aria-label') || (e.getAttribute('aria-labelledby') && document.getElementById(e.getAttribute('aria-labelledby'))?.textContent.trim().slice(0,20)) || '';
  const lm = (sel) => [...document.querySelectorAll(sel)].map(e => e.tagName.toLowerCase() + (name(e) ? `(${name(e)})` : ''));
  return {h1: levels.filter(l=>l===1).length, first: hs[0]?.tagName + ' ' + hs[0]?.textContent.trim().slice(0,25), jumps: jumps.slice(0,4),
    banner: lm('header:not(main header):not(section header):not(article header), [role=banner]').length, nav: lm('nav, [role=navigation]'), main: lm('main, [role=main]').length, contentinfo: lm('footer:not(main footer):not(section footer):not(article footer), [role=contentinfo]').length};
})));
await p.goto('http://localhost:8080/login/index.php', {waitUntil:'networkidle'}); await report('login');
await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
for (const u of ['/my/', '/my/courses.php', '/course/view.php?id=8']) { await p.goto('http://localhost:8080'+u, {waitUntil:'networkidle'}); await report(u); }
await b.close();
