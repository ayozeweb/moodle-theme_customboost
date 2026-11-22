import {chromium} from 'playwright';
const b = await chromium.launch();
const run = async (url, login) => {
  const ctx = await b.newContext({viewport:{width:360,height:800}, isMobile:true, hasTouch:true});
  if (login) { const lp = await ctx.newPage(); await lp.goto('http://localhost:8080/login/index.php'); await lp.fill('#username','ana'); await lp.fill('#password','Demo1234!'); await Promise.all([lp.waitForNavigation(), lp.click('#loginbtn')]); await lp.close(); }
  const p = await ctx.newPage();
  const cdp = await ctx.newCDPSession(p);
  await cdp.send('Network.enable'); await cdp.send('Network.setCacheDisabled', {cacheDisabled: true});
  await cdp.send('Network.emulateNetworkConditions', {offline:false, latency:150, downloadThroughput: 1.6*1024*1024/8, uploadThroughput: 750*1024/8});
  await cdp.send('Emulation.setCPUThrottlingRate', {rate: 4});
  let bytes = 0, reqs = 0;
  p.on('response', async r => { reqs++; try { const h = await r.headerValue('content-length'); bytes += +(h||0); } catch(e){} });
  const t0 = Date.now();
  await p.goto('http://localhost:8080'+url, {waitUntil:'load', timeout: 120000});
  const load = Date.now() - t0;
  const m = await p.evaluate(() => new Promise(res => { let lcp=0; new PerformanceObserver(l => { for (const e of l.getEntries()) lcp = e.startTime; }).observe({type:'largest-contentful-paint', buffered:true}); setTimeout(() => { const n=performance.getEntriesByType('navigation')[0]; const fcp=performance.getEntriesByName('first-contentful-paint')[0]; res({ttfb: Math.round(n.responseStart), fcp: Math.round(fcp?.startTime||0), lcp: Math.round(lcp), transfer: performance.getEntriesByType('resource').reduce((a,r)=>a+r.transferSize,0)+n.transferSize}); }, 1500); }));
  console.log(url, {load, ...m, transferKB: Math.round(m.transfer/1024), reqs});
  await ctx.close();
};
await run('/login/index.php', false);
await run('/my/', true);
await b.close();
