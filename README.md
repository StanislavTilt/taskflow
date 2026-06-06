# TaskFlow API

A Laravel 13 REST API with token-based authentication powered by **Laravel Sanctum**.
The codebase follows a layered architecture: **Controller → Service → Repository**, with
contracts (interfaces) bound through dedicated service providers.

## Tech Stack

| Component      | Version            |
| -------------- | ------------------ |
| PHP            | ^8.3               |
| Laravel        | ^13.8              |
| Laravel Sanctum| ^4.0               |
| Database       | SQLite (default)   |
| Testing        | PHPUnit ^12        |

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

---

## API Endpoints

Base URL: `http://127.0.0.1:8000/api`

| Method | Endpoint         | Auth          | Throttle      | Description                        |
| ------ | ---------------- | ------------- | ------------- | ---------------------------------- |
| POST   | `/auth/register` | Public        | 6 req / min   | Register a new user, returns token |
| POST   | `/auth/login`    | Public        | 6 req / min   | Log in, returns token              |
| POST   | `/auth/logout`   | Bearer token  | 6 req / min   | Revoke the current user's tokens   |
| GET    | `/user`          | Bearer token  | —             | Get the authenticated user         |

---

### 1. Register

`POST /api/auth/register`

**Body parameters**

| Field                   | Type   | Rules                                      |
| ----------------------- | ------ | ------------------------------------------ |
| `name`                  | string | required                                   |
| `email`                 | string | required, valid email, unique in `users`   |
| `password`              | string | required, min 6, must match confirmation   |
| `password_confirmation` | string | required, must equal `password`            |

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
  "message": "Неверный email или пароль.",
  "errors": {
    "email": ["Неверный email или пароль."]
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

### 4. Current User

`GET /api/user`

**Headers**

```
Authorization: Bearer <token>
```

**Response `200 OK`**

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": null,
  "created_at": "2026-06-06T18:40:00.000000Z",
  "updated_at": "2026-06-06T18:40:00.000000Z"
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

# Logout (replace TOKEN)
curl -X POST http://127.0.0.1:8000/api/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

---

## Project Structure

```
app/
├── Contracts/
│   ├── Repositories/AuthRepositoryInterface.php
│   └── Services/AuthServiceInterface.php
├── Http/
│   ├── Controllers/Api/Auth/AuthController.php
│   ├── Requests/Auth/{RegisterRequest,LoginRequest}.php
│   └── Resources/UserResource.php
├── Repositories/AuthRepository.php
├── Services/AuthService.php
└── Providers/{RepositoryServiceProvider,ServiceServiceProvider}.php
routes/
└── api.php
```

- **Controllers** handle HTTP only and delegate to services.
- **Services** hold business logic (`AuthService`).
- **Repositories** encapsulate all Eloquent/database access (`AuthRepository`).
- **Service Providers** bind interfaces to implementations for dependency injection.
