# FuelPHP Scaffold Architecture

## Purpose

This repository is the FuelPHP application scaffold. It provides the directory skeleton, entry point, and package management for a FuelPHP 1.x application. The framework core lives in `fuel/core/` (also available as the standalone `fuelphp/core` package).

## Directory Structure

```
fuel/                               # Application root (this repo)
├── fuel/
│   ├── app/                        # Application code
│   │   ├── classes/
│   │   │   ├── controller/         # Application controllers (extend Controller)
│   │   │   └── model/              # Application models (extend \Orm\Model or Model)
│   │   ├── config/                 # App-level config overrides
│   │   ├── migrations/             # Database migration files
│   │   ├── views/                  # View templates
│   │   └── bootstrap.php           # App bootstrap
│   ├── core/                       # FuelPHP core (git submodule / separate repo)
│   ├── packages/                   # Optional packages (auth, email, orm, parser…)
│   │   ├── auth/                   # Authentication package
│   │   ├── email/                  # Email sending package
│   │   └── parser/                 # Template parser package (Twig, Mustache…)
│   └── vendor/                     # Composer-managed dependencies
│       ├── fuelphp/upload/         # File upload library
│       ├── michelf/php-markdown/   # Markdown parser
│       └── monolog/monolog/        # PSR-3 logger
├── public/                         # Web root (index.php entry point)
├── CHANGELOG.md
├── README.md
└── TESTING.md
```

## Key Design Decisions

- **Static facades over instances**: FuelPHP uses static class facades (`Input::get()`, `Response::forge()`, `DB::query()`) backed by instances. The `forge()` pattern creates instances; `instance()` returns the current active one.
- **HMVC routing**: `Request::forge()` supports sub-requests (Hierarchical MVC). Routes are compiled from patterns like `:segment`, `:num`, `:any`.
- **Package system**: Packages extend the framework with optional features. They are loaded via `Package::load()` and may override core classes.
- **Config cascade**: Config files load from `core/config/` first, then `app/config/` overrides, then environment-specific config.
- **Module system**: Application modules are self-contained sub-applications under `fuel/app/modules/`, each with their own controllers, models, and views.

## Extension Points

- Add a controller: extend `Controller` (or `Controller_Template`) in `fuel/app/classes/controller/`.
- Add a package: place under `fuel/packages/` and call `Package::load('name')` in bootstrap.
- Add a route: define in `fuel/app/config/routes.php`.

## Dependency Flow

```
public/index.php
  → Fuel::init() / bootstrap.php
  → Router → Request::forge()
  → Controller::action_*()
  → Model / DB queries
  → View / Response::forge()
  → Response::send()
```
