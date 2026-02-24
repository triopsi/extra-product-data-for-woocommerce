# E2E & Integration Test Plan – Extra Product Data for WooCommerce

## 1. Test Plan Identification

| Field | Value |
|-------|-------|
| **Test Plan ID** | EPDFW-E2E-TP-001 |
| **Version** | 1.0 |
| **Status** | In Progress |
| **Date** | 2026-02-22 |
| **Owner** | Triopsi

## 2. Purpose and Objectives

### Purpose
Verify that the plugin functions correctly in a realistic WooCommerce environment (Admin → Product Management → Frontend → Cart/Checkout → Order → Backend Order View) and that critical regressions are detected early in CI.

### Test Objectives
- Extra fields can be configured per product in the admin area
- Extra fields appear correctly on the frontend (based on configuration)
- User inputs are correctly saved and displayed in Cart Item and Order Item Meta
- Validation/error handling behaves correctly (required, format, length, etc.)
- AJAX import via JSON works including security (Nonce/Capabilities) and UI feedback

## 3. Test Object

**WordPress Plugin:** Extra Product Data for WooCommerce

### Main Features
- **Admin UI:** Product metabox/tab/panel for extra fields
- **Frontend:** Input fields on product page
- **Cart/Checkout:** Data transfer & display
- **Order:** Persistence & display in admin
- **Import:** AJAX JSON Import / Mapping

## 4. Test Basis (Requirements / Artifacts)

- Plugin README / Feature descriptions
- User Stories / Issues / Pull Requests
- WooCommerce Hooks/Integration Points (documented in code)
- Acceptance criteria per feature (e.g., GitHub Issues)

### Traceability
Each test case references:
- Requirement ID or GitHub Issue (e.g., REQ-ADMIN-01, GH-123)
- Risk ID (e.g., R-CHECKOUT-01)

## 5. Test Approach / Strategy (ISTQB)

### Test Levels
- **System/Integration Tests (E2E):** Focus of this plan
- *(Supplementary: Unit/Integration via PHPUnit → separate test strategy)*

### Test Types
- **Functional:** Happy Path + Negative Tests
- **Regression:** Smoke subset per PR
- **Compatibility:** Matrix (WP/WC/PHP minimum versions)
- **Security:** Capabilities/Nonce for AJAX, XSS in fields
- **Usability Basics:** UI visibility, error messages

### Coverage
- Critical user flows are prioritized (Checkout/Order Meta)
- Import/Export as separate risk block

## 6. Scope

### In Scope
- ✅ Admin product editing: Create, edit, save fields
- ✅ Frontend display and validation
- ✅ Cart/Checkout data transfer
- ✅ Order meta persistence
- ✅ Import (AJAX) including error handling
- ✅ Multi-product scenario (one product with fields, one without)
- ✅ Guest checkout / Logged-in checkout (if relevant)

### Out of Scope
- ❌ Payment provider integrations (except "Offline/BACS" as test tool)
- ❌ Performance/Load testing (separate plan)
- ❌ Multisite (separate)
- ❌ 3rd party themes (only Storefront / Default theme)

## 7. Risks & Priorities (Risk-Based Testing)

### Risk Classes
- **High:** Checkout blocker, lost order data, security (Nonce/Cap), data corruption
- **Medium:** Admin UI rendering errors, incorrect cart display
- **Low:** Styling/spacing, non-critical labels

### Top Risks
| Risk ID | Description |
|---------|-------------|
| **R-CHECKOUT-01** | Required field doesn't prevent/incorrectly blocks checkout |
| **R-ORDER-01** | Order meta not saved → support cases |
| **R-SEC-01** | Import endpoint without Cap/Nonce → security vulnerability |
| **R-REG-01** | WooCommerce update breaks hooks (tested up to) |

## 8. Test Environment

### Local/CI Environment
- **Setup:** wp-env (Docker) → WP + WC
- **Runner:** Playwright outside (CI Runner) against 127.0.0.1:8889

### Configuration
- **WordPress:** Debug on, Mail off
- **WooCommerce:** Wizard off, EU Units (kg/cm), Standard Tax 19%, Offline Payment (BACS)
- **Theme:** Storefront or Twenty Twenty-Four
- **Sample Products:** WooCommerce Sample Data or custom fixtures

### Test Data
- **Admin:** admin/password
- **Product A:** "Simple Product + Extra Fields"
- **Product B:** "Simple Product without Extra Fields"
- **JSON Import Fixtures:** `tests/fixtures/fields_valid.json`, `fields_invalid.json`

## 9. Entry/Exit Criteria

### Entry Criteria
- ✅ WP/WC running, admin login possible
- ✅ Plugin mounted, WooCommerce active
- ✅ Seed/Fixtures successfully imported

### Exit Criteria
- ✅ Smoke Suite passing (P0)
- ✅ No open P0/P1 bugs
- ✅ Report + Artifacts (Screenshots/Traces) generated

