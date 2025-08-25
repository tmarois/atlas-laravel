# Agent Instructions

Atlas Laravel is a collection of reusable Laravel functionality  
that consumers can drop into their own applications. This file summarizes the
coding conventions and expectations for contributors of this repository.

## Coding guidelines

- Use PHP 8+ features with strict types and typed properties.
- Follow PSR-12 formatting (enforced via Laravel Pint).
- Write tests for new features and run `composer test` before committing.
- Format code with `./vendor/bin/pint` before committing.

## Documentation
- Group feature docs by their domain under `/docs/features/`.
- For feature improvements, update the domain-specific doc only; update `README.md` only when introducing a feature not already listed.
- New or changed features must update relevant Markdown files in `docs/`, including examples when possible.
- Any new features or behavior changes must be reflected in the docs.
- Documentation should start with a brief introduction, describe what the feature is and the problem it solves, then show how to use it via API descriptions and example usage.
