# Security Gate Pass Mobile API

Base URL: `{APP_URL}/api`

All endpoints (except login/refresh) require an `Authorization: Bearer {accessToken}` header, obtained from `/security_login`, using Laravel Sanctum tokens.

Response envelope (all endpoints):

```json
{
  "status": true,
  "message": "Human readable message",
  "data": { },
  "meta": { }
}
```

- `status` is `true` on success, `"failed"` on validation/business errors (4xx), `"error"` on unhandled exceptions (5xx).
- `meta` is only present on paginated list endpoints.

---

## 1. POST /security_login

Authenticates a security-role employee and issues an access token + refresh token.

**Auth required:** No

### Request body

| Field | Type | Required | Notes |
|---|---|---|---|
| `EmployeeEmail` | string | yes | Employee's login email |
| `EmployeePassword` | string | yes | Plain text password (hashed server-side with MD5 for comparison) |

```json
{
  "EmployeeEmail": "security1@company.com",
  "EmployeePassword": "yourpassword"
}
```

### Responses

**200 — success**
```json
{
  "success": true,
  "message": "Logged in successfully",
  "data": {
    "accessToken": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "refreshToken": "base64-random-64-char-string",
    "employee": {
      "ID": 12,
      "EmployeeID": 1042,
      "EmployeeName": "John Doe",
      "EmployeeRole": "security",
      "Branch_ID": 3
    }
  }
}
```
Notes:
- Only employees whose `EmployeeRole` (case-insensitive) equals `security` can log in here.
- `accessToken` expires in **15 minutes**. Use `/security_refresh` to renew.
- `refreshToken` is valid for **7 days**; store it securely (Keychain/Keystore), not in plain storage.
- Logging in deletes all previously issued tokens for that employee (single active session).

**403 — not found / not allowed**
```json
{ "success": false, "message": "Access not allowed or user not found" }
```

**401 — wrong password**
```json
{ "success": false, "message": "Invalid email or password" }
```

> Note: this endpoint's raw responses use the `success` key (not the `status` key used by other endpoints) — handle both shapes in the client's auth layer.

---

## 2. POST /security_refresh

Exchanges a valid refresh token for a new access token.

**Auth required:** No (uses refresh token in body, not the Bearer header)

### Request body

| Field | Type | Required | Notes |
|---|---|---|---|
| `refresh_token` | string | yes | The `refreshToken` returned by `/security_login` |

```json
{ "refresh_token": "base64-random-64-char-string" }
```

### Responses

**200 — success**
```json
{
  "status": true,
  "message": "New access token has been issued successfully.!",
  "data": { "accessToken": "2|yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy" }
}
```
New access token expires in **15 minutes**. The refresh token itself is not rotated/renewed by this call — reuse it until its own 7-day expiry, then the user must log in again.

**401 — invalid/expired refresh token**
```json
{ "success": false, "message": "Invalid or expired refresh token" }
```

---

## 3. POST /logout

Logs out the currently authenticated employee by revoking all of their tokens.

**Auth required:** Yes

### Request body
None.

### Response

**200**
```json
{ "message": "Logged out successfully" }
```
Notes:
- Deletes **all** Sanctum access tokens and **all** refresh tokens for the authenticated employee (`request->user()`).
- After this call, the current `accessToken` and any stored `refreshToken` are both invalid — the client must clear local storage and route to the login screen. `/security_refresh` will also fail (401 `Invalid or expired refresh token`) if attempted afterward.
- Unlike other endpoints in this API, the response is **not** wrapped in the standard `status`/`message`/`data` envelope — it's a bare `{ "message": ... }` object with implicit success (HTTP 200).

---

## 4. GET /gatepass/security-stats

Dashboard counters for the logged-in security user's workshop location.

**Auth required:** Yes

### Query params
None. Workshop is derived from `request->user()->Branch_ID`.

### Response

**200**
```json
{
  "status": true,
  "message": "Security stats fetched successfully",
  "data": {
    "pending": 4,
    "verified": 2,
    "rejected": 0,
    "pending_return": 3
  }
}
```

| Field | Meaning |
|---|---|
| `pending` | Gate passes with status `QUANTITY_ISSUED` awaiting security verification at this workshop |
| `verified` | Gate passes verified (`security_status = VERIFIED`) today at this workshop |
| `rejected` | Gate passes rejected (`security_status = REJECTED`) today at this workshop |
| `pending_return` | Gate passes that are `SECURITY_CLEARED` and have at least one `RETURNABLE` item still pending return |

---

## 5. GET /gatepass/pending-security-checks

Paginated list of gate passes awaiting security verification (exit check) at the logged-in user's workshop.