## 10. Test Cases and Suite Structure (Playwright)

### Suite 0 – Smoke (P0, PR Gate)
| Test ID | Description |
|---------|-------------|
| **SMK-01** | Admin loads + Plugin active |
| **SMK-02** | Product page shows extra fields |
| **SMK-03** | Add to cart persists extra data |
| **SMK-04** | Checkout creates order with meta |

### Suite 1 – Admin Configuration (P1)
| Test ID | Description |
|---------|-------------|
| **ADM-01** | Create field (Text) and save |
| **ADM-02** | Set field as "required", persistence after reload |
| **ADM-03** | Delete field, save, verify removed from frontend |
| **ADM-04** | Conditional/Visibility (if feature exists) |
| **ADM-05** | Permissions: Shop Manager vs Admin (if relevant) |

### Suite 2 – Frontend Validation (P0/P1)
| Test ID | Description |
|---------|-------------|
| **FE-01** | Required field empty → Error, add-to-cart blocked |
| **FE-02** | Max length / numeric format / sanitization (per field type) |
| **FE-03** | Multiple fields → all values correct |

### Suite 3 – Cart/Checkout/Order (P0)
| Test ID | Description |
|---------|-------------|
| **ORD-01** | Cart displays values correctly |
| **ORD-02** | Checkout → Order created, meta present |
| **ORD-03** | Order admin view displays meta correctly |
| **ORD-04** | Two products in cart: only one has meta → correctly separated |

### Suite 4 – Import (AJAX) (P0/P1)
| Test ID | Description |
|---------|-------------|
| **IMP-01** | Valid JSON import → Fields appear in admin |
| **IMP-02** | Invalid JSON → Error UI + no changes |
| **IMP-03** | Nonce missing/invalid → 403/Error |
| **IMP-04** | Capabilities: Non-admin cannot import |

### Suite 5 – Regression / Edge Cases (P2)
| Test ID | Description |
|---------|-------------|
| **EDGE-01** | Special chars / UTF-8 / Emojis |
| **EDGE-02** | Product duplicated / updated |
| **EDGE-03** | WooCommerce HPOS on/off (if relevant) |

## 11. Traceability Matrix

| Requirement | Test Cases |
|-------------|------------|
| **REQ-ADMIN-01** Field saving | ADM-01, ADM-02, ADM-03 |
| **REQ-FE-01** Required validation | FE-01, ORD-01 |
| **REQ-ORD-01** Persist order meta | ORD-02, ORD-03 |
| **REQ-IMP-01** JSON Import | IMP-01..IMP-04 |

## 12. Test Execution, Reporting, Artifacts

### Artifacts per CI Run
- Playwright HTML Report
- Trace (on-first-retry)
- Screenshots/Videos on failure
- Console logs (WP debug log optional as artifact)

### Reporting Strategy
- **PR:** Smoke Suite only
- **Nightly:** Full Suite + Matrix
- **Release Candidate:** Full + Compatibility matrix

## 13. Defect Management

### Severity/Priority (ISTQB)
| Level | Description |
|-------|-------------|
| **S1 Blocker** | Checkout/Order data loss/security |
| **S2 Critical** | Admin cannot configure fields |
| **S3 Major** | Incorrect display, workaround possible |
| **S4 Minor** | UI/Text issues |

### Defect Report Contents
- Steps to reproduce (Test case ID)
- Expected/Actual results
- Video/Trace link
- Environment (WP/WC/PHP versions)

## 14. Test Automation – Design Guidelines (Playwright)

### Page Objects
- `AdminLoginPage`, `ProductEditPage`, `ProductPage`, `CartPage`, `CheckoutPage`, `OrderAdminPage`

### Fixtures/Seeds
- `seedWoo.ts` (Wizard off, taxes/units, pages)
- `seedProductWithFields.ts`

### Stability
- **Selectors preferred:** `data-testid` (if added to plugin)
- **WP Admin:** Stable CSS/ARIA selectors or WP Test Utils

### Isolation
- Test data created fresh per run or DB reset (wp-env destroy/recreate in CI or DB reset via wp-cli)

## 15. Schedule / Frequency (CI)

| Trigger | Test Scope |
|---------|------------|
| **PR** | Smoke (P0) + Import Security (IMP-03/04) |
| **Nightly** | Full Suite |
| **Release Candidate** | Full + Compatibility matrix (WP/WC/PHP) |

## 16 Suites / Priorities

- `00-smoke.spec.ts` => P0 gate (PR)
- `10-admin-fields.spec.ts` => Admin configuration (P1)
- `20-checkout-order-meta.spec.ts` => Cart/Checkout/Order Meta (P0)
- `30-import-json.spec.ts` => Import & Security (P0/P1)

### Naming convention
- SMK-xx = Smoke
- ADM-xx = Admin config
- FE-xx  = Frontend validation
- ORD-xx = Checkout/Order meta
- IMP-xx = Import