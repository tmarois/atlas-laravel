# Atlas Laravel

Atlas Laravel is a backend toolkit for [Inertia.js](https://inertiajs.com) applications built with [Laravel](https://laravel.com). It removes the repetitive setup that comes with new projects so you can ship faster.

The package addresses common needs such as server-driven tables, exporting enums for frontend use, and scaffolding CRUD operations through a model service layer.

## Installation

You can install the package via Composer:

```bash
composer require tmarois/atlas-laravel
```

## Features

Atlas Laravel handles the backend foundation and Inertia bridge. It includes tooling for:

- [**DataTables**](docs/inertia-data-table-options.md) – build server-driven options for dynamic tables.
- [**Enums**](docs/enum-exporter.md) – export PHP enums for type-safe usage in Vue.
- [**Model Service**](docs/model-service.md) – base model service providing CRUD scaffolding.
- [**Support Helpers**](docs/support.md) – lightweight utility classes.
 
## Atlas UI

[Atlas UI](https://github.com/tmarois/atlas-ui) – reusable utilities and PrimeVue components that are complementary to this setup.

## Laravel Template

[Laravel Template](https://github.com/timothymarois/template-laravel-app) – example usage and boilerplate scaffolding is set up in this template.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) and [AGENTS.md](AGENTS.md) for coding standards, conventions, and pull request guidelines.
