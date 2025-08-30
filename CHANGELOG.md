# Changelog

All notable changes to this project will be documented in this file.

---

## [v1.1.0] - 2025-08-29

### Added

- 🔢 Introduced **`scoped` sequence support**
  - Sequences can now be scoped with a custom separator (default: `-`)
  - Example: `ORD-0001`, or `INV/0001` when using `/` as separator
- 🛠 Added `getSequenceScoped()` and `getSequenceSeparator()` helpers in `HasSequence` trait
- ⚡ Configurable via model properties (`$sequence_scoped`, `$sequence_separator`) or `config/sluguid.php`

### Improved

- ♻️ Refactored `HasSequence` trait to support new sequence generation flow
- ✨ Cleaner integration between Trait ↔ Service for sequence building

---

## [v1.0.1] - 2025-08-29

### Added

- ✅ Added support for **Laravel 12.x**
- ✅ Updated `composer.json` to require `illuminate/support` `^9.0|^10.0|^11.0|^12.0`

### Fixed

- 🛠 Cleaned duplicate `^11.0` constraint in `composer.json`

---

## [v1.0.0] - 2025-08-28

### Initial Release 🎉

- 🚀 Automatic **slug generation** for Eloquent models
- 🆔 Built-in **UUID assignment** for models
- 🔢 Sequence handling for duplicate slugs
- 🧩 Easy integration via `HasSlugUid` trait
