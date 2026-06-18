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

- **Users** — a user may only `show` or `update` **their own** account.
- **Projects** — a user may only `show`, `update`, or `delete` projects **they own**
  (`owner_id`). The project list (`GET /project`) returns only the authenticated user's projects.

Acting on a record you don't own returns `403 This action is unauthorized.`

### Emails

On registration a **welcome email** is sent automatically: `UserObserver` reacts to the
`User` `created` event and dispatches the queued `MailUserJob`, which sends `WelcomeMail`
(a Markdown mailable rendered from `resources/views/emails/welcome.blade.php`).

Because the job is queued, make sure a worker is running to actually deliver it:

```bash
php artisan queue:work
```

Mail delivery uses the configured mailer — by default `MAIL_MAILER=log`, so messages are
written to `storage/logs/laravel.log` rather than actually sent. To preview emails in a UI,
point the mailer at **Mailpit** (`MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`,
inbox at `http://localhost:8025`).

---

## API Endpoints

Base URL: `http://127.0.0.1:8000/api`

### Status codes

| Code              | Meaning                                                            |
| ----------------- | ----------------------------------------------------------------- |
| `200 OK`          | Successful read / update / action                                 |
| `201 Created`     | A new resource was created (register, create project)             |
| `401 Unauthorized`| Missing or invalid Bearer token on a protected route              |
| `403 Forbidden`   | Authenticated but acting on a record you don't own (policy)        |
| `404 Not Found`   | Route or model (route-model binding) not found                    |
| `422 Unprocessable Entity` | Validation failed — body contains `message` + `errors`   |

### Auth

| Method | Endpoint         | Auth         | Throttle    | Success | Description                        |
| ------ | ---------------- | ------------ | ----------- | ------- | ---------------------------------- |
| POST   | `/auth/register` | Public       | 6 req / min | `201`   | Register a new user, returns token |
| POST   | `/auth/login`    | Public       | 6 req / min | `200`   | Log in, returns token              |
| POST   | `/auth/logout`   | Bearer token | 6 req / min | `200`   | Revoke the current user's tokens   |

### Users

| Method      | Endpoint       | Auth         | Success | Description                |
| ----------- | -------------- | ------------ | ------- | -------------------------- |
| GET         | `/user/{user}` | Bearer token | `200`   | Show a user (owner only)   |
| PUT / PATCH | `/user/{user}` | Bearer token | `200`   | Update a user (owner only) |

### Projects

| Method      | Endpoint             | Auth         | Success | Description                            |
| ----------- | -------------------- | ------------ | ------- | -------------------------------------- |
| GET         | `/project`           | Bearer token | `200`   | List the authenticated user's projects |
| POST        | `/project`           | Bearer token | `201`   | Create a project (owned by caller)     |
| GET         | `/project/{project}` | Bearer token | `200`   | Show a project (owner only)            |
| PUT / PATCH | `/project/{project}` | Bearer token | `200`   | Update a project (owner only)          |
| DELETE      | `/project/{project}` | Bearer token | `200`   | Delete a project (owner only)          |

---

## Auth

### 1. Register

`POST /api/auth/register`

Creates the user and issues an access token in a single database transaction, then queues the
welcome email.

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

**Response `201 Created`**

> A new user resource is created, so the endpoint returns `201` (not `200`).

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

### 6. List Projects

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

### 7. Create Project

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

### 8. Show Project

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

### 9. Update Project

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

### 10. Delete Project

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

## Testing

The project uses **PHPUnit** feature tests that exercise the API end-to-end.

### Test environment

`phpunit.xml` runs tests in an isolated environment — nothing touches your real database or
sends real email:

| Setting             | Value       | Effect                                            |
| ------------------- | ----------- | ------------------------------------------------- |
| `DB_CONNECTION`     | `sqlite`    | separate test database…                           |
| `DB_DATABASE`       | `:memory:`  | …held in memory, migrated fresh per run           |
| `MAIL_MAILER`       | `array`     | emails are captured in memory, never delivered    |
| `QUEUE_CONNECTION`  | `sync`      | queued jobs (e.g. `MailUserJob`) run inline       |
| `BCRYPT_ROUNDS`     | `4`         | fast password hashing                             |

### Running tests

```bash
composer test                       # recommended — clears config cache first, then runs
php artisan test                    # run all tests
php artisan test --filter=LoginTest # a single class
php artisan test tests/Feature/Auth # a folder
php artisan test --parallel         # run in parallel
```

> ⚠️ Prefer `composer test`. A cached config (`bootstrap/cache/config.php`) overrides the
> `phpunit.xml` values above (e.g. forces the queue back to `database`), which makes
> queue/mail assertions fail. `composer test` runs `php artisan config:clear` first to avoid
> this; if you use `php artisan test` directly, clear the cache yourself.

### Factories

Test data is built with model factories:

- `UserFactory` — `User::factory()->create([...])`
- `ProjectFactory` — `Project::factory()->for($user, 'owner')->create()`

Each feature test uses the `RefreshDatabase` trait, authenticates with `Sanctum::actingAs($user)`
(or `$this->actingAs($user)`), and follows the Arrange → Act → Assert pattern.

### Current coverage

```
tests/Feature/
├── Auth/
│   ├── RegisterTest.php   # register success (201), duplicate email (422)
│   └── LoginTest.php      # login success (200), wrong credentials (422)
└── Users/
    ├── ShowUserTest.php   # owner can view (200), other user forbidden (403)
    └── UpdateTest.php     # owner can update (200), other user forbidden (403)
```

> The default `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php` are framework
> stubs and can be removed. Project endpoints (`/project`) are not covered yet — good next tests
> to add (list scoping, create `201`, owner-only update/delete).

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
├── Jobs/MailUserJob.php
├── Mail/WelcomeMail.php
├── Models/{User,Project}.php
├── Observers/UserObserver.php
├── Policies/{UserPolicy,ProjectPolicy}.php
├── Repositories/{UserRepository,ProjectRepository}.php
├── Services/{AuthService,UserService,ProjectService}.php
└── Providers/{AppServiceProvider,RepositoryServiceProvider,ServiceServiceProvider}.php
database/
├── factories/{UserFactory,ProjectFactory}.php
└── seeders/{DatabaseSeeder,UserSeeder,ProjectSeeder}.php
resources/
└── views/emails/welcome.blade.php
routes/
└── api.php
```

- **Controllers** handle HTTP only and delegate to services.
- **Services** hold business logic (`AuthService`, `UserService`, `ProjectService`);
  `AuthService::register` runs inside a DB transaction.
- **Repositories** encapsulate all Eloquent/database access (`UserRepository`, `ProjectRepository`).
- **Policies** authorize per-record ownership (`UserPolicy`, `ProjectPolicy`).
- **Mail / Observers / Jobs** — `UserObserver` reacts to the `User` `created` event and dispatches
  the queued `MailUserJob`, which sends `WelcomeMail` (`resources/views/emails/welcome.blade.php`).
- **Service Providers** bind interfaces to implementations for dependency injection
  (`RepositoryServiceProvider`, `ServiceServiceProvider`).
