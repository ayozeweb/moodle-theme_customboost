import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:360,height:800}})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
console.log(await p.evaluate(() => {
  const h = document.querySelector('.ct-dashboard-hero');
  const main = document.querySelector('[role=main], main');
  const mc = document.getElementById('maincontent');
  return {heroInMain: main.contains(h), mainTag: main.tagName+'#'+main.id, heroBeforeMaincontent: !!(h.compareDocumentPosition(mc) & Node.DOCUMENT_POSITION_FOLLOWING),
    landmarks: [...document.querySelectorAll('header,nav,main,footer,aside,[role=banner],[role=navigation],[role=main],[role=contentinfo],[role=complementary],[role=region],section[aria-labelledby]')].map(e=>e.tagName+(e.getAttribute('role')?'['+e.getAttribute('role')+']':'')+' '+(e.getAttribute('aria-label')||e.getAttribute('aria-labelledby')||'')).slice(0,20)};
}));
await b.close();
