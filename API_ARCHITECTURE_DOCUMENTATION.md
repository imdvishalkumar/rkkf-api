# API Architecture Documentation

## Overview

This document describes the complete API architecture for User, Instructor, and Super Admin management with CRUD operations, authentication, and clean folder structure.

## Folder Structure

```
app/Http/Controllers/Api/
 ├── FrontendAPI/
 │     ├── UserController.php
 │     ├── InstructorController.php
 │     └── AuthController.php
 │
 └── AdminAPI/
       ├── SuperAdminController.php
       ├── UserManagementController.php
       └── InstructorManagementController.php
```

## Architecture Layers

### 1. Controller Layer
- **FrontendAPI Controllers**: Handle frontend user and instructor operations
- **AdminAPI Controllers**: Handle admin operations for managing users, instructors, and super admins

### 2. Service Layer
- **UserService**: Business logic for user operations (already exists)
- Located in: `app/Services/UserService.php`

### 3. Repository Layer
- **UserRepository**: Data access layer (already exists)
- Located in: `app/Repositories/UserRepository.php`
- Interface: `app/Repositories/Contracts/UserRepositoryInterface.php`

### 4. Request Validation Layer
- **FrontendAPI Requests**: `app/Http/Requests/FrontendAPI/`
- **AdminAPI Requests**: `app/Http/Requests/AdminAPI/`

### 5. API Resources Layer
- **UserResource**: `app/Http/Resources/UserResource.php`
- **InstructorResource**: `app/Http/Resources/InstructorResource.php`
- **SuperAdminResource**: `app/Http/Resources/SuperAdminResource.php`

## API Endpoints

### FrontendAPI - User Endpoints

#### Public Routes (No Authentication Required)

1. **Register User**
   - `POST /api/frontend/user/register`
   - Request Body:
     ```json
     {
       "firstname": "John",
       "lastname": "Doe",
       "email": "john@example.com",
       "password": "password123",
       "mobile": "1234567890"
     }
     ```
   - Response: User object with token

2. **Login User**
   - `POST /api/frontend/user/login`
   - Request Body:
     ```json
     {
       "email": "john@example.com",
       "password": "password123"
     }
     ```
   - Response: User object with token

#### Protected Routes (Authentication Required)

3. **Update User**
   - `PUT /api/frontend/user/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Request Body: Same as register (all fields optional)
   - Response: Updated user object

4. **Delete User**
   - `DELETE /api/frontend/user/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Response: Success message

### FrontendAPI - Instructor Endpoints

#### Public Routes (No Authentication Required)

1. **Register Instructor**
   - `POST /api/frontend/instructor/register`
   - Request Body: Same as user register
   - Response: Instructor object with token

2. **Login Instructor**
   - `POST /api/frontend/instructor/login`
   - Request Body: Same as user login
   - Response: Instructor object with token

#### Protected Routes (Authentication Required)

3. **Update Instructor**
   - `PUT /api/frontend/instructor/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Request Body: Same as register (all fields optional)
   - Response: Updated instructor object

4. **Delete Instructor**
   - `DELETE /api/frontend/instructor/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Response: Success message

### AdminAPI - Super Admin Endpoints

#### Public Routes (No Authentication Required)

1. **Login Super Admin**
   - `POST /api/admin/super-admin/login`
   - Request Body:
     ```json
     {
       "email": "admin@rkkf.com",
       "password": "admin123"
     }
     ```
   - Response: Super admin object with token

#### Protected Routes (Super Admin Authentication Required)

2. **Register Super Admin**
   - `POST /api/admin/super-admin/register`
   - Headers: `Authorization: Bearer {token}`
   - Request Body: Same as user register
   - Response: Super admin object with token

3. **Update Super Admin**
   - `PUT /api/admin/super-admin/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Request Body: Same as register (all fields optional)
   - Response: Updated super admin object

4. **Delete Super Admin**
   - `DELETE /api/admin/super-admin/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Response: Success message

### AdminAPI - User Management Endpoints

All routes require Super Admin authentication.

1. **List All Users**
   - `GET /api/admin/users`
   - Headers: `Authorization: Bearer {token}`
   - Query Parameters: `role` (optional filter)
   - Response: Array of user objects

