# Apskel

**Apskel** ("Application Skeleton") is a lightweight PHP framework for Rapid
Application Development (RAD) of web applications. Its aim is to minimize the
work and complexity of building and maintaining web apps: you register a
module's *requests* (endpoints) and *tables*, write controllers that return
plain data arrays, and the framework handles routing, response formatting
(HTML / XML / JSON / templates), templating, and the database layer for you.

- **Author:** Matthew C. Tedder &lt;matthewct@gmail.com&gt;
- **License:** GNU LGPL (see [`LICENSE`](LICENSE))
- **Status:** Alpha. Originally built ~2011–2012 for the LAMP stack; currently
  being revived and modernized to run on **PHP 8.4** with **PostgreSQL** and
  **no Apache** (see [Modernization status](#modernization-status)).

---

## Table of contents

- [How it works](#how-it-works)
- [Requirements](#requirements)
- [Quick start (development)](#quick-start-development)
- [Project layout](#project-layout)
- [Modules](#modules)
- [Modernization status](#modernization-status)
- [Roadmap](#roadmap)

---

## How it works

Every request flows through a small, predictable pipeline:

1. **`webroot/index.php`** receives the request (from the web server or, in
   development, from PHP's built-in server via [`router.php`](router.php)).
2. **`identity.php`** maps the requesting domain to an *application version* and
   *environment* (development / staging / production), which selects the
   matching `application_{version}/` and `environments_{version}/` directories.
   This is how Apskel supports running multiple versions/environments of an app
   side by side.
3. **`application_{version}/framework.php`** parses and validates the request
   parameters against the target module's registration, then dispatches to the
   module's controller.
4. **`{module}_controllers.php`** does the work and returns a `[data, format]`
   array. The framework auto-formats `data` into the requested representation —
   `html`, `xml`, `json`, a `view`, a `template`, or plain `text`. Controllers
   can also issue internal sub-requests back through the framework.

A URL is interpreted as `/{module}/{request}/{param=value}/...`, e.g.
`/blog/view`, `/user/login`, `/wiki/SomeTopic`.

Templating is intentionally simple: `{{field}}` substitutes a response field,
`{{=include.html}}` pulls in another file, and views can compose sub-templates.

## Requirements

- **PHP 8.4** with the `pdo` and `pdo_pgsql` extensions
  (`sudo apt install php8.4-cli php8.4-pgsql`).
- **PostgreSQL 17** (or any reasonably recent PostgreSQL).
- No Apache, no Docker, and no Composer dependencies are required for
  development — the framework runs on PHP's built-in web server.

## Quick start (development)

1. **Create the development database and role** (run as the `postgres`
   superuser; adjust names to taste):

   ```sql
   CREATE ROLE apskel WITH LOGIN PASSWORD 'apskel_dev';
   CREATE DATABASE apskel_development OWNER apskel;
   GRANT ALL PRIVILEGES ON DATABASE apskel_development TO apskel;
   ```

   The development connection settings live in
   [`environments_0.0/development_settings.php`](environments_0.0).

2. **Start the development server** from the project root:

   ```bash
   php -S localhost:8000 router.php
   ```

   `router.php` reproduces exactly what Apache used to do — set the working
   directory to `webroot/` and funnel every URL into `webroot/index.php` — so
   no `.htaccess` or `mod_rewrite` is needed. (If you do want to run under
   Apache, a sample virtual host is provided in
   [`tools/apskel.dev.conf`](tools/apskel.dev.conf).)

3. **Initialize a module's tables.** For example, browse to
   `http://localhost:8000/user/initialize` to create the user tables and the
   initial super-user account.

4. Visit `http://localhost:8000/` and start clicking around.

> **Note:** A long-running `php -S` process does **not** pick up newly installed
> PHP extensions — if you install `php8.4-pgsql` while the server is running,
> stop and restart it.

## Project layout

```
apskel/
├── router.php                  # Dev server entry (PHP built-in server)
├── webroot/index.php           # Real request entry point
├── identity.php                # Domain → application/version/environment mapping
├── environments_{version}/     # Per-environment settings (db, logging, …)
├── application_{version}/
│   ├── framework.php           # Core: routing, dispatch, formatting, DB, sessions
│   └── modules/
│       ├── models.php          # Base model: safe parameterized query helpers
│       ├── controllers.php     # Base controller
│       ├── views.php           # Base view
│       └── {module}/           # Each module: registration + controllers + models + views
├── tools/
│   ├── build                   # CLI code generator (scaffolds modules)
│   └── configure               # CLI configuration tool (work in progress)
└── logs/                       # Runtime logs (git-ignored)
```

A **module** is a self-contained feature consisting of:

- `{module}_registration.php` — declares the module's requests (with parameter
  specs) and its database tables.
- `{module}_controllers.php` — request handlers.
- `{module}_models.php` — data access.
- `views/*.html` — templates.

## Modules

| Module      | Purpose                                                        | State |
|-------------|----------------------------------------------------------------|-------|
| **user**    | Registration, login/logout, profile editing, super-user        | Working (register/login/logout/edit); recover/change/activate are stubs |
| **blog**    | Multi-author blog with settings and articles                   | Settings + article upsert working; comments/moderation pending |
| **wiki**    | Topic-based wiki pages                                          | Scaffold / early |
| **agent**   | ELIZA-style rule-based conversational engine (meanings, reactions, conditions, actions, long-term memory, topics) | Most mature feature, but still on the legacy query layer |
| **developer** | Web UI to scaffold new modules                               | Incomplete (currently an accidental copy of the agent module) |

## Modernization status

The codebase is being brought from its original 2012 LAMP/MySQL form to a
modern, lighter stack. Work happens on the `modernize-php8` branch in phases:

- **Phase 0** — Boots on PHP 8.4 without Apache (removed `get_magic_quotes_gpc()`,
  `list()`/`count()` fixes, dynamic-property handling, defensive logging,
  `router.php` for the built-in server).
- **Phase 1a** — Driver-agnostic PDO connection layer targeting PostgreSQL
  (`ERRMODE_EXCEPTION`, `FETCH_ASSOC`, prepared-statement support).
- **Phase 1b / 2a** — Prepared statements throughout, **bcrypt** password hashing
  (`password_hash`/`password_verify`, replacing salted MD5), and PostgreSQL DDL
  generation. Secure user authentication verified end-to-end over HTTP.
- **Phase 1c** — Safe parameterized query helpers in the base model
  (`buildSelect` / `insertRecord` / `updateRecords` / `updateElseInsert`); blog
  module migrated; fixed short-open-tag (`<?`) breakage in generated code.
- **Phase 1d** — Removed `eval()` from the agent's condition evaluator (it
  executed attacker-controllable text as PHP). Replaced with an `eval`-free
  recursive-descent boolean evaluator that only ever calls whitelisted
  predicate functions.
- **Phase 1e** — **CSRF protection and session hardening**: per-session token
  auto-injected into every POST form and required back on state-changing
  requests; `HttpOnly` / `SameSite=Lax` / `Secure`-under-HTTPS session cookie;
  session-id rotation on login/logout.

The security pass (Phases 1a–1e) is complete. Remaining work is technical debt
and feature completion rather than security holes.

## Roadmap

- Migrate the **agent** module off the legacy string-based query builders onto
  the parameterized model API (or re-architect it — an LLM-backed engine is a
  candidate now that rule-based conversation has been superseded).
- Finish the base model keystone (`getRecords` / `setRecords` / `synchronize`).
- Complete the **developer** module and the `tools/configure` CLI so modules can
  be scaffolded end to end.
- Round out **user** (recover / change password / activate / deactivate),
  **blog** (comments + moderation), and **wiki**.
- Modernize the front end (the bundled jQuery is very old) and add an automated
  test suite / CI.
