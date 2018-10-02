## About Scout Extended

Scout Extended was created by, and is maintained by [Algolia](https://github.com/algolia), and extends [Laravel Scout](https://github.com/laravel/scout)'s Algolia driver adding **Algolia-specific features**.

## Installation & Usage

This package is **still in development**. It's not ready for use.

## Features

- [x] For [Laravel Scout](https://github.com/laravel/scout)
- [x] Based on [Algolia's PHP Client v2](https://github.com/algolia/algoliasearch-client-php/tree/2.0)
- [x] Contains **macros** from [github.com/algolia/laravel-scout-algolia-macros](https://github.com/algolia/laravel-scout-algolia-macros)
- [x] **Facade** to provide a "static" interface to access clients, analytics
- [x] Replace `scout:flush` command : Clear the index of the the given model
- [x] Replace `scout:import` command : Import the records of the given model
- [x] Adds `scout:optimize` command : optimize the search experience based on information from the model class
- [x] Adds `scout:sync` command : Backups & Synchronize the given model settings
- [x] Aggregators - **Multiple models on the same index**
- [x] Replace `scout:aggregator` command : Create a new aggregator class
- [x] Ability to create **Custom Ranking**.
- [ ] Improve usage/access to **Rules, Synonyms**. Using side configuration, or on the `model::class` itself.
- [ ] Manager - **Multiple connections** per project
- [ ] **Extends Driver's Query Builder** adding more methods: whereIn, whereNotIn, whereNot, whereBetween, and others cases to be studied
- [ ] Consider providing easy access to Algolia's **Places** features
- [ ] Support to **multiple language indexes**. Implementation to be studied.
- [ ] Advanced **Distinct** - Easy way leverage this feature
- [ ] Set a default **UserAgent**

---
- [ ] Nova tool
- [ ] Provide front-end resources

## License

Scout Extended is an open-sourced software licensed under the [MIT license](LICENSE.md).
