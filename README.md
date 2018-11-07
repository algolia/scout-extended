<p align="center">
  <a href="https://www.algolia.com">
    <img alt="Scout Extended" src="https://www.algolia.com/static_assets/images/press/downloads/algolia-logo-light.png" width="250">
  </a>

  <p align="center">
    Scout Extended extends <a href="https://github.com/laravel/scout">Laravel Scout</a> adding algolia-specific features.
  </p>

  <p align="center">
    <a href="https://travis-ci.org/algolia/scout-extended"><img src="https://img.shields.io/travis/algolia/scout-extended/master.svg" alt="Build Status"></img></a>
    <a href="https://scrutinizer-ci.com/g/algolia/scout-extended"><img src="https://img.shields.io/scrutinizer/g/algolia/scout-extended.svg" alt="Quality Score"></img></a>
    <a href="https://scrutinizer-ci.com/g/algolia/scout-extended"><img src="https://scrutinizer-ci.com/g/algolia/scout-extended/badges/coverage.png?b=master" alt="Coverage"></img></a>
    <a href="https://packagist.org/packages/algolia/scout-extended"><img src="https://poser.pugx.org/algolia/scout-extended/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/algolia/scout-extended"><img src="https://poser.pugx.org/algolia/scout-extended/v/stable.svg" alt="Latest Version"></a>
    <a href="https://packagist.org/packages/algolia/scout-extended"><img src="https://poser.pugx.org/algolia/scout-extended/license.svg" alt="License"></a>
  </p>
</p>

## ✨ Features

