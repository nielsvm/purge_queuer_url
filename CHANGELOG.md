# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added `CHANGELOG.md` to start tracking changes.
- Added `.gitattributes` to keep certain assets out of built packages.
- Added `composer.json` for integration with Composer-based workflows.

### Changed
- **Improvement:** fixes to pass all these these standards checks:
    - `phpcs --standard=Drupal --extensions=php,module,inc,install,..`
    - `phpcs --standard=AcquiaDrupalStrict`
