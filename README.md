## About Laravel Scout Extended

Laravel Scout Extended was created by, and is maintained by [Algolia](https://github.com/algolia), and extends [Laravel Scout](https://github.com/laravel/scout)'s Algolia driver adding **Algolia-specific features**.

- This package is **still in development**. 

## Installation & Usage

This package is **still in development**. It's not ready for use.

## Features

- [x] For [Laravel Scout](https://github.com/laravel/scout)
- [x] Based on [Algolia's PHP Client v2](https://github.com/algolia/algoliasearch-client-php/tree/2.0)
- [x] Contains **macros** from [github.com/algolia/laravel-scout-algolia-macros](https://github.com/algolia/laravel-scout-algolia-macros)
- [x] **Facade** to provide a "static" interface to access clients, analytics
- [ ] Consider improve Laravel Scout's `scout:clear` command
- [ ] Introduce **settings management**: Backups and easy synchronization
- [ ] Manager - **Multiple connections** per project
- [ ] **Extends Driver's Query Builder** adding more methods: whereIn, whereNotIn, whereNot, whereBetween, and others cases to be studied
- [ ] Consider providing easy access to Algolia's **Places** features
- [ ] Improve usage/access to **Rules, Synonyms**. Using side configuration, or on the `model::class` itself.
- [ ] Ability to create **Custom Ranking**. Using side configuration, or on the `model::class` itself.
- [ ] **Smart index** format settings - detect metadata from the model::class to build default settings for `searchableAttributes`, `filteringAttributes`, `dateFields`, and others to be studied
- [ ] Aggregators - **Multiple models on the same index**, using the same approach already used on the Symfony bundle
- [ ] Support to **multiple language indexes**. Implementation to be studied.
- [ ] Advanced **Distinct** - Easy way leverage this feature
- [ ] Set a default **UserAgent**

---
- [ ] Nova tool
- [ ] Provide front-end resources

## License

Laravel Scout Extended is an open-sourced software licensed under the [MIT license](LICENSE.md).
