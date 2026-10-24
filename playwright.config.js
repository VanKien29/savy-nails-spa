import {defineConfig} from '@playwright/test';
const port=process.env.STARTER_TEST_PORT||'8091';
export default defineConfig({
    testDir:'tests/Browser', timeout:60000, workers:1, reporter:[['list'],['html',{open:'never'}]],
    use:{baseURL:process.env.PLAYWRIGHT_BASE_URL||`http://127.0.0.1:${port}`,headless:true,trace:'retain-on-failure',screenshot:'only-on-failure'},
    webServer:{command:`"${process.env.STARTER_PHP||'php'}" artisan serve --host=127.0.0.1 --port=${port}`,url:`http://127.0.0.1:${port}`,reuseExistingServer:!process.env.CI,timeout:120000},
});
