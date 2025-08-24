# Agent Instructions

Atlas is a collection of reusable Laravel packages 
that consumers can drop into their own applications. This file summarizes the
coding conventions and expectations for contributors.

## Coding guidelines

- Follow the architecture and rules in the [docs](docs) directory.
- Use PHP 8+ features with strict types and typed properties.
- Follow PSR-12 formatting (enforced via Laravel Pint).
- Keep controllers thin; business logic belongs in service classes.
- Write tests for new features and run `composer test` before committing.

## Testing
- Run package tests with `composer test`

## Pull requests
- Keep commits focused and reference relevant documentation when introducing new patterns.
- Update this file and other docs as conventions evolve.

