# HM Records Store API — Task & Test Cases

## Endpoint

```
POST /api/hm/records
```

- **Route name:** `hm.records.store`
- **Controller:** `App\Http\Controllers\HMController@store`
- **Middleware:** `api.token` → `App\Http\Middleware\PublicApiTokenMiddleware`
- **File:** [routes/api.php:25](routes/api.php#L25)

## Authentication

Requires header `x-api-key` matching the `API_ACCESS_KEY` env value.

| Condition | Response |
|---|---|
| Missing/invalid `x-api-key` | `401 Unauthorized` — `{ "message": "Unauthorized" }` |

## Task Description

This single endpoint accepts Time & Attendance (TNA) punch records from three different upstream sources, distinguished by the `tas_data_from` field in the JSON body:

| `tas_data_from` | Handler | Purpose |
|---|---|---|
| `Highmessage` | `handleHighMessage()` | Employee start/end punching via High Messaging device. Supports start-only, end-only, or full (start+end) entries. |
| `SMS` | `handleSms()` | Punch record submitted via SMS gateway. Delegates to `TnaService::updateSMSTask()`. |
| `TAS` | `handleTaskServer()` | Punch record submitted via Task Allocation Server. Delegates to `TnaService::updateTnaTask()`. |

### Common flow (`store`)

1. Decode JSON body.
2. Require `tas_data_from` to be present and one of `Highmessage`, `SMS`, `TAS`.
3. Dispatch to the matching handler.
4. Any uncaught exception is logged (`TNA Store Error`) and returns a generic error response.

### `Highmessage` flow details

1. Validate payload (see **Validation Rules** below).
2. Determine `hasStart` (`startdate` + `starttime` present) and `hasEnd` (`enddate` + `endtime` present).
3. Verify the employee exists and is `Active` (`toCheckUserStatusTaskNo`) — else `"Employee does not exist or is inactive"`.
4. Check whether the task is already open (`isTaskOpen` — a TNA entry with null `enddate`/`endtime`).
5. Resolve action (`resolveTaskAction`):
   - **End task**: `hasEnd && isTaskOpen` → `HMService::updateHM()` — closes the open entry matching company/employee/job/startdate/starttime.
   - **Full entry**: `hasStart && hasEnd && !isTaskOpen` → validates job card exists (`toCheckJobCard`), then `HMService::createFullHM()`.
   - **Start task**: `hasStart && !hasEnd && !isTaskOpen` → validates job card exists, then `HMService::createHM()`.
   - **Otherwise** (e.g. task already open and no valid end supplied): `422` — `"This job is already open. Please close the existing job before proceeding"`.

### `SMS` / `TAS` flow details

1. Validate payload (`employeecode`, `jobcode`, `tas_data_from` required).
2. Verify employee is active.
3. Verify job card exists (`toCheckJobCard`).
4. Delegate to `TnaService::updateSMSTask()` (SMS) or `TnaService::updateTnaTask()` (TAS) — both upsert a TNA entry: create a new open entry if none exists for that employee/job, or close the open one if found. If a *different* job is already open for the employee, returns an error naming that job code.

## Validation Rules

### `Highmessage` (`validateHighMessage`)

| Field | Rule |
|---|---|
| `companycode` | required |
| `employeecode` | required |
| `jobcode` | required |
| `tas_data_from` | required |
| `startdate` | required |
| `starttime` | required |

### `SMS` / `TAS` (`validateSMSMessage`)

| Field | Rule |
|---|---|
| `employeecode` | required |
| `jobcode` | required |
| `tas_data_from` | required |

## Response Shapes

**Success** (`ApiResponse::successResponse`)
```json
{
  "status": true,
  "message": "Record created successfully.",
  "data": { }
}
```

**Error** (`ApiResponse::errorResponse`)
```json
{
  "status": "error",
  "message": "Employee does not exist or is inactive",
  "errors": null
}
```

> Note: `HMService::updateHM()` returns a plain array (`success`/`message` keys) rather than the standard `ApiResponse` shape — this is an existing inconsistency in the current implementation, not intentional API design.

---

## Test Cases

### Authentication

| # | Case | Request | Expected Result |
|---|---|---|---|
| A1 | Missing `x-api-key` header | No header | `401 Unauthorized` |
| A2 | Wrong `x-api-key` value | Invalid key | `401 Unauthorized` |
| A3 | Valid `x-api-key` | Matches `API_ACCESS_KEY` | Request proceeds to controller |

### General / Source Routing

| # | Case | Payload | Expected Result |
|---|---|---|---|
| G1 | Missing `tas_data_from` | `{}` | Error: `"tas_data_from is required and must be one of: Highmessage, SMS, TAS."` |
| G2 | Invalid `tas_data_from` value | `tas_data_from: "Other"` | Same error as G1 |
| G3 | Malformed JSON body | Invalid JSON string | `422` — `"Invalid JSON payload"` |
| G4 | Unexpected exception in handler | Trigger DB/service error | Generic error `"An unexpected error occurred. Please try again later."`, error logged |

### `Highmessage` Source

| # | Case | Payload | Expected Result |
|---|---|---|---|
| H1 | Missing required field (e.g. `jobcode`) | Omit `jobcode` | `422` with field-specific validation message |
| H2 | Inactive/non-existent employee | Valid payload, unknown `employeecode` | Error: `"Employee does not exist or is inactive"` |
| H3 | Start-only entry, no open task, valid job card | `startdate`+`starttime` only | `200` — `"Record created successfully."` (via `createHM`) |
| H4 | Start-only entry, invalid job card | `startdate`+`starttime` only, bad `jobcode` | Error: `"Invalid job card. Please verify the details and try again."` |
| H5 | Start-only entry, task already open for employee/job | Same employee/job already punched in | `422` — `"This job is already open. Please close the existing job before proceeding"` |
| H6 | Full entry (start+end), no open task, valid job card | All four date/time fields | `200` — `"Full Task created successfully."` (via `createFullHM`) |
| H7 | Full entry, invalid job card | All four fields, bad `jobcode` | Error: `"Invalid job card..."` |
| H8 | End-only entry, task currently open | `enddate`+`endtime` only, matching open entry exists | `200` (or array `success: true`) — `"Job card updated and closed successfully."` (via `updateHM`) |
| H9 | End-only entry, no matching open entry | `enddate`+`endtime` only, no open record | `success: false` — `"No matching job card found for employee '...' with job code '...' on '... ...'."` |
| H10 | Neither start nor end provided | Only `companycode`, `employeecode`, `jobcode`, `tas_data_from` | Fails validation at `startdate`/`starttime` required (H1-style) |

### `SMS` Source

| # | Case | Payload | Expected Result |
|---|---|---|---|
| S1 | Missing required field | Omit `employeecode` or `jobcode` | `422` with validation message |
| S2 | Inactive/non-existent employee | Unknown `employeecode` | Error: `"Employee does not exist or is inactive"` |
| S3 | Invalid job card | Bad `jobcode` | Error: `"Invalid job card. Please verify the details and try again."` |
| S4 | No existing open punch for employee/job | New employee/job pair | `200` — `"Task created successfully."`, `Action = START` |
| S5 | Existing open punch for same employee/job | Employee already punched in on this job | `200` — `"Task updated successfully."`, `Action = CLOSED` |
| S6 | Different job already open for employee | Employee has another job open | Error: `"Job card '<code>' is already open"` |

### `TAS` Source

| # | Case | Payload | Expected Result |
|---|---|---|---|
| T1 | Missing required field | Omit `employeecode` or `jobcode` | `422` with validation message |
| T2 | Inactive/non-existent employee | Unknown `employeecode` | Error: `"Employee does not exist or is inactive"` |
| T3 | Invalid job card | Bad `jobcode` | Error: `"Invalid job card. Please verify the details and try again."` |
| T4 | No existing open punch for employee/job | New employee/job pair | `200` — `"Task created successfully."` |
| T5 | Existing open punch for same employee/job | Employee already punched in on this job | `200` — `"Task updated successfully."` |
| T6 | Different job already open for employee | Employee has another job open | Error: `"Job card '<code>' is already open"` |

### Sample Payloads

**Highmessage — start only**
```json
{
  "tas_data_from": "Highmessage",
  "companycode": "01",
  "employeecode": "EMP001",
  "jobcode": "JOB1001",
  "startdate": "2026-08-04",
  "starttime": "09.00"
}
```

**Highmessage — end only (closes open task)**
```json
{
  "tas_data_from": "Highmessage",
  "companycode": "01",
  "employeecode": "EMP001",
  "jobcode": "JOB1001",
  "startdate": "2026-08-04",
  "starttime": "09.00",
  "enddate": "2026-08-04",
  "endtime": "17.30"
}
```

**SMS**
```json
{
  "tas_data_from": "SMS",
  "employeecode": "EMP001",
  "jobcode": "JOB1001"
}
```

**TAS**
```json
{
  "tas_data_from": "TAS",
  "employeecode": "EMP001",
  "jobcode": "JOB1001"
}
```