2. **Get User by ID**
   - `GET /api/admin/users/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Response: User object

3. **Create User**
   - `POST /api/admin/users`
   - Headers: `Authorization: Bearer {token}`
   - Request Body:
     ```json
     {
       "firstname": "John",
       "lastname": "Doe",
       "email": "john@example.com",
       "password": "password123",
       "role": 0,
       "mobile": "1234567890"
     }
     ```
   - Response: Created user object

4. **Update User**
   - `PUT /api/admin/users/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Request Body: Same as create (all fields optional)
   - Response: Updated user object

5. **Delete User**
   - `DELETE /api/admin/users/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Response: Success message

### AdminAPI - Instructor Management Endpoints

All routes require Super Admin authentication.

1. **List All Instructors**
   - `GET /api/admin/instructors`
   - Headers: `Authorization: Bearer {token}`
   - Response: Array of instructor objects

2. **Get Instructor by ID**
   - `GET /api/admin/instructors/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Response: Instructor object

3. **Create Instructor**
   - `POST /api/admin/instructors`
   - Headers: `Authorization: Bearer {token}`
   - Request Body: Same as user create (role is automatically set to 2)
   - Response: Created instructor object

4. **Update Instructor**
   - `PUT /api/admin/instructors/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Request Body: Same as create (all fields optional)
   - Response: Updated instructor object

5. **Delete Instructor**
   - `DELETE /api/admin/instructors/{id}`
   - Headers: `Authorization: Bearer {token}`
   - Response: Success message

## Authentication

### Token-Based Authentication (Laravel Sanctum)

- All protected routes require a Bearer token in the Authorization header
- Tokens are generated upon successful login/registration
- Token format: `Authorization: Bearer {token}`

### Password Handling

- Supports both plain text (legacy) and hashed passwords for backward compatibility
- New registrations will have hashed passwords (more secure)
- Existing users with plain text passwords can still login

## Database Schema

### Users Table

The `users` table contains all user types (User, Instructor, Super Admin) differentiated by the `role` field:

- **user_id** (Primary Key): Auto-incrementing integer
- **firstname**: VARCHAR(50)
- **lastname**: VARCHAR(50)
- **email**: VARCHAR(100), Unique
- **password**: VARCHAR(255) - Supports both plain text and hashed
- **mobile**: VARCHAR(15), Nullable
- **role**: INTEGER
  - `0` = Regular User
  - `1` = Super Admin
  - `2` = Instructor
- **created_at**: TIMESTAMP
- **updated_at**: TIMESTAMP

## Response Format

All API responses follow this structure:

### Success Response
```json
{
  "status": true,
  "message": "Success message",
  "data": {
    // Response data
  },
  "errors": null,
  "meta": {
    "timestamp": "2025-01-02T12:00:00Z",
    "version": "1.0"
  }
}
```

### Error Response
```json
{
  "status": false,
  "message": "Error message",
  "data": null,
  "errors": {
    "field": ["Error message"]
  },
  "meta": {
    "timestamp": "2025-01-02T12:00:00Z",
    "version": "1.0"
  }
}
```

## Testing with Postman

### Setup

1. Base URL: `http://localhost:8000/api` (or your Laravel app URL)
2. For protected routes, add header: `Authorization: Bearer {token}`

### Example Workflow

1. **Register a User**
   - POST `/api/frontend/user/register`
   - Save the token from response

2. **Login User**
   - POST `/api/frontend/user/login`
   - Save the token from response

3. **Update User Profile**
   - PUT `/api/frontend/user/{user_id}`
   - Header: `Authorization: Bearer {token}`
   - Body: Update fields

4. **Super Admin Login**
   - POST `/api/admin/super-admin/login`
   - Use credentials: `admin@rkkf.com` / `admin123`
   - Save the token

5. **List All Users (Admin)**
   - GET `/api/admin/users`
   - Header: `Authorization: Bearer {admin_token}`

## Security Features

1. **Role-Based Access Control**: Different endpoints require different roles
2. **Token Authentication**: All protected routes require valid tokens
3. **Password Hashing**: New passwords are hashed (backward compatible with plain text)
4. **Email Uniqueness**: Email addresses must be unique across all users
5. **Self-Update Protection**: Users can only update their own profiles (FrontendAPI)
6. **Admin-Only Operations**: User/Instructor management requires Super Admin role

## Notes

- All existing fields from the old Core PHP Super Admin schema are preserved
- API response structure is backward compatible
- Folder structure clearly separates FrontendAPI and AdminAPI
- Uses Laravel Sanctum for token-based authentication
- Supports both plain text and hashed passwords for backward compatibility






