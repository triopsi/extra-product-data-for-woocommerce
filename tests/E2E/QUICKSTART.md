# Playwright E2E Quick Start

## Run From Repo Root

Install root dependencies:

```bash
composer install
npm install
cd tests/E2E && npm install
```

Start the local WordPress test environment from the repository root:

```bash
npm run wp:start
bash ./bin/set-up-test-env.sh
```

Run Playwright suites from the repository root:

```bash
cd tests/E2E
npm run e2e:setup
npm run e2e:smoke
npm run e2e:fields
npm run e2e:checkout
npm run e2e:backend
npm run e2e:import-export
npm run e2e
```

## Suite Structure

The tests are grouped by area under `tests/E2E/testcases`:

- `setup/`
- `smoke/`
- `fields/`
- `checkout/`
- `backend/import-export/`

Playwright discovers spec files recursively below `testcases/`, so additional subfolders can be added when the suite grows.

## Useful Commands

```bash
npm run e2e:headed
npm run e2e:debug
npm run e2e:ui
npm run e2e:report
```

## Notes

- `tests/E2E/playwright.config.js` uses `testDir: './testcases'`.
- The smoke suite is intended as the fast PR gate.
- The HTML report is written to `tests/E2E/playwright-report`.
