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

use Algolia\AlgoliaSearch\Api\SearchClient;

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
     * @var \Algolia\AlgoliaSearch\Api\SearchClient
     */
    private $client;


    /**
     * UserDataRepository constructor.
     *
     * @param \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository $remoteRepository
     * @param \Algolia\AlgoliaSearch\Api\SearchClient $client
     */
    public function __construct(RemoteSettingsRepository $remoteRepository, SearchClient $client)
    {
        $this->remoteRepository = $remoteRepository;
        $this->client = $client;
    }

    /**
     * Find the User Data of the given Index.
     *
     * @param string $index
     *
     * @return array
     */
    public function find($index): array
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
     * @param string $index
     * @param  array $userData
     *
     * @return void
     */
    public function save($index, array $userData): void
    {
        $currentUserData = $this->find($index);

        $userDataJson = json_encode(array_merge($currentUserData, $userData));

        $result = $this->client->setSettings($index, ['userData' => $userDataJson]);
        $this->client->waitForTask($index, $result['taskID']);
    }

    /**
     * Get the settings hash.
     *
     * @param string $index
     *
     * @return string
     */
    public function getSettingsHash($index): string
    {
        $userData = $this->find($index);

        return $userData['settingsHash'] ?? '';
    }
}
