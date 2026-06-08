# TaskFlow API

A Laravel 13 REST API with token-based authentication powered by **Laravel Sanctum**.
The codebase follows a layered architecture: **Controller → Service → Repository**, with
contracts (interfaces) bound through dedicated service providers, and authorization handled
by **Policies**.

## Tech Stack

| Component       | Version          |
| --------------- | ---------------- |
| PHP             | ^8.3             |
| Laravel         | ^13.8            |
| Laravel Sanctum | ^4.0             |
| Database        | SQLite (default) |
| Testing         | PHPUnit ^12      |

---

## Requirements

- PHP **8.3+** with the usual Laravel extensions (`pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`)
- [Composer](https://getcomposer.org/)
- (Optional) Node.js + npm — only needed if you build front-end assets

> On Windows the project is set up to run under **Laragon** (`C:\laragon\www\taskflow`),
> but any environment with PHP + Composer works.

---

## Getting Started

### 1. Install dependencies

```bash
composer install
```

### 2. Environment file

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database

The project uses **SQLite** out of the box (`DB_CONNECTION=sqlite`). Create the database file
and run the migrations:

```bash
# create the empty SQLite file (Windows PowerShell)
New-Item -ItemType File database\database.sqlite

# or on Linux/macOS
touch database/database.sqlite

php artisan migrate
```

> Prefer MySQL? Set the `DB_*` variables in `.env` (`DB_CONNECTION=mysql`, `DB_HOST`,
> `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) and re-run `php artisan migrate`.

### 4. Run the server

```bash
php artisan serve
```

The API is now available at **http://127.0.0.1:8000**.

> Shortcut: `composer run dev` starts the server, queue worker, log viewer (Pail) and Vite
> together via `concurrently`.

### 5. Run tests

```bash
php artisan test
# or
composer test
```

---

## Authentication

The API uses **Sanctum personal access tokens**. After a successful `register` or `login`
you receive a `token` in the response. Send it on protected endpoints using the
`Authorization: Bearer <token>` header.

Always send these headers:

```
Accept: application/json
Content-Type: application/json
```

### Authorization

Resource endpoints are protected by **policies**:

- **Users** — a user may only `show`, `update`, or `delete` **their own** account.
- **Projects** — a user may only `show`, `update`, or `delete` projects **they own**
  (`owner_id`). The project list (`GET /project`) returns only the authenticated user's projects.

Acting on a record you don't own returns `403 This action is unauthorized.`

---

## API Endpoints

Base URL: `http://127.0.0.1:8000/api`

### Auth

| Method | Endpoint         | Auth         | Throttle    | Description                        |
| ------ | ---------------- | ------------ | ----------- | ---------------------------------- |
| POST   | `/auth/register` | Public       | 6 req / min | Register a new user, returns token |
| POST   | `/auth/login`    | Public       | 6 req / min | Log in, returns token              |
| POST   | `/auth/logout`   | Bearer token | 6 req / min | Revoke the current user's tokens   |

### Users

| Method      | Endpoint       | Auth         | Description                |
| ----------- | -------------- | ------------ | -------------------------- |
| GET         | `/user/{user}` | Bearer token | Show a user (owner only)   |
| PUT / PATCH | `/user/{user}` | Bearer token | Update a user (owner only) |
| DELETE      | `/user/{user}` | Bearer token | Delete a user (owner only) |

### Projects

| Method      | Endpoint             | Auth         | Description                            |
| ----------- | -------------------- | ------------ | -------------------------------------- |
| GET         | `/project`           | Bearer token | List the authenticated user's projects |
| POST        | `/project`           | Bearer token | Create a project (owned by caller)     |
| GET         | `/project/{project}` | Bearer token | Show a project (owner only)            |
| PUT / PATCH | `/project/{project}` | Bearer token | Update a project (owner only)          |
| DELETE      | `/project/{project}` | Bearer token | Delete a project (owner only)          |

---

## Auth

### 1. Register

`POST /api/auth/register`

**Body parameters**

| Field                   | Type   | Rules                                     |
| ----------------------- | ------ | ----------------------------------------- |
| `name`                  | string | required                                  |
| `email`                 | string | required, valid email, unique in `users`  |
| `password`              | string | required, min 6, must match confirmation  |
| `password_confirmation` | string | required, must equal `password`           |

**Request**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Response `200 OK`**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
}
```

**Validation error `422`**

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### 2. Login

`POST /api/auth/login`

**Body parameters**

| Field      | Type   | Rules                 |
| ---------- | ------ | --------------------- |
| `email`    | string | required, valid email |
| `password` | string | required              |

**Request**

```json
{
  "email": "john@example.com",
  "password": "secret123"
}
```

**Response `200 OK`**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "2|zYxWvUtSrQpOnMlKjIhGfEdCbA..."
}
```

**Invalid credentials `422`**

```json
{
  "message": "Wrong email or password.",
  "errors": {
    "email": ["Wrong email or password."]
  }
}
```

---

### 3. Logout

`POST /api/auth/logout`

**Headers**

```
Authorization: Bearer <token>
```

Revokes **all** tokens belonging to the authenticated user.

**Response `200 OK`**

```json
{
  "message": "Logged out"
}
```

---

## Users

### 4. Show User

`GET /api/user/{user}`

Returns the user identified by `{user}`. Allowed only when `{user}` is the authenticated user.

**Headers**

```
Authorization: Bearer <token>
```

**Response `200 OK`**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Forbidden `403`** — when requesting another user's record:

```json
{
  "message": "This action is unauthorized."
}
```

---

### 5. Update User

`PUT|PATCH /api/user/{user}`

Partial update — every field is optional (`sometimes`); only the fields you send are validated
and updated. Allowed only for the account's owner.

**Body parameters**

| Field                   | Type   | Rules                                                      |
| ----------------------- | ------ | ---------------------------------------------------------- |
| `name`                  | string | optional, string, max 255                                  |
| `email`                 | string | optional, valid email, unique in `users` (ignores own row) |
| `password`              | string | optional, string, min 8, must match confirmation           |
| `password_confirmation` | string | required only when `password` is present                   |

**Request**

```json
{
  "name": "John Updated",
  "email": "john.updated@example.com"
}
```

**Response `200 OK`**

```json
{
  "data": {
    "id": 1,
    "name": "John Updated",
    "email": "john.updated@example.com"
  }
}
```

---

### 6. Delete User

`DELETE /api/user/{user}`

Deletes the user. Allowed only for the account's owner.

**Response `200 OK`**

```json
{
  "message": "User deleted"
}
```

---

## Projects

A **project** belongs to a user (`owner_id`) and has a `status` of `active` or `archived`
(defaults to `active`). Every project endpoint requires a Bearer token.

**Project object**

```json
{
  "id": 1,
  "name": "Website redesign",
  "description": "Q3 marketing site",
  "status": "active",
  "created_at": "2026-06-08 11:30:00",
  "updated_at": "2026-06-08 11:30:00"
}
```

### 7. List Projects

`GET /api/project`

Returns only the authenticated user's projects.

**Response `200 OK`**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Website redesign",
      "description": "Q3 marketing site",
      "status": "active",
      "created_at": "2026-06-08 11:30:00",
      "updated_at": "2026-06-08 11:30:00"
    }
  ]
}
```

---

### 8. Create Project

`POST /api/project`

The new project is automatically owned by the authenticated user.

**Body parameters**

| Field         | Type   | Rules                        |
| ------------- | ------ | ---------------------------- |
| `name`        | string | required, string, max 255    |
| `description` | string | optional, string, max 1000   |

**Request**

```json
{
  "name": "Website redesign",
  "description": "Q3 marketing site"
}
```

**Response `201 Created`**

```json
{
  "data": {
    "id": 1,
    "name": "Website redesign",
    "description": "Q3 marketing site",
    "status": "active",
    "created_at": "2026-06-08 11:30:00",
    "updated_at": "2026-06-08 11:30:00"
  }
}
```

---

### 9. Show Project

`GET /api/project/{project}`

Allowed only for the project's owner.

**Response `200 OK`**

```json
{
  "data": {
    "id": 1,
    "name": "Website redesign",
    "description": "Q3 marketing site",
    "status": "active",
    "created_at": "2026-06-08 11:30:00",
    "updated_at": "2026-06-08 11:30:00"
  }
}
```

---

### 10. Update Project

`PUT|PATCH /api/project/{project}`

Partial update — all fields optional. Allowed only for the project's owner.

**Body parameters**

| Field         | Type   | Rules                                       |
| ------------- | ------ | ------------------------------------------- |
| `name`        | string | optional, string, max 255                   |
| `description` | string | optional, string, max 1000                  |
| `status`      | string | optional, one of `active`, `archived`       |

**Request**

```json
{
  "status": "archived"
}
```

**Response `200 OK`**

```json
{
  "data": {
    "id": 1,
    "name": "Website redesign",
    "description": "Q3 marketing site",
    "status": "archived",
    "created_at": "2026-06-08 11:30:00",
    "updated_at": "2026-06-08 11:35:00"
  }
}
```

---

### 11. Delete Project

`DELETE /api/project/{project}`

Allowed only for the project's owner.

**Response `200 OK`**

```json
{
  "message": "Project deleted"
}
```

---

## Quick Test with cURL

```bash
# Register
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"secret123","password_confirmation":"secret123"}'

