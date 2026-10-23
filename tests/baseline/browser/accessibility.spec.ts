import {test,expect} from './helpers';
import AxeBuilder from '@axe-core/playwright';
for(const path of ['/','/login','/register'])test('critical accessibility '+path,async({page},info)=>{
 expect((await page.goto(path))?.status()).toBe(200);
 const scan=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();
 await info.attach('axe-results',{body:JSON.stringify(scan,null,2),contentType:'application/json'});
 expect(scan.violations.filter(v=>['serious','critical'].includes(v.impact!)).map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>n.target)}))).toEqual([]);
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth+1)).toBe(true);
});
