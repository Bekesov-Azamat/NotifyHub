API Examples

Create Notification

Request

POST "/api/notifications"

{
  "user_id": 1,
  "channel": "telegram",
  "text": "Hello from NotifyHub"
}

Example Response

{
  "data": {
    "id": 15,
    "user_id": 1,
    "channel": "telegram",
    "text": "Hello from NotifyHub",
    "status": "pending",
    "attempts": 0,
    "last_error": null,
    "sent_at": null,
    "created_at": "2026-06-14T10:00:00.000000Z",
    "updated_at": "2026-06-14T10:00:00.000000Z"
  }
}

---

Get Notification Status

Request

GET "/api/notifications/15/status"

Example Response

{
  "data": {
    "id": 15,
    "user_id": 1,
    "channel": "telegram",
    "text": "Hello from NotifyHub",
    "status": "sent",
    "attempts": 1,
    "last_error": null,
    "sent_at": "2026-06-14T10:00:03.000000Z",
    "created_at": "2026-06-14T10:00:00.000000Z",
    "updated_at": "2026-06-14T10:00:03.000000Z"
  }
}

---

Get User Notification History

Request

GET "/api/users/1/notifications"

With Filters

GET "/api/users/1/notifications?status=failed"

GET "/api/users/1/notifications?channel=telegram"

GET "/api/users/1/notifications?status=failed&channel=telegram"

---

Create Report

Request

POST "/api/reports"

{
  "user_id": 1,
  "from_date": "2026-06-01",
  "to_date": "2026-06-30"
}

Example Response

{
  "data": {
    "id": 1,
    "user_id": 1,
    "from_date": "2026-06-01T00:00:00.000000Z",
    "to_date": "2026-06-30T00:00:00.000000Z",
    "status": "pending",
    "file_path": null,
    "last_error": null,
    "created_at": "2026-06-14T10:10:00.000000Z",
    "updated_at": "2026-06-14T10:10:00.000000Z"
  }
}

---

Get Report Status

Request

GET "/api/reports/1"

Example Response

{
  "data": {
    "id": 1,
    "user_id": 1,
    "from_date": "2026-06-01T00:00:00.000000Z",
    "to_date": "2026-06-30T00:00:00.000000Z",
    "status": "completed",
    "file_path": "reports/notification-report-1.json",
    "last_error": null,
    "created_at": "2026-06-14T10:10:00.000000Z",
    "updated_at": "2026-06-14T10:10:02.000000Z"
  }
}

---

Download Report

Request

GET "/api/reports/1/download"

Response

Returns generated JSON report file.

Example:

{
  "user_id": 1,
  "period": {
    "from_date": "2026-06-01T00:00:00.000000Z",
    "to_date": "2026-06-30T00:00:00.000000Z"
  },
  "total_notifications": 6,
  "total_errors": 1,
  "notifications_by_channel": {
    "email": 2,
    "telegram": 4
  },
  "errors_by_channel": {
    "telegram": 1
  },
  "generated_at": "2026-06-14T10:10:02.000000Z"
}
