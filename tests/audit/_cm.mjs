import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:360,height:700}})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','admin'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
console.log(await p.evaluate(() => {
  const n = document.querySelector('nav.navbar .navbar-nav.ms-auto') || document.querySelector('nav.navbar');
  return [...n.children].map(c => c.outerHTML.replace(/\s+/g,' ').slice(0,260));
}));
await b.close();
