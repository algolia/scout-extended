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

use Algolia\AlgoliaSearch\Index;

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
     * @param  \Algolia\AlgoliaSearch\Index $index
     *
     * @return array
     */
    public function find(Index $index): array
    {
        $settings = $this->remoteRepository->getSettingsRaw($index);

        if (array_key_exists('userData', $settings)) {
            $userData = @json_decode($settings['userData']);
        }

        return $userData ?? [];
    }

    /**
     * Save the User Data of the given Index.
     *
     * @param  \Algolia\AlgoliaSearch\Index $index
     * @param  array $userData
     *
     * @return void
     */
    public function save(Index $index, array $userData): void
    {
        $currentUserData = $this->find($index);

        $userDataJson = json_encode(array_merge($currentUserData, $userData));

        $index->setSettings(['userData' => $userDataJson])->wait();
    }
}