# Login
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"secret123"}'

# Create project (replace TOKEN)
curl -X POST http://127.0.0.1:8000/api/project \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"name":"Website redesign","description":"Q3 marketing site"}'

# List projects
curl http://127.0.0.1:8000/api/project \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"

# Update project
curl -X PATCH http://127.0.0.1:8000/api/project/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"status":"archived"}'

# Delete project
curl -X DELETE http://127.0.0.1:8000/api/project/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"

# Logout
curl -X POST http://127.0.0.1:8000/api/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

---

## Project Structure

```
app/
├── Contracts/
│   ├── Repositories/
│   │   ├── UserRepositoryInterface.php
│   │   └── ProjectRepositoryInterface.php
│   └── Services/
│       ├── AuthServiceInterface.php
│       ├── UserServiceInterface.php
│       └── ProjectServiceInterface.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── Auth/AuthController.php
│   │   ├── UsersController.php
│   │   └── ProjectController.php
│   ├── Requests/
│   │   ├── Auth/{RegisterRequest,LoginRequest}.php
│   │   ├── Users/UpdateRequest.php
│   │   └── Projects/{CreateRequest,UpdateRequest}.php
│   └── Resources/{UserResource,ProjectResource}.php
├── Models/{User,Project}.php
├── Policies/{UserPolicy,ProjectPolicy}.php
├── Repositories/{UserRepository,ProjectRepository}.php
├── Services/{AuthService,UserService,ProjectService}.php
└── Providers/{AppServiceProvider,RepositoryServiceProvider,ServiceServiceProvider}.php
routes/
└── api.php
```

- **Controllers** handle HTTP only and delegate to services.
- **Services** hold business logic (`AuthService`, `UserService`, `ProjectService`).
- **Repositories** encapsulate all Eloquent/database access (`UserRepository`, `ProjectRepository`).
- **Policies** authorize per-record ownership (`UserPolicy`, `ProjectPolicy`).
- **Service Providers** bind interfaces to implementations for dependency injection
  (`RepositoryServiceProvider`, `ServiceServiceProvider`).
