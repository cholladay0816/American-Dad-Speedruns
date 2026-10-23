import {test,expect,login} from './helpers';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
test('populated list preserves ties, ordering, disqualified-last and existing no-pagination behavior',async({page})=>{
 const expected=Array.from({length:25},(_,i)=>'SyntheticRank'+String(i).padStart(2,'0'));
 for(const path of ['/speedruns','/speedruns?page=2']){await page.goto(path);const rows=page.locator('tbody tr');await expect(rows).toHaveCount(25);expect((await rows.locator('td:nth-child(2)').allTextContents()).map(s=>s.trim())).toEqual(expected);const places=await rows.locator('td:first-child').allTextContents();expect(places.slice(0,3).map(s=>s.trim())).toEqual(['1st','1st','3rd']);await expect(rows.last()).toContainText('Disqualified');}
});
for(const surface of ['category','run','profile'])test('populated accessibility '+surface,async({page},info)=>{
 await login(page,'moderator');const ids=JSON.parse(fs.readFileSync('/tmp/adsr-ranking-fixture.json','utf8')).ids;await page.goto(surface==='category'?'/categories/ranking-fixture':surface==='run'?'/watch/'+ids[0]:'/user/profile');await expect(page.locator('main')).toBeVisible();
 const scan=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();await info.attach('axe',{body:JSON.stringify(scan,null,2),contentType:'application/json'});await page.screenshot({path:info.outputPath('populated-'+surface+'.png'),fullPage:true});expect(scan.violations.filter(v=>['serious','critical'].includes(v.impact!)).map(v=>({id:v.id,nodes:v.nodes.map(n=>n.target)}))).toEqual([]);
});
