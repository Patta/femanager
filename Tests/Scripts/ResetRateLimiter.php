<?php

namespace In2code\Femanager\Tests\Scripts;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DeleteFeusers
 */
class ResetRateLimiter
{
    /**
     * @return string
     */
    public function reset()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('cache_femanager_ratelimiter');
        $queryBuilder
            ->delete('cache_femanager_ratelimiter');

        try {
            $queryBuilder->execute();

            return 'Rate limiter cache has been reset';
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }
        return 'Could not reset rate limiter. ' . $errorMsg;
    }
}
