import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:1280,height:800}, forcedColors:'active'})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
console.log(await p.evaluate(() => {
  const a = [...document.querySelectorAll('.primary-navigation a')].find(x => x.textContent.trim()==='Dashboard');
  const sw = document.querySelector('.editmode-switch-form');
  return sw.querySelector(".mds-switch").outerHTML.replace(/\s+/g," ").slice(0,900);
}));
await b.close();
