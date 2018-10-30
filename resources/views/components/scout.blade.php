<ais-instant-search index-name="{{ (new $searchable)->searchableAs() }}"
           :search-client="__algolia.algoliasearch('{{ config('scout.algolia.id') }}', '{{ app(\Algolia\ScoutExtended\Repositories\ApiKeysRepository::class)->getSearchKey($searchable) }}')">
    {{ $slot }}
</ais-instant-search>
