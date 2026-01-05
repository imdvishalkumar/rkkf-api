# User & Instructor API Documentation

Complete API documentation for User and Instructor operations: **Login, Create, Update, Delete**

---

## Base URL

```
http://your-domain.com/api
```

---

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

Tokens are returned upon successful login or registration.

---

## 📋 User APIs (Role = 0)

### 1. Register User

**Endpoint:** `POST /api/frontend/user/register`

**Description:** Register a new user account (role = 0)

**Authentication:** Not required (Public)

**Request Body:**
```json
{
    "firstname": "John",
    "lastname": "Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "mobile": "+1234567890"
}
```

**Validation Rules:**
- `firstname`: required, string, max:50
- `lastname`: required, string, max:50
- `email`: required, email, unique, max:100
- `password`: required, string, min:6
- `mobile`: optional, string, max:15

**Success Response (200):**
```json
{
    "status": true,
    "message": "User created successfully",
    "data": {
        "user": {
            "user_id": 1,
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@example.com",
            "mobile": "+1234567890",
            "role": 0
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    },
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

**Error Response (422 - Validation Error):**
```json
{
    "status": false,
    "message": "Validation failed",
    "data": null,
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

### 2. Login User

**Endpoint:** `POST /api/frontend/user/login`

**Description:** Login as a user (role = 0)

**Authentication:** Not required (Public)

**Request Body:**
```json
{
    "email": "john.doe@example.com",
    "password": "password123"
}
```

**Validation Rules:**
- `email`: required, email
- `password`: required, string

**Success Response (200):**
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "user": {
            "user_id": 1,
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@example.com",
            "mobile": "+1234567890",
            "role": 0
        },
        "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    },
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

**Error Response (401 - Invalid Credentials):**
```json
{
    "status": false,
    "message": "Invalid credentials",
    "data": null,
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

### 3. Update User

**Endpoint:** `PUT /api/frontend/user/{id}`

**Description:** Update user profile. User can only update their own profile.

**Authentication:** Required (Bearer Token)

**URL Parameters:**
- `id` (integer): User ID (must match authenticated user)

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (All fields optional):**
```json
{
    "firstname": "John Updated",
    "lastname": "Doe Updated",
    "mobile": "+9876543210",
    "password": "newpassword123"
}
```

**Validation Rules:**
- `firstname`: optional, string, max:50
- `lastname`: optional, string, max:50
- `mobile`: optional, string, max:15
- `password`: optional, string, min:6
- `email`: cannot be updated (for security)

**Success Response (200):**
```json
{
    "status": true,
    "message": "User updated successfully",
    "data": {
        "user": {
            "user_id": 1,
            "firstname": "John Updated",
            "lastname": "Doe Updated",
            "email": "john.doe@example.com",
            "mobile": "+9876543210",
            "role": 0
        }
    },
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

**Error Response (403 - Forbidden):**
```json
{
    "status": false,
    "message": "You can only update your own profile",
    "data": null,
    "errors": null
}
```

---

### 4. Delete User

**Endpoint:** `DELETE /api/frontend/user/{id}`

**Description:** Delete user account. User can only delete their own account. All tokens are revoked.

**Authentication:** Required (Bearer Token)

**URL Parameters:**
- `id` (integer): User ID (must match authenticated user)

**Request Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "status": true,
    "message": "User deleted successfully",
    "data": null,
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

**Error Response (403 - Forbidden):**
```json
{
    "status": false,
    "message": "You can only delete your own account",
    "data": null,
    "errors": null
}
```

---

## 👨‍🏫 Instructor APIs (Role = 2)

### 1. Register Instructor

**Endpoint:** `POST /api/frontend/instructor/register`

**Description:** Register a new instructor account (role = 2)

**Authentication:** Not required (Public)

**Request Body:**
```json
{
    "firstname": "Jane",
    "lastname": "Smith",
    "email": "jane.smith@example.com",
    "password": "password123",
    "mobile": "+1234567890"
}
```

**Validation Rules:**
- `firstname`: required, string, max:50
- `lastname`: required, string, max:50
- `email`: required, email, unique, max:100
- `password`: required, string, min:6
- `mobile`: optional, string, max:15

**Success Response (200):**
```json
{
    "status": true,
    "message": "Instructor registered successfully",
    "data": {
        "instructor": {
            "user_id": 2,
            "firstname": "Jane",
            "lastname": "Smith",
            "email": "jane.smith@example.com",
            "mobile": "+1234567890",
            "role": 2
        },
        "token": "3|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    },
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

---

### 2. Login Instructor

**Endpoint:** `POST /api/frontend/instructor/login`

**Description:** Login as an instructor (role = 2)

**Authentication:** Not required (Public)

**Request Body:**
```json
{
    "email": "jane.smith@example.com",
    "password": "password123"
}
```

**Validation Rules:**
- `email`: required, email
- `password`: required, string

**Success Response (200):**
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "instructor": {
            "user_id": 2,
            "firstname": "Jane",
            "lastname": "Smith",
            "email": "jane.smith@example.com",
            "mobile": "+1234567890",
            "role": 2
        },
        "token": "4|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    },
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

---

### 3. Update Instructor

**Endpoint:** `PUT /api/frontend/instructor/{id}`

**Description:** Update instructor profile. Instructor can only update their own profile.

**Authentication:** Required (Bearer Token)

**URL Parameters:**
- `id` (integer): Instructor ID (must match authenticated instructor)

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (All fields optional):**
```json
{
    "firstname": "Jane Updated",
    "lastname": "Smith Updated",
    "mobile": "+9876543210",
    "password": "newpassword123"
}
```

**Validation Rules:**
- `firstname`: optional, string, max:50
- `lastname`: optional, string, max:50
- `mobile`: optional, string, max:15
- `password`: optional, string, min:6
- `email`: cannot be updated (for security)

**Success Response (200):**
```json
{
    "status": true,
    "message": "User updated successfully",
    "data": {
        "instructor": {
            "user_id": 2,
            "firstname": "Jane Updated",
            "lastname": "Smith Updated",
            "email": "jane.smith@example.com",
            "mobile": "+9876543210",
            "role": 2
        }
    },
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

**Error Response (403 - Forbidden):**
```json
{
    "status": false,
    "message": "You can only update your own profile",
    "data": null,
    "errors": null
}
```

---

### 4. Delete Instructor

**Endpoint:** `DELETE /api/frontend/instructor/{id}`

**Description:** Delete instructor account. Instructor can only delete their own account. All tokens are revoked.

**Authentication:** Required (Bearer Token)

**URL Parameters:**
- `id` (integer): Instructor ID (must match authenticated instructor)

**Request Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "status": true,
    "message": "Instructor deleted successfully",
    "data": null,
    "errors": null,
    "meta": {
        "timestamp": "2026-01-02T12:00:00+00:00",
        "version": "1.0"
    }
}
```

**Error Response (403 - Forbidden):**
```json
{
    "status": false,
    "message": "You can only delete your own account",
    "data": null,
    "errors": null
}
```

---

## 🔐 Password Handling

The API supports both:
- **Plain text passwords** (legacy system compatibility)
- **Bcrypt hashed passwords** (new registrations)

When logging in, the system checks both formats automatically.

---

## 📊 Role Values

- `0` = User (Regular User)
- `1` = Admin (Super Admin)
- `2` = Instructor

---

## ⚠️ Error Codes

| Status Code | Description |
|------------|-------------|
| 200 | Success |
| 400 | Bad Request |
| 401 | Unauthorized (Invalid credentials) |
| 403 | Forbidden (Permission denied) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Internal Server Error |

---

## 📝 Notes

1. **Email Uniqueness:** Email addresses must be unique across all users
2. **Self-Service Only:** Users/Instructors can only update/delete their own accounts
3. **Token Revocation:** Deleting an account revokes all associated tokens
4. **Password Hashing:** New registrations automatically hash passwords using bcrypt
5. **Email Immutability:** Email addresses cannot be updated after registration (for security)

---

## 🚀 Quick Start

1. **Import Postman Collection:** Import `API_COLLECTION_USER_INSTRUCTOR.json` into Postman
2. **Set Base URL:** Update the `base_url` variable in Postman
3. **Register/Login:** Start with Register or Login endpoints
4. **Save Token:** Copy the token from response and set it as `user_token` or `instructor_token` variable
5. **Use Protected Endpoints:** Token is automatically included in Authorization header

---

## 📞 Support

For issues or questions, refer to:
- `CORE_REVIEW_FIXES.md` - Known issues and fixes
- `SANCTUM_SETUP.md` - Authentication setup
- `FIX_PASSWORD_COLUMN.md` - Database migration guide

