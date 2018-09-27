

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

    'searchableAttributes' => {!! $searchableAttributes ?? 'null' !!},

    /*
    |--------------------------------------------------------------------------
    | Disable Typo Tolerance
    |--------------------------------------------------------------------------
    |
    | Algolia provides robust "typo-tolerance" out-of-the-box. This parameter accepts an
    | array of attributes for which typo-tolerance should be disabled. This is useful,
    | for example, products that might require SKU search without "typo-tolerance".
    |
    | Example: ['id', 'sku', 'reference', 'code',]
    |
    */

    'disableTypoToleranceOnAttributes' => {!! $disableTypoToleranceOnAttributes ?? 'null' !!},

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

    'customRanking' => {!! $customRanking ?? 'null'!!},

    /*
    |--------------------------------------------------------------------------
    | Attributes For Faceting
    |--------------------------------------------------------------------------
    |
    | Your index comes with no categories. By designating an attribute as a facet, this enables
    | Algolia to compute a set of possible values that can later be used to create categories
    | or filters. You can also get a count of records that match those values.
    |
    | Example: ['type', 'filterOnly(country)', 'searchable(city)',]
    |
    */

    'attributesForFaceting' => {!! $attributesForFaceting ?? 'null'  !!},

    /*
    |--------------------------------------------------------------------------
    | Unretrievable Attributes
    |--------------------------------------------------------------------------
    |
    | This is particularly important for security or business reasons, where some attributes are
    | used only for ranking or other technical purposes, but should never be seen by your end
    | users, such us: total_sales, permissions, stock_count, and other private information.
    |
    | Example: ['total_sales', 'permissions', 'stock_count',]
    |
    */

    'unretrievableAttributes' => {!! $unretrievableAttributes ?? 'null' !!},

    /*
    |--------------------------------------------------------------------------
    | Ignore Plurals
    |--------------------------------------------------------------------------
    |
    | Treats singular, plurals, and other forms of declensions as matching terms. When
    | enabled, will make the engine consider “car” and “cars”, or “foot” and “feet”,
    | equivalent. This is used in conjunction with the "queryLanguages" setting.
    |
    | Example: true
    |
    */

    'ignorePlurals' => {!! $ignorePlurals ?? 'null' !!},

    /*
    |--------------------------------------------------------------------------
    | Remove Stop Words
    |--------------------------------------------------------------------------
    |
    | Stop word removal is useful when you have a query in natural language, e.g.
    | “what is a record?”. In that case, the engine will remove “what”, “is”,
    | before executing the query, and therefore just search for “record”.
    |
    | Example: true
    |
    */

    'removeStopWords' => {!! $removeStopWords ?? 'null' !!},

    /*
    |--------------------------------------------------------------------------
    | Query Languages
    |--------------------------------------------------------------------------
    |
    | Sets the languages to be used by language-specific settings such as
    | "removeStopWords" or "ignorePlurals". For optimum relevance, it is
    | recommended to only enable languages that are used in your data.
    |
    | Example: ['en', 'fr',]
    |
    */

    'queryLanguages' => {!! $queryLanguages ?? 'null' !!},

    /*
    |--------------------------------------------------------------------------
    | Other Modified Settings
    |--------------------------------------------------------------------------
    |
    | ..
    |
    | Supported: ..
    |
    */
{!! $__indexChangedSettings  !!};
