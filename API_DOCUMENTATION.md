# Sunstar Logistics API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your_token_here}
```

---

## Authentication Endpoints

### 1. Admin Login
**Endpoint:** `POST /api/auth/admin/login`

**Request Body:**
```json
{
    "email": "admin@sunstar.com",
    "password": "password123"
}
```

**Response (Success - 200):**
```json
{
    "success": true,
    "message": "Admin login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@sunstar.com",
            "roles": ["Admin"]
        },
        "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
        "token_type": "Bearer"
    }
}
```

**Response (Error - 422):**
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

### 2. Client Registration
**Endpoint:** `POST /api/auth/client/register`

**Request Body:**
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "business_name": "ACME Corporation",
    "email": "john@acme.com",
    "password": "password123",
    "password_confirmation": "password123",
    "mobile": "+1234567890",
    "address": "123 Main St, New York, NY",
    "preferred_city": "New York",
    "currency_code": "USD"
}
```

**Required Fields:**
- `first_name`
- `email`
- `password`
- `password_confirmation`

**Optional Fields:**
- `last_name`
- `business_name`
- `mobile`
- `address`
- `preferred_city`
- `currency_code` (default: USD)

**Response (Success - 201):**
```json
{
    "success": true,
    "message": "Client registration successful",
    "data": {
        "client": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "business_name": "ACME Corporation",
            "email": "john@acme.com",
            "currency_code": "USD",
            "credit_limit": 0,
            "available_credit": 0
        },
        "token": "2|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
        "token_type": "Bearer"
    }
}
```

---

### 3. Client Login
**Endpoint:** `POST /api/auth/client/login`

**Request Body:**
```json
{
    "email": "john@acme.com",
    "password": "password123"
}
```

**Response (Success - 200):**
```json
{
    "success": true,
    "message": "Client login successful",
    "data": {
        "client": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "business_name": "ACME Corporation",
            "email": "john@acme.com",
            "currency_code": "USD",
            "credit_limit": 5000,
            "available_credit": 3500
        },
        "token": "3|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
        "token_type": "Bearer"
    }
}
```

---

### 4. Driver Login
**Endpoint:** `POST /api/auth/driver/login`

**Request Body:**
```json
{
    "email": "driver@sunstar.com",
    "password": "password123"
}
```

**Response (Success - 200):**
```json
{
    "success": true,
    "message": "Driver login successful",
    "data": {
        "driver": {
            "id": 1,
            "driver_id": "DRV-1-001",
            "first_name": "Mike",
            "last_name": "Driver",
            "email": "driver@sunstar.com",
            "mobile": "+1234567890",
            "client_id": 1
        },
        "token": "4|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
        "token_type": "Bearer"
    }
}
```

---

### 5. Logout
**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (Success - 200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### 6. Get Current User
**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Response for Admin (200):**
```json
{
    "success": true,
    "data": {
        "type": "admin",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@sunstar.com",
            "roles": ["Admin"]
        }
    }
}
```

**Response for Client (200):**
```json
{
    "success": true,
    "data": {
        "type": "client",
        "client": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "business_name": "ACME Corporation",
            "email": "john@acme.com",
            "currency_code": "USD",
            "credit_limit": 5000,
            "available_credit": 3500
        }
    }
}
```

**Response for Driver (200):**
```json
{
    "success": true,
    "data": {
        "type": "driver",
        "driver": {
            "id": 1,
            "driver_id": "DRV-1-001",
            "first_name": "Mike",
            "last_name": "Driver",
            "email": "driver@sunstar.com",
            "mobile": "+1234567890",
            "client_id": 1
        }
    }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
    "message": "The email field is required. (and 1 more error)",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
    "message": "This action is unauthorized."
}
```

---

## Testing the API

### Using cURL

**Admin Login:**
```bash
curl -X POST http://your-domain.com/api/auth/admin/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@sunstar.com","password":"password123"}'
```

**Client Registration:**
```bash
curl -X POST http://your-domain.com/api/auth/client/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@acme.com",
    "password": "password123",
    "password_confirmation": "password123",
    "business_name": "ACME Corp"
  }'
```

**Get Current User:**
```bash
curl -X GET http://your-domain.com/api/auth/me \
  -H "Authorization: Bearer {your_token}" \
  -H "Accept: application/json"
```

**Logout:**
```bash
curl -X POST http://your-domain.com/api/auth/logout \
  -H "Authorization: Bearer {your_token}" \
  -H "Accept: application/json"
```

---

## Available Currency Codes

Supported currencies for client registration and invoices:
- **USD** - US Dollar ($)
- **EUR** - Euro (€)
- **GBP** - British Pound (£)
- **CAD** - Canadian Dollar (C$)
- **AUD** - Australian Dollar (A$)
- **JPY** - Japanese Yen (¥)
- **CNY** - Chinese Yuan (¥)
- **INR** - Indian Rupee (₹)
- **MXN** - Mexican Peso ($)
- **BRL** - Brazilian Real (R$)
- **CHF** - Swiss Franc (CHF)
- **SGD** - Singapore Dollar (S$)
- **NZD** - New Zealand Dollar (NZ$)
- **ZAR** - South African Rand (R)
- **KRW** - South Korean Won (₩)

---

## Next Steps

Additional API endpoints will be documented here as they are developed:
- Client Dashboard
- Driver Bookings
- Container Management
- Invoice Management
- etc.

