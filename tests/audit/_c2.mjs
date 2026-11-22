import {chromium} from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({viewport:{width:360,height:800}, reducedMotion:'reduce'})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','prof'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/course/view.php?id=8', {waitUntil:'networkidle'});
const t = p.locator('.editmode-switch-form input[type=checkbox]').first();
if (!(await t.isChecked())) { await t.evaluate(e=>e.click()); await p.waitForFunction(()=>document.body.classList.contains('editing')); await p.waitForLoadState('networkidle'); }
await p.locator('[data-for=enableBulk]').first().evaluate(e => e.click());
await p.waitForTimeout(800);
await p.locator('[data-for=cmBulkSelect] input, .bulkselect input[type=checkbox]').nth(1).check({force:true}).catch(e=>console.log('nocheck', e.message.slice(0,80)));
await p.waitForTimeout(500);
await p.screenshot({path:'/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/bulk360.png'});
console.log(await p.evaluate(() => { const f=document.querySelector('#sticky-footer'); const r=f.getBoundingClientRect(); return 'footer top '+Math.round(r.top)+' h '+Math.round(r.height)+' sw '+document.documentElement.scrollWidth; }));
await b.close();
