# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [1.9.0] - 2020-03-03
### Added
- Support to Laravel 7 ([#225](https://github.com/algolia/scout-extended/pull/225))

### Changed
- Drops PHP `7.1` and Laravel `5.x` series ([#225](https://github.com/algolia/scout-extended/pull/225))

## [1.8.0] - 2019-10-14
### Added
- Support to `_snippetResult` in searchable metadata ([#207](https://github.com/algolia/scout-extended/pull/207))

## [1.7.0] - 2019-08-28
### Added
- Support to `laravel/framework:^6.0` ([#198](https://github.com/algolia/scout-extended/pull/198))

### Fixed
- Empty searchable array got indexed when soft delete meta data is enabled ([#193](https://github.com/algolia/scout-extended/pull/193))

## [1.6.0] - 2019-03-07
### Added
- Allow eager loading relationships on aggregated models ([#131](https://github.com/algolia/scout-extended/pull/131))

## [1.5.0] - 2019-02-27
### Added
- Support to Lumen ([#156](https://github.com/algolia/scout-extended/pull/156))

### Fixed
- No longer sets scout metadata on non searchable models ([bb1cefb](https://github.com/algolia/scout-extended/commit/bb1cefb2397c27d141a70e0cf7177a8ac24145e9))

## [1.4.0] - 2019-02-21
### Added
- Algolia `_highlightResult` and `_rankingInfo` to Scout Metadata ([#147](https://github.com/algolia/scout-extended/pull/147))
- Support to Laravel 5.8 ([#141](https://github.com/algolia/scout-extended/pull/141))

## [1.3.1] - 2019-02-12
### Fixed
- Issue while making unsearchable multiple models ([#143](https://github.com/algolia/scout-extended/pull/143))

## [1.3.0] - 2019-02-11
### Added
- Support to Laravel Scout 7.0 ([#137](https://github.com/algolia/scout-extended/pull/137))

## [1.2.0] - 2019-02-07
### Added
- Added configurable settings path ([#120](https://github.com/algolia/scout-extended/pull/120))

## [1.1.1] - 2019-01-22
### Added
- Generated settings file docs example ([1622cb0](https://github.com/algolia/scout-extended/commit/1622cb0399269d0b787194dcb8ac2e77f6005cf6))

## [1.1.0] - 2019-01-10
### Added
- Method `whereIn` on the query builder ([#115](https://github.com/algolia/scout-extended/pull/115))

## [1.0.5] - 2019-01-09
### Fixed
- Warns the user to `scout:reimport` if objectID invalid ([3048d74](https://github.com/algolia/scout-extended/commit/3048d74302c4e8e5bf8a02310a72d939b2c2a15b))

## [1.0.4] - 2019-01-04
### Fixed
- Exception when importing with no searchables with `scout:import` ([#109](https://github.com/algolia/scout-extended/pull/109))

## [1.0.3] - 2019-01-04
### Fixed
- Creation of search key using `Algolia::searchKey` ([ba0afdf](https://github.com/algolia/scout-extended/commit/ba0afdf7eeabf6b26cb1117c7387b27bc6e7bed9))

## [1.0.2] - 2019-01-03
### Fixed
- `scout:reimport` with indexes imported using `laravel/scout` ([9aa9370](https://github.com/algolia/scout-extended/commit/9aa937089343c05460252b9a438c670b7beebabb))

## [1.0.1] - 2019-01-02
### Fixed
- User agent version ([21eb42f](https://github.com/algolia/scout-extended/commit/21eb42f8a1223211d93de750ce75337ee914ffd2))

## [1.0.0] - 2018-12-20
### Added
- First stable release

## [0.4.3] - 2018-12-19
### Fixed
- Using `null` on `Model::search` method ([46c9405](https://github.com/algolia/scout-extended/commit/46c9405f3f9c202e5f15551cadad731ed059eb94))

## [0.4.2] - 2018-12-19
### Fixed
- Missing Eager loading in `search` method ([d98dcce](https://github.com/algolia/scout-extended/commit/d98dccee7032dbb5d9a3a101a65913f03da6904d))

## [0.4.1] - 2018-12-09
### Fixed
- `AlgoliaEngine::map()` returns `searchable`'s collection ([09ae017](https://github.com/algolia/scout-extended/commit/09ae017b050941ffef2e3e71ef86910f5b3fbc3e))

## [0.4.0] - 2018-11-29
### Added
- `Builder::whereBetween` method to Builder ([4161a60](https://github.com/algolia/scout-extended/commit/4161a6048e6f6e2f54c2dc4e6f0af7bc108c1436))

### Changed
- Custom splitters must implement `Algolia\ScoutExtended\Contracts\SplitterContract`.

### Fixed
- Queuing aggregators ([#77](https://github.com/algolia/scout-extended/pull/77))

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

[Unreleased]: https://github.com/algolia/scout-extended/compare/v1.9.0...HEAD
