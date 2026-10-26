import { test, expect } from '@playwright/test';
test.beforeEach(async ({ context, baseURL }) => {
 await context.route('**/*', route => {
  if(route.request().url()==='https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.7.3/dist/alpine.js') return route.fulfill({path:'/app/tests/baseline/node_modules/alpinejs/dist/alpine.js',contentType:'application/javascript'});
  return new URL(route.request().url()).origin===new URL(baseURL!).origin?route.continue():route.abort('blockedbyclient');
 });
});
async function login(page:any,email:string){
 await page.goto('/login');await page.locator('#email').fill(email);await page.locator('#password').fill('Synthetic-only-Password123!');
 await page.getByRole('button',{name:'Login',exact:true}).click();await expect(page).toHaveURL(/\/dashboard$/);
}
test('observed live unverified user can submit form without verification challenge',async({page})=>{
 await login(page,'unverified@example.test');const r=await page.goto('/speedruns/new');expect(r?.status()).toBe(200);await expect(page.locator('input[name=url]')).toBeVisible();
 await expect(page).toHaveScreenshot('unverified-submission.png',{fullPage:true});
});
test('ordinary runner is forbidden from administration',async({page})=>{
 await login(page,'runner@example.test');expect((await page.goto('/admin'))?.status()).toBe(403);
});
test('ordinary runner cannot delete another users run',async({page})=>{
 await login(page,'other@example.test');const token=await page.locator('meta[name=csrf-token]').getAttribute('content');
 const r=await page.request.delete('/speedruns/1',{headers:{'X-CSRF-TOKEN':token!}});expect([401,403]).toContain(r.status());
});
test('profile renders under observed live auth overlay',async({page})=>{
 await login(page,'runner@example.test');expect((await page.goto('/user/profile'))?.status()).toBe(200);await expect(page.locator('body')).not.toContainText('Server Error');
 await expect(page.locator('#name')).toHaveValue('SyntheticRunner');
 await expect(page.locator('#email')).toHaveValue('runner@example.test');
 await expect(page).toHaveScreenshot('profile.png',{fullPage:true});
});
test('@retirement-acceptance retired billing controls are unavailable on profile',async({page})=>{
 await login(page,'runner@example.test');expect((await page.goto('/user/profile'))?.status()).toBe(200);
 await expect(page.getByRole('button',{name:'Visit Stripe'})).toHaveCount(0);
});
test('invalid login is rejected',async({page})=>{
 await page.goto('/login');await page.locator('#email').fill('runner@example.test');await page.locator('#password').fill('WrongSyntheticPassword');await page.getByRole('button',{name:'Login',exact:true}).click();await expect(page).toHaveURL(/\/login$/);await expect(page.locator('body')).toContainText('These credentials do not match our records');
});