**Auth required:** Yes

### Query params

| Param | Type | Default | Notes |
|---|---|---|---|
| `page` | int | 1 | |
| `per_page` | int | 20 | Max 100 |
| `search` | string | "" | Matches technician name, customer name, gate pass no, or vehicle reg. no. |

### Response

**200**
```json
{
  "status": true,
  "message": "Pending security checks fetched successfully",
  "data": [
    {
      "id": 1071,
      "gate_pass_no": "GP-2026-0456",
      "wo_number": "WO-9981",
      "customer_name": "Acme Corp",
      "customer_number": "9876543210",
      "site": "Site A",
      "department_id": 3,
      "department_name": "Repair Dept",
      "business_unit": "BU1",
      "vehicle_registration_number": "MH12AB1234",
      "remarks": "Urgent",
      "status": "QUANTITY_ISSUED",
      "created_by": 105,
      "created_date": "2026-06-30T10:15:00.000000Z",
      "security_status": null,
      "security_verified_by": null,
      "security_verified_date": null,
      "technician_name": "Rakesh Singh",
      "technician_email": "rakesh@company.com",
      "technician_contact_no": "9998887770",
      "pass_type": "General",
      "driver_name": null,
      "driver_mobile_no": null,
      "items": [
        {
          "id": 501,
          "gate_pass_id": 1071,
          "item_id": 88,
          "uom": "PCS",
          "requested_qty": 10,
          "issued_qty": 10,
          "item_type": "RETURNABLE",
          "item_image": "https://.../item.png",
          "item_code": "ITM-088",
          "is_returned": 0,
          "returned_qty": 0
        }
      ]
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 4,
    "total_pages": 1
  }
}
```
Notes:
- `pass_type` defaults to `"General"` when null.
- `supplier_name`/`supplier_mobile` are only included when `pass_type` is `"BOUGHTOUT"`; otherwise omitted from the object entirely.
- Included gate passes have `status IN ('QUANTITY_ISSUED', 'SHORTAGE_APPROVED')` and `security_status IS NULL OR = 'PENDING'`.

---

## 6. GET /gatepass/pending-returnable-checks

Paginated list of gate passes that have `RETURNABLE` items still awaiting return, at the logged-in user's workshop.

**Auth required:** Yes

### Query params

Same as #4: `page`, `per_page`, `search`.

### Response

