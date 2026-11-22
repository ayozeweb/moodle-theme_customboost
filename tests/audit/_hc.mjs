import {chromium} from 'playwright';
import {execSync} from 'node:child_process';
const out = '/tmp/claude-0/-home-user-moodle-theme-customboost/f3b5be22-ab6d-5763-99b2-7cd7eb11c8f2/scratchpad/';
const set = (v) => execSync(`cd /home/user/moodle && php admin/cli/cfg.php --component=theme_customboost --name=colourpreset --set=${v} && php -r 'define("CLI_SCRIPT",1); require "config.php"; theme_reset_all_caches();'`);
const b = await chromium.launch();
const shoot = async (name, opts, urls) => {
  const ctx = await b.newContext({viewport:{width:1280,height:800}, reducedMotion:'reduce', ...opts});
  const p = await ctx.newPage();
  await p.goto('http://localhost:8080/login/index.php', {waitUntil:'networkidle'});
  await p.screenshot({path: out + `hc-${name}-login.png`});
  await p.fill('#username','ana'); await p.fill('#password','Demo1234!');
  await Promise.all([p.waitForNavigation(), p.click('#loginbtn')]);
  await p.goto('http://localhost:8080/my/', {waitUntil:'networkidle'});
  await p.screenshot({path: out + `hc-${name}-dash.png`});
  console.log(name, await p.evaluate(() => [document.documentElement.dataset.bsTheme, document.documentElement.dataset.ctContrast, getComputedStyle(document.body).color, getComputedStyle(document.body).backgroundColor, matchMedia('(prefers-contrast: more)').matches, matchMedia('(forced-colors: active)').matches]));
  await ctx.close();
};
// Wait for compile.
set('highcontrast'); await new Promise(r => setTimeout(r, 1000));
const warm = await b.newPage(); await warm.goto('http://localhost:8080/login/index.php', {timeout: 120000}); await warm.close();
await shoot('light', {}, []);
set('highcontrastdark');
await shoot('dark', {}, []);
set('light');
await shoot('prefers', {contrast: 'more'}, []);
await shoot('forced', {forcedColors: 'active'}, []);
await b.close();
