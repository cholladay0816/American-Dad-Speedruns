import { test, expect } from '@playwright/test';
test.beforeEach(async ({ context, baseURL }) => {
  const origin = new URL(baseURL!).origin;
  await context.route('**/*', route => {
    if (route.request().url() === 'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.7.3/dist/alpine.js') return route.fulfill({ path: '/app/tests/baseline/node_modules/alpinejs/dist/alpine.js', contentType: 'application/javascript' });
    const url = new URL(route.request().url());
    return url.origin === origin ? route.continue() : route.abort('blockedbyclient');
  });
});
for (const path of ['/', '/speedruns', '/categories', '/platforms', '/login', '/register']) {
  test('public surface ' + path, async ({ page }) => {
    const errors: string[] = [];
    page.on('pageerror', e => errors.push(e.message));
    const response = await page.goto(path);
    expect(response?.status()).toBe(200);
    await expect(page.locator('body')).not.toContainText(/Fatal error|Warning:|Server Error/);
    await expect(page.locator('meta[name=csrf-token]')).toHaveCount(1);
    if (path === '/login' || path === '/register') await expect(page.locator('#email')).toBeVisible();
    else await expect(page.locator('main')).toBeVisible();
    await expect(page).toHaveScreenshot(path.replaceAll('/', '-') + '.png', { fullPage: true });
    expect(errors).toEqual([]);
  });
}
for (const path of ['/billing-portal', '/council', '/council/join', '/elections', '/elections/1']) {
  test('@retirement-acceptance retired surface unavailable: ' + path, async ({ request }) => {
    const response = await request.get(path, { maxRedirects: 0 });
    // Leftovers remain visible release blockers, not hidden expected failures.
    expect([404, 410]).toContain(response.status());
  });
}
test('guest cannot submit a speedrun', async ({ page }) => {
  await page.goto('/speedruns/new');
  await expect(page).toHaveURL(/\/login$/);
});
