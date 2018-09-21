

return [

    /*
    |--------------------------------------------------------------------------
    | Searchable Attributes
    |--------------------------------------------------------------------------
    |
    | Limits the scope of a search to the attributes listed in this setting. Defining
    | specific attributes as searchable is critical for relevance because it gives
    | you direct control over what information the search engine will look at.
    |
    | Example: ["attribute1", "ordered(attribute4)", "unordered(attribute5)",]
    |
    */
    'searchableAttributes' => {!! $searchableAttributes  !!},

    /*
    |--------------------------------------------------------------------------
    | Attributes For Faceting
    |--------------------------------------------------------------------------
    |
    | Your index comes with no categories. By designating an attribute as a facet, this enables
    | Algolia to compute a set of possible values that can later be used to create categories
    | or filters. You can also get a count of records that match those values.
    |
    | Example: ['attribute1', 'filterOnly(attribute2)', 'searchable(attribute3)',]
    |
    */
    'attributesForFaceting' => {!! $attributesForFaceting  !!},

    /*
    |--------------------------------------------------------------------------
    | Attributes To Retrieve
    |--------------------------------------------------------------------------
    |
    | You don’t always need to retrieve a full response that includes every attribute
    | in your index. Sometimes you may only want to receive the most relevant
    | attributes, or exclude attributes used only for internal purposes.
    |
    | Example: ['attribute1', 'attribute2', 'attribute3',]
    |
    */
    'attributesToRetrieve' => {!! $attributesToRetrieve  !!},

    /*
    |--------------------------------------------------------------------------
    | Custom Ranking
    |--------------------------------------------------------------------------
    |
    | To return great results, custom ranking attributes are applied after records
    | sorted by textual relevance. Said another way, if two matched records have
    | the same match textually, we resort to custom ranking to tie-break.
    |
    | Examples: "asc(name)", "asc(email)", "desc(age)"
    |
    */
    'customRanking' => {!! $customRanking  !!},

    /*
    |--------------------------------------------------------------------------
    | Ranking Formula
    |--------------------------------------------------------------------------
    |
    | Configure how hits are sorted. The ranking formula is implemented as
    | a tie-breaking algorithm, comparing each criteria one after another.
    |
    | Supported: "typo", "geo", "words", "filters", "proximity", "attribute"
    |            "exact", "custom".
    |
    */
    'ranking' => {!! $ranking  !!},

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
