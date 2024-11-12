<?php

declare(strict_types=1);

namespace In2code\Femanager\UserFunc;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\PageTreeService;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserOptions
 */
class UserOptions
{
    /**
     * Just add users from start point to selection
     */
    public function addOptions(array &$params): void
    {
        if ($this->getPages($params) !== []) {
            $params['items'] = [
                $params['items'][0], // please choose
                $params['items'][1], // currently logged in user
            ];

            foreach ($this->getUsers($params) as $user) {
                $params['items'][] = [$user['username'], $user['uid']];
            }
        }
    }

    protected function getUsers(array $params): array
    {
        $queryBuilder = ObjectUtility::getQueryBuilder(User::TABLE_NAME);
        $result = $queryBuilder
            ->select('uid', 'username')
            ->from(User::TABLE_NAME)
            ->where('pid in (' . $this->getPageUidList($params) . ')')
            ->setMaxResults(10000)->orderBy('username', 'ASC')->executeQuery();
        return $result->fetchAllAssociative();
    }

    /**
     * Get lists of page uids where the users are stored with recursive setting
     */
    protected function getPageUidList(array $params): string
    {
        $list = '';
        $pageTreeService = GeneralUtility::makeInstance(PageTreeService::class);
        $depth = $params['flexParentDatabaseRow']['recursive'];
        foreach ($this->getPages($params) as $pageIdentifier) {
            $list .= $pageTreeService->getTreeList($pageIdentifier, $depth, 0, '1');
            $list .= ',';
        }

        return rtrim($list, ',');
    }

    protected function getPages(array $params): array
    {
        $pages = [];
        if (!empty($params['flexParentDatabaseRow']['pages'][0]['uid'])) {
            foreach ($params['flexParentDatabaseRow']['pages'] as $page) {
                $pages[] = $page['uid'];
            }
        }

        return $pages;
    }
}
