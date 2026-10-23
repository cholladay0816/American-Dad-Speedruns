import {test,expect,login,headers} from './helpers';
for(const [role,admin,runs,banners] of [['admin',200,200,200],['moderator',200,200,403],['viewonly',200,403,403],['runsonly',403,403,403],['bannersonly',403,403,403]] as const){
 test('HTTP ability boundaries '+role,async({page})=>{
  await login(page,role);expect((await page.goto('/admin'))?.status()).toBe(admin);expect((await page.goto('/admin/verify'))?.status()).toBe(runs);expect((await page.goto('/banners'))?.status()).toBe(banners);
 });
}
test('profile hydrates current user and offers no active 2FA enrollment @auth-characterization',async({page})=>{
 const errors:string[]=[];page.on('pageerror',e=>errors.push(e.message));await login(page);await page.goto('/user/profile');
 await expect(page.locator('#name')).toHaveValue('SyntheticRunner');await expect(page.locator('#email')).toHaveValue('runner@example.test');expect(errors).toEqual([]);
 // Desired preservation of observed disabled trait; flag UI inconsistency instead of enabling it.
 await expect(page.getByRole('button',{name:'Enable',exact:true})).toHaveCount(0);
});
test('submission validation and duplicate protection',async({page},info)=>{
 await login(page);await page.goto('/speedruns/new');const h=await headers(page);
 const invalid=await page.request.post('/speedruns/new',{headers:h,data:{url:'bad',time:0,category:9999,platform:9999}});expect(invalid.status()).toBe(422);expect(Object.keys((await invalid.json()).errors).sort()).toEqual(['category','platform','time','url']);
 const video='https://youtu.be/T'+String(info.workerIndex).padStart(2,'0')+String(Date.now()).slice(-8);
 await page.locator('input[name=url]').fill(video);await page.locator('input[name=time]').fill('12.34567');await page.locator('select[name=category]').selectOption('1');await page.locator('select[name=platform]').selectOption('1');await page.getByRole('button',{name:'Submit',exact:true}).click();await expect(page).toHaveURL(/\/runner\/SyntheticRunner$/);
 const duplicate=await page.request.post('/speedruns/new',{headers:await headers(page),data:{url:video,time:'12.34567',category:1,platform:1}});expect(duplicate.status()).toBe(422);expect((await duplicate.json()).errors).toHaveProperty('url');
});
test('comments hydrate and synthetic comment can be posted',async({page})=>{
 await login(page);await page.goto('/speedruns/1');await page.locator('textarea[name=message]').fill('Synthetic browser comment');await page.locator('textarea[name=message]').blur();await page.getByRole('button',{name:'Submit',exact:true}).click();await expect(page.locator('p').filter({hasText:'Synthetic browser comment'}).first()).toBeVisible();
});
test('banner create edit delete workflow',async({page})=>{
 test.setTimeout(60000);
 await login(page,'admin');await page.goto('/banners/new');await page.locator('input[name=title]').fill('Synthetic browser banner');await page.locator('input[name=url]').fill('http://127.0.0.1:8085/');await page.locator('input[name=expiration]').fill('2035-01-01');
 await Promise.all([page.waitForURL(/\/admin$/,{timeout:30000}),page.locator('form').filter({has:page.locator('input[name=title]')}).evaluate((f:HTMLFormElement)=>f.requestSubmit())]);
 await page.goto('/banners');const edit=page.locator('tr').filter({hasText:'Synthetic browser banner'}).last().getByRole('link',{name:'Edit',exact:true});await expect(edit).toBeVisible();await edit.click();
 const id=page.url().split('/').pop();const h=await headers(page);const update=await page.request.put('/banners/'+id,{headers:h,data:{title:'Synthetic edited banner',url:'http://127.0.0.1:8085/',expiration:'2035-01-01',bodycolor:'blue-300',textcolor:'blue-700'},maxRedirects:0});expect(update.status()).toBe(302);
 expect((await page.request.delete('/banners/'+id,{headers:h,maxRedirects:0})).status()).toBe(302);expect((await page.goto('/banners/'+id))?.status()).toBe(404);
});
