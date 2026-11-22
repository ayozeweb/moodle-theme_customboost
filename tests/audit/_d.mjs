import {chromium} from 'playwright';
const b = await chromium.launch();
for (const w of [360, 1280]) {
const p = await (await b.newContext({viewport:{width:w,height:800}, reducedMotion:'reduce'})).newPage();
await p.goto('http://localhost:8080/login/index.php'); await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
await p.screenshot({path:`/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/dash-new-${w}.png`});
}
await b.close();
