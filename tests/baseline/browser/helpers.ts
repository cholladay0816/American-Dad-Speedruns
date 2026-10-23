import { test as base, expect, Page } from '@playwright/test';
export const test=base.extend({context:async({context,baseURL},use)=>{
 await context.route('**/*',r=>{
  if(r.request().url()==='https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.7.3/dist/alpine.js')return r.fulfill({path:'/app/tests/baseline/node_modules/alpinejs/dist/alpine.js',contentType:'application/javascript'});
  return new URL(r.request().url()).origin===new URL(baseURL!).origin?r.continue():r.abort('blockedbyclient');
 });await use(context);
}});
export {expect};
export async function login(page:Page,role='runner'){
 await page.goto('/login');await page.locator('#email').fill(role+'@example.test');await page.locator('#password').fill('Synthetic-only-Password123!');await page.getByRole('button',{name:'Login',exact:true}).click();await expect(page).toHaveURL(/\/dashboard$/);
}
export async function headers(page:Page){return {'X-CSRF-TOKEN':(await page.locator('meta[name=csrf-token]').getAttribute('content'))!,'Accept':'application/json'};}
