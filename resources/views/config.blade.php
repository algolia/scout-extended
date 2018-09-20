

return [

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
