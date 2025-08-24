# Atlas Laravel

Atlas Laravel is a backend toolkit for [Inertia.js](https://inertiajs.com) applications built with [Laravel](https://laravel.com). It removes the repetitive setup that comes with new projects so you can ship dashboards faster.

The package addresses common needs such as server-driven tables, exporting enums for frontend use, and scaffolding CRUD operations through a model service layer.

## Installation

You can install the package via Composer:

```bash
composer require tmarois/atlas-laravel
```

## Features

Atlas handles the backend foundation and Inertia bridge. It includes tooling for:

- **DataTables** – build server-driven options for dynamic tables.
- **Enums** – export PHP enums for type-safe usage in Vue.
- **Model Service** – base model service providing CRUD scaffolding.

**Documentation**

- [Inertia DataTable Options](docs/inertia-data-table-options.md)
- [Enum Exporter](docs/enum-exporter.md)
- [Model Service](docs/model-service.md)
- [Support](docs/support.md)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) and [AGENTS.md](AGENTS.md) for coding standards, conventions, and pull request guidelines.
