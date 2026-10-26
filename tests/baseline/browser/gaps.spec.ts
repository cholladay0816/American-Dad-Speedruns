import {test,expect} from './helpers';
import fs from 'node:fs';
const password='Synthetic-only-Password123!';
async function signIn(page:any,email:string,pw=password,expected=/dashboard$/){
 await page.goto('/login');
 // Observe initial autofocus before typing, just as on the reset form.
 await expect(page.locator('#email')).toBeFocused();
 await page.locator('#email').fill(email);await page.locator('#password').fill(pw);
 await expect(page.locator('#email')).toHaveValue(email);
 await expect(page.locator('#password')).toHaveValue(pw);
 const response=page.waitForResponse(r=>r.request().method()==='POST'&&new URL(r.url()).pathname==='/login');
 await page.getByRole('button',{name:'Login',exact:true}).click();
 expect((await response).status()).toBe(302);await expect(page).toHaveURL(expected);
}
test('valid reset token completes in browser, is single use, and new password signs in',async({page},info)=>{
 const email='reset-'+info.project.name+'@example.test';const token=JSON.parse(fs.readFileSync('/tmp/adsr-reset-fixtures.json','utf8'))[info.project.name];const url='/reset-password/'+token+'?email='+encodeURIComponent(email);const next='Synthetic-Changed-Password123!';
 async function submitReset() {
  await page.goto(url);
  // WebKit may apply initial HTML autofocus after the first fill starts.
  // Observe that lifecycle before typing; do not force focus or bypass validation.
  await expect(page.locator('#email')).toBeFocused();
  await expect(page.locator('#email')).toHaveValue(email);
  await page.locator('#password').fill(next);
  await page.locator('#password_confirmation').fill(next);
  await expect(page.locator('#email')).toHaveValue(email);
  await expect(page.locator('#password')).toHaveValue(next);
  await expect(page.locator('#password_confirmation')).toHaveValue(next);
  const response = page.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/reset-password');
  await page.getByRole('button',{name:'Reset Password',exact:true}).click();
  expect((await response).status()).toBe(302);
 }
 await submitReset();await expect(page).toHaveURL(/login$/);
 await submitReset();await expect(page.locator('body')).toContainText(/password reset token is invalid/i);await signIn(page,email,next);
});
test('account deletion rejects wrong password then deletes only disposable account',async({page},info)=>{
 const email='delete-'+info.project.name+'@example.test';await signIn(page,email);await page.goto('/user/profile');await page.locator('[wire\\:click="confirmUserDeletion"]').click();const input=page.locator('input[wire\\:model="password"]:visible');await expect(input).toBeFocused();await page.keyboard.press('Tab');await expect(page.getByRole('button',{name:'Nevermind',exact:true})).toBeFocused();await page.keyboard.press('Escape');await expect(input).not.toBeVisible();await page.getByRole('button',{name:'Delete Account',exact:true}).click();await expect(input).toBeFocused();await input.fill('wrong');await page.locator('[wire\\:click="deleteUser"]').click();await expect(page.locator('body')).toContainText('This password does not match our records.');await input.fill(password);await page.locator('[wire\\:click="deleteUser"]').click();await expect(page).toHaveURL(/8085\/$/);await page.goto('/user/profile');await expect(page).toHaveURL(/login$/);await page.locator('#email').fill(email);await page.locator('#password').fill(password);await page.getByRole('button',{name:'Login',exact:true}).click();await expect(page.locator('body')).toContainText('These credentials do not match our records.');await signIn(page,'runner@example.test',password,/user\/profile$/);await expect(page.locator('#email')).toHaveValue('runner@example.test');
});
test('other browser session action characterizes file driver or revokes database sessions',async({page,browser,baseURL},info)=>{
 const email='sessions-'+info.project.name+'@example.test';await signIn(page,email);const other=await browser.newContext({baseURL});await other.route('**/*',r=>new URL(r.request().url()).origin===new URL(baseURL!).origin?r.continue():r.abort());const second=await other.newPage();
 try{await signIn(second,email);await second.goto('/user/profile');await page.goto('/user/profile');await page.locator('[wire\\:click="confirmLogout"]').click();await page.locator('input[wire\\:model="password"]:visible').fill(password);await page.locator('[wire\\:click="logoutOtherBrowserSessions"]').click();
 if(process.env.ADSR_SESSION_DRIVER==='database'){await expect(page.locator('[wire\\:click="logoutOtherBrowserSessions"]')).not.toBeVisible();await second.goto('/user/profile');await expect(second).toHaveURL(/login$/);await page.reload();await expect(page).toHaveURL(/user\/profile$/);}
 else{await second.goto('/user/profile');await expect(second).toHaveURL(/user\/profile$/);await expect(page.locator('[wire\\:click="logoutOtherBrowserSessions"]')).toBeVisible();}
 }finally{await other.close();}
});
test('keyboard login sequence and missing page preserve usable navigation',async({page})=>{
 await page.goto('/login');await page.locator('#email').focus();await page.keyboard.type('runner@example.test');await page.keyboard.press('Tab');await expect(page.locator('#password')).toBeFocused();await page.keyboard.type(password);await page.keyboard.press('Enter');await expect(page).toHaveURL(/dashboard$/);expect((await page.goto('/speedruns/99999999'))?.status()).toBe(404);await page.goto('/speedruns');await expect(page.locator('body')).not.toContainText('Server Error');
});