- Automatically [improves the search experience](#-optimize-the-search-experience).
- Reindex your data in production with [zero downtime](#-zero-downtime-deployments).
- Gives you a quick and simple [status overview](#-status) of your indexes.
- Implement site-wide search amongst multiple models with [aggregators](#%EF%B8%8F-aggregators).
- A useful collection of [macros](#-builder-macros), [facades](#%EF%B8%8F-algolia-facade), front-end [directives](#-scout-directive---scout--vue-instantsearch), and much more.

> **Note:** This package adds functionalities to [Laravel Scout](https://github.com/laravel/scout), and for this reason, we encourage you to read the Scout documentation first. Documentation for Scout can be found on the [Laravel website](https://github.com/laravel/scout).

## 💕 Community Plan

Because everyone should be able to build great search, you can use Algolia's basic [Community Plan](https://www.algolia.com/users/sign_up/hacker). It's free up to a certain number of records and writing operations. Search operations are not part of any quota and will not be charged in any way.

## ⬇️ Installation

> **Requires:**
- **[PHP 7.1.3+](https://php.net/releases/)**
- **[Laravel 5.6+](https://github.com/laravel/laravel)**

First, install Scout Extended via the [Composer](https://getcomposer.org) package manager:

```bash
composer require algolia/scout-extended
```

After installing Scout Extended, you should publish the Scout configuration using the `vendor:publish` Artisan command. This command will publish the `config/scout.php` configuration file to your config directory:

```bash
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```

Then you should configure your Algolia `id` and `secret` credentials in your `config/scout.php` configuration file.

Finally, add the `Laravel\Scout\Searchable` trait to the model you would like to make searchable. This trait will register a model observer to keep the model in sync with algolia:

```php
<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Searchable;
}
```

## 🔎 Optimize the search experience

Performance is important. However, in order for a search to be successful, results need to be relevant to the user. Scout Extended provides an `scout:optimize` Artisan command that you may use to optimize the search experience with Algolia settings.

```bash
php artisan scout:optimize
```

Scout Extended automatically detects `searchable` classes. But feel free to specify the `searchable` class to optimize:

```bash
php artisan scout:optimize "App\Thread"
```

The Artisan command `scout:optimize` will do his best to generate the settings of your searchable class index, but you may need to edit those settings in `config/scout-threads.php`:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Searchable Attributes
    |--------------------------------------------------------------------------
    |
    | Limits the scope of a search to the attributes listed in this setting. Defining
    | specific attributes as searchable is critical for relevance because it gives
    | you direct control over what information the search engine should look at.
    |
    | Example: ["name", "ordered(email)", "unordered(city)",]
    |
    */

    'searchableAttributes' => ['subject', 'body', 'slug', 'author_name', 'author_email'],

    /*
    |--------------------------------------------------------------------------
    | Custom Ranking
    |--------------------------------------------------------------------------
    |
    | To return great results, custom ranking attributes are applied after records
    | sorted by textual relevance. Said another way, if two matched records have
    | the same match textually, we resort to custom ranking to tie-break.
    |
    | Examples: ['desc(comments_count)', 'desc(views_count)',]
    |
    */

    'customRanking' => ['desc(reply_count)', 'desc(likes_count)'],

    // ...
];
```

Once you have verified the settings file, all you need to do is synchronize the settings with Algolia using the `scout:sync` Artisan command:

```bash
php artisan scout:sync
```

Feel free to dig further into all settings parameters to optimize even more your search experience: [Algolia Settings](https://www.algolia.com/doc/api-reference/settings-api-parameters).

> **Note:** You may also edit settings using the [Algolia Dashboard](https://www.algolia.com/dasboard). But make sure you apply those settings locally running the `scout:sync` Artisan command.

## 🚀 Zero Downtime Deployments

In order to keep your existing service running while reimporting your data, we recommend the usage of the `scout:reimport` Artisan command.

```bash
php artisan scout:reimport
```

To ensure that searches performed on the index during the rebuild will not be interrupted. Scout Extended creates a temporary index with all your records before moving the temporary index to the target index

 > **Note:** If you are using the Community Plan, please verify if you have enough number of records available in order to execute the operation.

## ✅ Status

If you are not sure about the current status of your indexes, you can always run the `scout:status` Artisan command to make sure that your records and your settings are up-to-date:

```bash
php artisan scout:status
```

## ⚡️ Aggregators

Scout Extended provides a clean way to implement site-wide search among multiple models.

### Defining aggregators

To create a new aggregator, use the `scout:make-aggregator` Artisan command. This command will create a new aggregator class in the `app/Search` directory. Don't worry if this directory does not exist in your application since it will be created the first time you run the command.

```bash
php artisan make:aggregator News
```

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

To register an Aggregator, use the `bootSearchable` method on the aggregator you wish to register. For this, you should use the boot method of one of your service providers. In this example, we'll register the aggregator in the `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use App\Search\News;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        News::bootSearchable();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
```

### Searching

An aggregator is a normal searchable class, and, as usual, you may begin searching models on the aggregator using the `search` method. You may need to prepare your code to receive different models instances while searching.

```php
$models = App\Search\News::search('Laravel')->get();

echo get_class($models[0]); // "App\Article"
echo get_class($models[1]); // "App\Event"
```

## 🏗 Builder Macros

Scout Extended adds a few methods to the Laravel Scout's builder class.

#### `count()`

The `count` method returns the number of hits matched by the query.

```php
$count = Article::search('query')->count();
```

#### `with()`

The `with` method gives you complete access to customize **search** [API parameters](https://www.algolia.com/doc/api-reference/search-api-parameters).

```php
$models = Article::search('query')
    ->with([
        'hitsPerPage' => 30,
        'filters' => 'attribute:value',
        'typoTolerance' => false,
    ])->get();
```

#### `aroundLatLng()`

The `aroundLatLng ` method will add geolocation parameter to the search request. You can define a point with its coordinate. This method is pure syntactic sugar, you can use the method `with` to specify more location details such us `aroundRadius` or `aroundLatLngViaIP`.

```
$models = Article::search('query')
    ->aroundLatLng(48.8588536, 2.3125377)
    ->get();
```

## ✂️ Split Records

For performance reasons, objects in Algolia should be 10kb or less. Large records can be split into smaller documents by splitting on a logical chunk such as paragraphs or sentences.

To split an attribute, your searchable class must implement a `splitAttribute` method. This means that if you want to split the `body` attribute, the method name will be `splitBody`.

### Split directly on the searchable class
The most basic way of split a record is doing it directly on the searchable class:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use Searchable;

    /**
     * Splits the given value.
     *
     * @param  string $value
     * @return mixed
     */
    public function splitBody($value)
    {
        return explode('. ', $value);
    }
}
```

### Split using a splitter

Of course, sometimes you will need to isolate the splitting logic into a dedicated class.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Algolia\ScoutExtended\Splitters\HtmlSplitter;

class Article extends Model
{
    use Searchable;

    /**
     * Splits the given value.
     *
     * @param  string $value
     * @return mixed
     */
    public function splitBody($value)
    {
        return HtmlSplitter::class; // You can also return an instance instead of the class name.
    }
}
```

### Writing Splitters

One of the primary benefits of creating a `Splitter` class is the ability to type-hint any dependencies your splitter may need in its constructor. The declared dependencies will automatically be resolved and injected into the splitter instance.

Writing a splitter is simple. Create a new `Invokable` class, and the `__invoke` method should split the given `$value` as needed:

```php
<?php

namespace App\Splitters;

use App\Contracts\SplitterService;

class CustomSplitter
{
	 /**
     * @var \App\Contracts\SplitterService
     */
    protected $service;

	 /**
     * Creates a new instance of the class.
     *
     * @param  \App\Contracts\SplitterService $service
     *
     * @return void
     */
    public function __construct(SplitterService $service)
    {
    	 $this->service = $service;
    }

    /**
     * Splits the given value.
     *
     * @param  object $searchable
     * @param  mixed $value
     *
     * @return array
     */
    public function __invoke($searchable, $value)
    {
    	 $values = $this->service->split($searchable->articleType, $value);

        return $values;
    }
}
```

### Distinct

Distinct functionality allows you to force the algolia to return distinct results based on one attribute defined in `attributeForDistinct`. Using this attribute, you can limit the number of returned records that contain the same value in that attribute.

In order to use the distinct functionality, you should configure the `attributeForDistinct` in your `config/scout-{index-name}.php` configuration file:

```
// ...
/*
|--------------------------------------------------------------------------
| Distinct
|--------------------------------------------------------------------------
|
| Using this attribute, you can limit the number of returned records that contain the same
| value in that attribute. For example, if the distinct attribute is the series_name and
| several hits (Episodes) have the same value for series_name (Laravel From Scratch).
|
| Example: 'null', 'id', 'name'
|
*/

'distinct' => true,
'attributeForDistinct' => 'slug',
// ...
```

> **Note:** If the `config/scout-{index-name}.php` file doesn't exist, it will be created when you run the `scout:sync` Artisan command.

## 🏄‍♂️ Algolia Facade

The Algolia Facade may be used to interact with [Algolia's PHP client](https://github.com/algolia/algoliasearch-client-php).

#### `Algolia::client()`

The `client ` method returns an instance of `AlgoliaSearch\Client`:

```php
use Algolia\ScoutExtended\Facades\Algolia;

$client = Algolia::client();
$apiKeys = $client->listApiKeys();
```

#### `Algolia::index()`

The `index` method returns an instance of `AlgoliaSearch\Index`:

```php
use Algolia\ScoutExtended\Facades\Algolia;

$index = Algolia::index('contacts');
$synonym = $index->getSynonym("a-unique-identifier");
$rule = $index->getRule('a-rule-id');
```

#### `Algolia::analytics()`

The `analytics` method returns an instance of `AlgoliaSearch\Analytics`:

```php
use Algolia\ScoutExtended\Facades\Algolia;

$analytics = Algolia::analytics();
$test = $analytics->getABTest(42);
```

## 🎨 Scout Directive - Scout & Vue InstantSearch

This package contains a blade directive to provide easy integration with Vue InstantSearch.

### Installation

```bash
npm install
npm install vue-instantsearch@alpha
```

Then, open up your `resources/assets/js/app.js` and add:

```javascript
import InstantSearch from 'vue-instantsearch';
Vue.use(InstantSearch);
```

### Usage

```html
@scout(['searchable' => 'App\Article'])

<ais-search-box/>

<ais-hits>
  <template slot="item" slot-scope="{ item }">
    <h2>
      <a :href="item.url">
        {{ item.title }}
      </a>
    </h2>
    <p>{{ item.description }}</p>
  </template>
</ais-hits>

@endscout
```

## 🤫 Others

Both `scout:flush` and `scout:import` Artisan commands got replaced by implementations that better fits Algolia.

## 🆓 License

Scout Extended is an open-sourced software licensed under the [MIT license](LICENSE.md).