**200**
```json
{
  "status": true,
  "message": "Pending returnable checks fetched successfully",
  "data": [
    {
      "id": 1071,
      "gate_pass_no": "GP-2026-0456",
      "wo_number": "WO-9981",
      "customer_name": "Acme Corp",
      "site": "Site A",
      "technician_name": "Rakesh Singh",
      "technician_email": "rakesh@company.com",
      "security_verified_date": "2026-07-01T09:00:00.000000Z",
      "vehicle_registration_number": "MH12AB1234",
      "pass_type": "General",
      "driver_name": null,
      "driver_mobile_no": null,
      "items": [
        {
          "id": 501,
          "gate_pass_id": 1071,
          "item_id": 88,
          "uom": "PCS",
          "requested_qty": 10,
          "issued_qty": 10,
          "item_type": "RETURNABLE",
          "item_image": "https://.../item.png",
          "item_code": "ITM-088",
          "is_returned": 0,
          "returned_qty": 3
        }
      ]
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 3,
    "total_pages": 1
  }
}
```
Notes:
- Only gate passes with `status = 'SECURITY_CLEARED'` and at least one item where `item_type` (case-insensitive) = `RETURNABLE`.
- `items` array is filtered to **only** `RETURNABLE` items (non-returnable items are excluded here, unlike endpoint #4 which returns all items).
- `returned_qty` reflects quantity already returned across previous partial `/gatepass/return-item` calls for that item.

---

## 7. POST /gatepass/verify

Security marks a gate pass as verified (allow exit) or rejected at the exit checkpoint.

**Auth required:** Yes

### Request body

| Field | Type | Required | Notes |
|---|---|---|---|
| `gate_pass_id` | string/int | yes | This is actually the `gate_pass_no`, not the numeric `id` (see implementation note below) |
| `status` | string | yes | `"VERIFIED"` or `"REJECTED"` |
| `remarks` | string | conditional | **Required** if `status = "REJECTED"` |

```json
{
  "gate_pass_id": "GP-2026-0456",
  "status": "VERIFIED",
  "remarks": ""
}
```

> Implementation note: despite the field name `gate_pass_id`, the controller matches it against `gate_pass.gate_pass_no`, not the numeric primary key. Send the gate pass number string here.

### Responses

**200 — verified**
```json
{ "status": true, "message": "Gate pass verified and exit allowed", "data": null }
```

**200 — rejected**
```json
{ "status": true, "message": "Gate pass rejected", "data": null }
```

**422 — invalid params**
```json
{ "status": "failed", "message": "Invalid parameters" }
```

**422 — missing remarks on reject**
```json
{ "status": "failed", "message": "Remarks are required when rejecting a gate pass" }
```

Side effects: sets `security_status` (`VERIFIED`/`REJECTED`), `status` (`SECURITY_CLEARED`/`SECURITY_REJECTED`), `security_verified_by`, `security_verified_date`, and writes an audit log entry.

---

## 8. POST /gatepass/return-item

Marks a returnable gate pass item as returned. Supports **partial quantity returns** — an item is only marked fully returned once the cumulative returned quantity reaches its issued/requested quantity, and the parent gate pass is only closed once **all** returnable items on it are fully returned.

**Auth required:** Yes

### Request body

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | int | yes | `gate_pass_items.id` — the specific line item being returned |
| `gate_pass_id` | int | yes | Parent gate pass numeric `id` |
| `qty` | number | no | Quantity being returned in this call. If omitted, returns the full remaining balance in one shot (legacy single-shot behavior) |

```json
{
  "id": 501,
  "gate_pass_id": 1071,
  "qty": 5
}
```

### Business rules

- Only allowed when the gate pass `status = 'SECURITY_CLEARED'`.
- Target quantity for an item = `issued_qty` (falls back to `requested_qty` if `issued_qty` is null/0).
- `qty` is added to the item's existing `returned_qty`. If the resulting total would **exceed** the target quantity, the request is rejected with a 422 — it is never silently capped.
- Once an item's `returned_qty` reaches its target, that item is flagged `is_returned = 1` and stamped with `returned_at` / `returned_by`.
- Once **every** `RETURNABLE` item on the gate pass is fully returned, the gate pass itself is set to `status = 'CLOSED'`, an audit log is written, and a "Gate Pass Return Completed" email is sent to the technician (and a fixed internal address).

### Responses

**200 — partial quantity returned (item not yet fully returned)**
```json
{
  "status": true,
  "message": "Partial quantity returned",
  "data": {
    "gatepass_id": 1071,
    "gate_pass_no": "GP-2026-0456",
    "wo_number": "WO-9981",
    "customer_name": "Acme Corp",
    "site": "Site A",
    "technician_name": "Rakesh Singh",
    "technician_email": "rakesh@company.com",
    "security_verified_date": "2026-07-01T09:00:00.000000Z",
    "vehicle_registration_number": "MH12AB1234",
    "pass_type": "General",
    "driver_name": null,
    "driver_mobile_no": null,
    "all_returned": false,
    "item_returned": false,
    "returned_qty": 5,
    "pending_qty": 3
  }
}
```

**200 — item fully returned, other items on the gate pass still pending**
```json
{
  "status": true,
  "message": "Item fully returned",
  "data": {
    "...": "same gate pass fields as above",
    "all_returned": false,
    "item_returned": true,
    "returned_qty": 8,
    "pending_qty": 0
  }
}
```

**200 — all returnable items returned, gate pass closed**
```json
{
  "status": true,
  "message": "All items returned — gate pass closed",
  "data": {
    "...": "same gate pass fields as above",
    "all_returned": true
  }
}
```

**422 — invalid ids**
```json
{ "status": "failed", "message": "Invalid parameters" }
```

**404 — gate pass or item not found**
```json
{ "status": "failed", "message": "Gate pass not found" }
```
```json
{ "status": "failed", "message": "Gate pass item not found" }
```

**422 — gate pass not eligible**
```json
{ "status": "failed", "message": "Gate pass not eligible for return marking" }
```

**422 — over-return attempt**
```json
{ "status": "failed", "message": "Return quantity exceeds requested quantity. Remaining: 3" }
```

---

## Client integration notes

1. **Token lifecycle:** access token lives 15 min; refresh proactively (e.g. on 401) using `/security_refresh` with the stored `refresh_token`. On refresh-token expiry (7 days) or `401` from `/security_refresh`, force re-login.
2. **Single session:** logging in again invalidates all previously issued access tokens for that employee — only one active device session per security user at a time.
3. **Partial returns UX:** after each `/gatepass/return-item` call, use `pending_qty` from the response to update the item row (e.g. "5 of 8 returned"), and `all_returned` to know when to remove the whole gate pass from the pending-returns list.
4. **Envelope key inconsistency:** most endpoints return `status` (boolean or `"failed"`/`"error"` string) as the top-level flag, but `/security_login` and its error branches use `success` instead — code the client's response parser to check both keys defensively.
