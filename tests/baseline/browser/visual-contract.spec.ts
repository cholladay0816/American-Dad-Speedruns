import {test,expect} from './helpers';
test('home illustration retains original scale and responsive navigation works',async({page},info)=>{
 await page.goto('/');const illustration=page.getByRole('img',{name:'Stan Smith'});await expect(illustration).toBeVisible();const box=await illustration.boundingBox();expect(box!.width).toBeGreaterThan(250);expect(box!.height).toBeGreaterThan(350);
 await page.screenshot({path:info.outputPath('restored-home.png'),fullPage:true});
 if(info.project.name.startsWith('mobile-')){const menu=page.getByRole('button',{name:'Toggle navigation'});await expect(menu).toHaveAttribute('aria-expanded','false');await menu.click();await expect(menu).toHaveAttribute('aria-expanded','true');await expect(page.getByRole('link',{name:'Categories',exact:true})).toBeVisible();await page.getByRole('link',{name:'Categories',exact:true}).click();await expect(page).toHaveURL(/\/categories$/);}
 await page.screenshot({path:info.outputPath('reviewed-layout.png'),fullPage:true});
});
