import {test,expect,login,headers} from './helpers';
import AxeBuilder from '@axe-core/playwright';

test('new account registers, edits profile, logs out and signs back in',async({page})=>{
 const id=String(Date.now());const email='synthetic'+id+'@example.test';const name='Synthetic'+id;const password='Synthetic-only-Password123!';
 await page.goto('/register');await page.locator('#name').fill(name);await page.locator('#email').fill(email);await page.locator('#password').fill(password);await page.locator('#password_confirmation').fill(password);await page.getByRole('button',{name:'Register',exact:true}).click();await expect(page).toHaveURL(/\/dashboard$/);
 await page.goto('/user/profile');await page.locator('#name').fill(name+'Edited');const form=page.locator('form').filter({has:page.locator('#name')});await Promise.all([page.waitForResponse(r=>r.url().includes('/livewire/update')&&r.status()===200),form.getByRole('button',{name:'Save',exact:true}).click()]);await page.reload();await expect(page.locator('#name')).toHaveValue(name+'Edited');
 expect((await page.request.post('/logout',{headers:await headers(page),maxRedirects:0})).status()).toBe(204);
 await page.goto('/user/profile');await expect(page).toHaveURL(/\/login$/);
 await page.goto('/login');await expect(page.locator('#email')).toBeFocused();await page.locator('#email').fill(email);await page.locator('#password').fill(password);await expect(page.locator('#email')).toHaveValue(email);await expect(page.locator('#password')).toHaveValue(password);await page.getByRole('button',{name:'Login',exact:true}).click();await expect(page).toHaveURL(/\/user\/profile$/);await expect(page.locator('#name')).toHaveValue(name+'Edited');
});

test('password reset rejects invalid token and nonexistent user without outbound mail',async({page})=>{
 await page.goto('/forgot-password');await page.locator('#email').fill('missing-synthetic@example.test');await page.getByRole('button',{name:/Email Password Reset Link/i}).click();await expect(page.locator('body')).not.toContainText('Server Error');
 await page.goto('/reset-password/synthetic-invalid-token?email=runner%40example.test');await page.locator('#password').fill('Synthetic-replacement-Password123!');await page.locator('#password_confirmation').fill('Synthetic-replacement-Password123!');await page.getByRole('button',{name:'Reset Password',exact:true}).click();await expect(page.locator('body')).toContainText(/password reset token is invalid/i);
});

test('moderator can verify, disqualify, edit and reverse a synthetic run',async({page},info)=>{
 test.setTimeout(120000);await login(page,'moderator');await page.goto('/speedruns/new');const url='https://youtu.be/M'+String(Date.now()).slice(-10);
 const response=await page.request.post('/speedruns/new',{headers:await headers(page),data:{url,time:'15.12345',category:1,platform:1},maxRedirects:0});expect(response.status()).toBe(302);
 await page.goto('/runner/SyntheticModerator');await page.waitForFunction(()=>Array.from(document.querySelectorAll('a')).some(a=>/\/watch\/\d+$/.test(a.href)));const links=await page.locator('a').evaluateAll(as=>as.map(a=>a.getAttribute('href')||'').filter(h=>/\/watch\/\d+$/.test(h)));const id=Math.max(...links.map(h=>Number(h.split('/').pop())));expect(id).toBeGreaterThan(1);
 try {
 expect((await page.request.patch('/speedruns/'+id,{headers:await headers(page),maxRedirects:0})).status()).toBe(302);
 await page.goto('/');await expect(page.getByRole('link',{name:'Featured Run',exact:true})).toHaveAttribute('href',new RegExp('/watch/'+id+'$'));
 const scan=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();await info.attach('featured-axe',{body:JSON.stringify(scan,null,2),contentType:'application/json'});expect(scan.violations.filter(v=>['serious','critical'].includes(v.impact!)).map(v=>({id:v.id,nodes:v.nodes.map(n=>n.target)}))).toEqual([]);await page.screenshot({path:info.outputPath('featured-synthetic-run.png'),fullPage:true});
 await page.goto('/admin/disqualify/'+id);await page.locator('textarea[name=reason]').fill('Synthetic moderation reason');await page.locator('textarea[name=evidence]').fill('http://127.0.0.1:8085/');await page.getByRole('button',{name:'Submit',exact:true}).click();await expect(page).toHaveURL(/\/admin\/disqualifications\/\d+$/);
 const dq=page.url();await page.locator('textarea[name=reason]').fill('Synthetic revised reason');await page.getByRole('button',{name:'Update',exact:true}).click();await expect(page.locator('textarea[name=reason]')).toHaveValue('Synthetic revised reason');
 await page.getByRole('button',{name:'Delete',exact:true}).click();await expect(page).toHaveURL(new RegExp('/speedruns/'+id+'$'));expect((await page.goto(dq))?.status()).toBe(404);
 } finally {
 await page.goto('/admin');const cleanup=await page.request.delete('/speedruns/'+id,{headers:await headers(page),maxRedirects:0});expect(cleanup.status()).toBe(302);
 }
});

test('empty, missing and suspension states are bounded',async({page})=>{
 expect((await page.goto('/runner/SyntheticBannersOnly'))?.status()).toBe(200);expect((await page.goto('/speedruns/99999999'))?.status()).toBe(404);await login(page,'suspended');await page.goto('/speedruns/1');const message='SuspendedComment'+Date.now();await page.locator('textarea[name=message]').fill(message);await page.locator('textarea[name=message]').blur();await page.getByRole('button',{name:'Submit',exact:true}).click();await page.reload();await expect(page.locator('p').filter({hasText:message})).toHaveCount(0);
});
