# Agent Instructions — Atlas Laravel

Purpose: Define contributor and automation rules for the **Atlas Laravel** package.  

This guide governs coding, testing, documentation, and contribution workflow to maintain consistent quality across the repository.

---

## Coding Standards

- Use **PHP 8.4+** with strict types and typed properties.
- Follow **PSR-12** formatting (enforced via **Laravel Pint**).
- Run `./vendor/bin/pint` before committing to auto-format code.
- Write clear, maintainable, and dependency-light code; avoid unnecessary abstractions.
- All new features or changes must include automated tests; run `composer test` before committing.
- Avoid framework hacks or direct vendor modifications.
- Keep commits free of debug code, comments, and experimental logic.

---

## Documentation Standards

- Place feature docs under `/docs/features/<domain>/`.
- Update only the relevant feature doc when improving or extending functionality.
- Documentation must include:
    - A short summary of the feature and its purpose.
    - Example usage or API reference.
    - Notes on configuration, dependencies, or edge cases.
- Any behavioral or API change **must** be documented before merge.

---

## Contributing Workflow

All contributors must follow the [CONTRIBUTING.md](./CONTRIBUTING.md) workflow before committing:

1. Review this **AGENTS.md** file in full — it defines all coding, testing, and documentation requirements.
2. Format code using **Laravel Pint**:
   ```bash
   ./vendor/bin/pint
   ```
3. Run all tests with Composer:
   ```bash
   composer test
   ```
4. Update any affected documentation in `/docs/`.
5. Commit clean, tested, and formatted code only — no debug or temporary code.

---

## Enforcement

All contributors and automation must comply with these rules and the steps in `CONTRIBUTING.md`.  
Pull requests or commits that fail coding, testing, or documentation requirements will be rejected.

---
