<?php

declare(strict_types=1);

/**
 * This file is part of Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\ScoutExtended\Repositories;

use Algolia\AlgoliaSearch\SearchIndex;

/**
 * @internal
 */
final class UserDataRepository
{
    /**
     * @var \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository
     */
    private $remoteRepository;

    /**
     * UserDataRepository constructor.
     *
     * @param \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository $remoteRepository
     */
    public function __construct(RemoteSettingsRepository $remoteRepository)
    {
        $this->remoteRepository = $remoteRepository;
    }

    /**
     * Find the User Data of the given Index.
     *
     * @param  \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return array
     */
    public function find(SearchIndex $index): array
    {
        $settings = $this->remoteRepository->getSettingsRaw($index);

        if (array_key_exists('userData', $settings)) {
            $userData = @json_decode($settings['userData'], true);
        }

        return $userData ?? [];
    }

    /**
     * Save the User Data of the given Index.
     *
     * @param  \Algolia\AlgoliaSearch\SearchIndex $index
     * @param  array $userData
     *
     * @return void
     */
    public function save(SearchIndex $index, array $userData): void
    {
        $currentUserData = $this->find($index);

        $userDataJson = json_encode(array_merge($currentUserData, $userData));

        $index->setSettings(['userData' => $userDataJson])->wait();
    }

    /**
     * Get the settings hash.
     *
     * @param  \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return string
     */
    public function getSettingsHash(SearchIndex $index): string
    {
        $userData = $this->find($index);

        return $userData['settingsHash'] ?? '';
    }
}
