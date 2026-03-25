# Contributing

## Commit messages — Conventional Commits

This project enforces [Conventional Commits](https://www.conventionalcommits.org/) via GrumPHP.
A non-conforming commit message is **blocked** before it reaches the repo.

### Format

```
<type>(<scope>): <description>
```

- Max 72 characters on the first line
- Sentence case, no trailing period
- `scope` is optional but recommended

### Types

| Type | When to use |
|---|---|
| `feat` | A new feature |
| `fix` | A bug fix |
| `refactor` | Code change with no feature or fix |
| `test` | Adding or updating tests |
| `docs` | Documentation only |
| `chore` | Build, config, tooling |
| `ci` | CI/CD workflow changes |
| `perf` | Performance improvement |
| `build` | Dependency changes |

### Examples

```bash
# Good
git commit -m "feat(ticket): add AI-powered priority suggestion"
git commit -m "fix(zendesk): handle missing ticket.id in API response"
git commit -m "refactor: extract ContactName value object"
git commit -m "test(handler): add reservation number custom field assertion"
git commit -m "chore(ci): add PHPStan to GitHub Actions matrix"
git commit -m "docs: update AI provider configuration table"

# Blocked by GrumPHP
git commit -m "fix this shit 345"   # no type, no scope
git commit -m "WIP"                 # no type, too short
git commit -m "Update"              # no type
```

---

## Setup

```bash
composer install
# GrumPHP hooks are installed automatically by composer post-install scripts
```

If hooks are not installed:
```bash
./vendor/bin/grumphp git:init
```

---

## Running checks manually

```bash
# All GrumPHP tasks (same as pre-commit)
./vendor/bin/grumphp run

# Individual tools
./vendor/bin/phpunit --testdox --testsuite Unit
./vendor/bin/phpstan analyse
./vendor/bin/php-cs-fixer fix --dry-run --diff
```
