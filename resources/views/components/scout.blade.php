<ais-index index-name="{{ (new $searchable)->searchableAs() }}"
           app-id="{{ config('scout.algolia.id') }}"
           api-key="{{ app(\Algolia\ScoutExtended\Repositories\ApiKeysRepository::class)->getSearchKey($searchable) }}">
    {{ $slot }}
</ais-index>
