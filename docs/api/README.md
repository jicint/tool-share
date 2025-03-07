# Tool Share API Documentation

## Base URL

## Authentication
All endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <your-token>
```

## Endpoints

### Tool Rental

#### 1. Rent a Tool

```http
POST /tools/{toolId}/rent
```

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| toolId    | integer | The ID of the tool to rent |

**Request Body:**
```json
{
    "start_date": "2025-02-21",
    "end_date": "2025-02-23",
    "total_price": 150.00
}
```

**Validation Rules:**
| Field | Rules |
|-------|--------|
| start_date | required, date, after:today |
| end_date | required, date, after:start_date |
| total_price | required, numeric, min:0 |

**Success Response:**
```http
HTTP/1.1 201 Created
```
```json
{
    "id": 1,
    "tool_id": 1,
    "user_id": 2,
    "start_date": "2025-02-21",
    "end_date": "2025-02-23",
    "total_price": 150.00,
    "status": "active",
    "created_at": "2025-02-20T12:00:00.000000Z",
    "updated_at": "2025-02-20T12:00:00.000000Z"
}
```

**Error Responses:**

*Own Tool Rental Attempt:*
```http
HTTP/1.1 403 Forbidden
```
```json
{
    "error": "Cannot rent your own tool"
}
```

*Tool Unavailable:*
```http
HTTP/1.1 422 Unprocessable Entity
```
```json
{
    "error": "Tool is not available"
}
```

*Overlapping Rental:*
```http
HTTP/1.1 422 Unprocessable Entity
```
```json
{
    "error": "Tool is not available for the selected dates"
}
```

#### 2. Check Tool Availability

```http
GET /tools/{toolId}/check-availability
```

Check if a tool is available and get its next available date.

**Request Headers:**
```
Authorization: Bearer <your-token>
```

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| toolId    | integer | The ID of the tool to check |

**Success Response:**
```http
HTTP/1.1 200 OK
```
```json
{
    "is_available": true,
    "next_available_date": "2025-02-24"
}
```

## Business Rules

### Tool Availability
- A tool becomes unavailable immediately after being rented
- A tool becomes available automatically when its rental period ends
- A tool cannot have overlapping rental periods
- Users cannot rent their own tools

### Rental Status
- New rentals are created with "active" status
- Rentals are automatically marked as "completed" when their end date passes
- Tool availability is automatically updated based on rental status

### Date Validation
- Rental start date must be in the future
- Rental end date must be after the start date
- Rental periods cannot overlap with existing rentals

## Error Handling

All errors return JSON responses with an "error" key containing the error message:

```json
{
    "error": "Error message here"
}
```

Common HTTP status codes:
- 200: Success
- 201: Created
- 403: Forbidden
- 422: Validation Error
- 500: Server Error

Rent a tool for a specific period.

**Request Headers:**
```
Authorization: Bearer <your-token>
Content-Type: application/json
```


**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| toolId | integer | The ID of the tool to check |
Success Response:
json{ "is_available": true, "next_available_date": "2025-02-24"}

Business Rules
Tool Availability
A tool becomes unavailable immediately after being rented
A tool becomes available automatically when its rental period ends
A tool cannot have overlapping rental periods
Users cannot rent their own tools

Rental Status
New rentals are created with "active" status
Rentals are automatically marked as "completed" when their end date passes
Tool availability is automatically updated based on rental status
Date Validation
Rental start date must be in the future
Rental end date must be after the start date
Rental periods cannot overlap with existing rentals

Error Handling
All errors return JSON responses with an "error" key containing the error message:
Common HTTP status codes:
200: Success
201: Created
403: Forbidden
422: Validation Error
500: Server Error
markdown:README.md
API Documentation
Detailed API documentation can be found in the docs/api directory.
