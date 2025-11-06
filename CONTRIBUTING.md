# Contributing — Atlas Laravel

Follow this workflow to maintain consistency and quality across the repository.

---

## 1. Review Standards

All coding, testing, and documentation rules are defined in [AGENTS.md](./AGENTS.md).  
Read that file before starting any work—its rules take precedence over all others.

---

## 2. Format and Lint

Run **Laravel Pint** to apply PSR-12 formatting before committing:

```bash
./vendor/bin/pint
```

Ensure no formatting issues remain after running Pint.

---

## 3. Run Tests

Run the full test suite with Composer:

```bash
composer test
```

- All tests must pass before committing.
- Add or update tests for any new features or behavior changes.
- Keep tests deterministic, small, and placed under `/tests/Feature` or `/tests/Unit`.
- Do not skip or silence failing tests.

---

## 4. Documentation

If your change affects functionality, update the relevant Markdown files under `/docs/`.  
Include concise examples or usage notes where appropriate.

---

## 5. Commit Cleanly

Keep commits small, self-contained, and descriptive.  
Do not include debug code, commented-out sections, or incomplete work.

---

For full architectural and workflow rules, see **[AGENTS.md](./AGENTS.md)**.
