## ⚙️ About Scout Extended

Scout Extended was created by, and is maintained by [Algolia](https://github.com/algolia), and extends [Laravel Scout](https://github.com/laravel/scout)'s Algolia driver adding **Algolia-specific features**.

## ⬇️ Installation

This package is **still in development**. It's not ready for use.

> **Requires:**
- **[PHP 7.1.3+](https://php.net/releases/)**
- **[Laravel 5.6+](https://github.com/laravel/laravel)**

First, install Scout via the [Composer](https://getcomposer.org) package manager:

```bash
composer require algolia/scout-extended
```


After installing Scout Extended, you should publish the Scout configuration using the `vendor:publish` Artisan command. This command will publish the `scout.php configuration file to your config directory:

```bash
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```

## 🔎 Optimize the search experience

Performance is important. However, in order for a search to be successful,
results need to be relevant to the user. Scout Extended provides an optimize
`Artisan` command that you may use to optimize the search experience based on information from the searchable class:
```bash
php artisan scout:optimize
```

With Scout Extended, `Artisan` automatically detects the `searchable` classes of your project. But fell free
to specify the `searchable` class to optimize:
```bash
php artisan scout:optimize "App\Thread"
```

After running the optimize command, you may need to edit the created
settings in `config/scout-threads.php`.

Once you have verified the settings file, all you need to do is synchronize
the settings with Algolia using the `Artisan` command sync:

 ```bash
 php artisan scout:sync
 ```

> **Note:** You may also edit the settings of your index using the Algolia Dashboard.
Make sure you apply those remote settings locally running the sync command.

## 🚀 Zero Downtime deployment

In order to keep your existing service running while re-importing your data, we recommend the usage of the reimport `Artisan` command.
 ```bash
 php artisan scout:reimport
 ```

 To ensure that searches performed on the index during the rebuild will not be interrupted.
 Scout Extended creates a temporary index with all your records before moving the temporary index to the target index

 > **Note:** TODO about the plan.

## ✅ Status

If you are not sure about the current status of your indexes, you can always run
the status `Artisan` command to make sure that your records and your settings are
up-to-date:
 ```bash
 php artisan scout:status
 ```

## ⚡️ Aggregators

Scout Extended provides a clean way of implement site-wide search amongst multiple models.

### Generating Aggregators

To create a new aggregator, use the Make Aggregator `Artisan` command. This command will create a new aggregator class in the `app/Search` directory. Don't worry if this directory does not exist in your application, since it will be created the first time you run the command.

 ```bash
php artisan make:aggregator News
 ```
 
 ### Aggregator Structure
 
After generating your aggregator, you should fill in the models property of the class, which will be used to identify the models that should be aggregated:
```php
<?php

namespace App\Search;

use Algolia\ScoutExtended\Searchable\Aggregator;

class News extends Aggregator
{
    /**
     * The names of the models that should be aggregated.
     *
     * @var string[]
     */
    protected $models = [
    	 \App\Event::class,
    	 \App\Article::class,
    ];
}
```
## ✂️ Split Records & Distinct

For performance reasons, objects in Algolia should be 10kb or less. Large records can be split into smaller documents by splitting on a logical chunk such as paragraphs or sentences.

### Split using the value

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
	 use Searchable;

    /**
     * Splits the given value.
     *
     * @param  string $value
     * @return string[]
     */
    public function splitBody($value)
    {
        return explode('. ', $value);
    }
}
```

### Split using a splitter

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Algolia\ScoutExtended\Splitters\HtmlSplitter;

class Thread extends Model
{
	 use Searchable;

    /**
     * Splits the given value.
     *
     * @param  string $value
     * @return string[]
     */
    public function splitBody($value)
    {
        return HtmlSplitter::by('p');
    }
}
```

### Writing Splitters

Writing a splitter is simple. Create a new `Invokable` class, and the `__invoke` method should split the given `$value` as needed:

```php
<?php

namespace App\Splitters;

class CustomSplitter
{
    /**
     * Splits the given value.
     *
     * @param  object $searchable
     * @param  string $value
     *
     * @return array
     */
    public function __invoke($searchable, $value)
    {
    	 $values = [$value];

        return $values;
    }
}

```



## Features

- [x] For [Laravel Scout](https://github.com/laravel/scout)
- [x] Based on [Algolia's PHP Client v2](https://github.com/algolia/algoliasearch-client-php/tree/2.0) ([47e84e3](https://github.com/nunomaduro/scout/commit/47e84e3c62121a588930b7e04901f7e6a378abb2))
- [x] Contains **macros** from [github.com/algolia/laravel-scout-algolia-macros](https://github.com/algolia/laravel-scout-algolia-macros)
- [x] **Facade** to provide a "static" interface to access clients and analytics
- [x] Replace `scout:flush` command : Clear the index of the the given model
- [x] Replace `scout:import` command : Import the records of the given model
- [x] Adds `scout:reimport` command : Reimport the records of the given model using a temporary indices
- [x] Adds `scout:status` command : Gives information about the remote status
- [x] Adds `scout:optimize` command : Optimize the search experience based on information from the model class
- [x] Adds `scout:sync` command : Backups & Synchronize the given model settings
- [x] Adds `scout:aggregator` command : Create a new aggregator class
- [x] Aggregators - **Multiple models on the same index**
- [x] Ability to use **display features** (**Searchable attributes**, **Query Language**, **Custom Ranking**, etc) of using a configuration file
- [x] Automatic way of leverage **Distinct**
- [x] Update **UserAgent**
- [x] Adds `@scout` blade directive
- [ ] Manager - **Multiple client connections** per project
- [ ] **Extends Driver's Query Builder** adding more methods: whereIn, whereNotIn, whereNot, whereBetween, and others cases to be studied
- [ ] Support to **multiple language indexes**
- [ ] Ability to use **synonyms features** of using a configuration file
- [ ] Ability to use **Rules features**

## License

Scout Extended is an open-sourced software licensed under the [MIT license](LICENSE.md).
