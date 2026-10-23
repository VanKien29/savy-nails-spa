import {defineConfig} from '@playwright/test';
export default defineConfig({
    testDir:'tests/Browser', timeout:60000, workers:1, reporter:[['list'],['html',{open:'never'}]],
    use:{baseURL:process.env.PLAYWRIGHT_BASE_URL||'http://127.0.0.1:8000',headless:true,trace:'retain-on-failure',screenshot:'only-on-failure'},
    webServer:{command:`"${process.env.STARTER_PHP||'php'}" artisan serve --host=127.0.0.1 --port=8000`,url:'http://127.0.0.1:8000',reuseExistingServer:!process.env.CI,timeout:120000},
});
