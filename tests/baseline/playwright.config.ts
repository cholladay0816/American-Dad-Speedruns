import { defineConfig, devices } from '@playwright/test';
const baseURL = process.env.ADSR_BASE_URL || 'http://127.0.0.1:8085';
const evidence = process.env.ADSR_EVIDENCE || 'test-results';
const target = new URL(baseURL);
if (!['127.0.0.1', 'localhost', 'app'].includes(target.hostname)) {
  throw new Error('Baseline harness accepts only the isolated local app.');
}
export default defineConfig({
  testDir: './browser',
  testIgnore: process.env.ADSR_RANKING_FIXTURE === '1' ? [] : ['**/ranking.spec.ts'],
  workers: 1,
  timeout: 90000,
  expect: { timeout: 15000 },
  outputDir: './' + evidence,
  fullyParallel: false,
  forbidOnly: true,
  retries: 0,
  reporter: [['list'], ['html', { open: 'never', outputFolder: evidence + '-report' }], ['junit', { outputFile: evidence + '/results.xml' }]],
  use: { baseURL, trace: 'on', video: 'on', screenshot: 'on', serviceWorkers: 'block' },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
    { name: 'webkit', use: { ...devices['Desktop Safari'] } },
    { name: 'mobile-chrome', use: { ...devices['Pixel 7'] } },
    { name: 'mobile-safari', use: { ...devices['iPhone 13'] } }
  ]
});
