# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.3.2] - 2018-11-21
### Changed
- `Builder::where` now accepts 3 arguments like Eloquent ([d883ce1](https://github.com/algolia/scout-extended/commit/d883ce199f5c5fcf592af96c89e27a08b811d362))

## [0.3.1] - 2018-11-19
### Changed
- Wheres gets mutated before sending it to Algolia ([8075476](https://github.com/algolia/scout-extended/commit/80754769539c7ce369aa193d8b0daaa0d99d1b58))

## [0.3.0] - 2018-11-19
### Added
- Support to `<`, `<=`, `=`, `!=`, `>=`, `>` operators in `Builder::where` method ([#69](https://github.com/algolia/scout-extended/pull/69))

### Changed
- Object gets mutated before sending it to Algolia ([#68](https://github.com/algolia/scout-extended/pull/68))

## [0.2.0] - 2018-11-16
### Added
- `Algolia::searchKey` method ([1bbffa9](https://github.com/algolia/scout-extended/commit/1bbffa9ce63e0151d0e1805572a729f706f6573c))

### Fixed
- No verification of `shouldBeSearchable` while calling `makeAllSearchable` in aggregators ([62](https://github.com/algolia/scout-extended/pull/62/files))
- No usage of `scout.chunk.searchable` while calling `makeAllSearchable` in aggregators ([62](https://github.com/algolia/scout-extended/pull/62/files))
- No usage of `ModelsImported` event while calling `makeAllSearchable` in aggregators ([29c56a6](https://github.com/algolia/scout-extended/commit/29c56a6a014e53a861c780f987e48f3a9d033b01))

### Removed
- `@scout` directive ([1bbffa9](https://github.com/algolia/scout-extended/commit/1bbffa9ce63e0151d0e1805572a729f706f6573c))

## [0.1.1] - 2018-11-05
### Fixed
- Updated order of settings in `config.blade.php` view ([841c002](https://github.com/algolia/scout-extended/commit/841c002b940f7d558cea1ceacd6d797e6e75e786))

## 0.1.0 - 2018-11-02
### Added
- Initial release

[Unreleased]: https://github.com/algolia/scout-extended/compare/v0.3.1...HEAD
[0.1.1]: https://github.com/algolia/scout-extended/compare/v0.1.0...v0.1.1
[0.1.2]: https://github.com/algolia/scout-extended/compare/v0.1.1...v0.1.2
[0.2.0]: https://github.com/algolia/scout-extended/compare/v0.1.2...v0.2.0
[0.3.0]: https://github.com/algolia/scout-extended/compare/v0.2.0...v0.3.0
[0.3.1]: https://github.com/algolia/scout-extended/compare/v0.3.0...v0.3.1
