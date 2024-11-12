<?php

namespace In2code\Femanager\Tests\Scripts;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DeleteFrontendSessions
 */
class DeleteFrontendSessions
{
    public function delete(): string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_sessions');
        $queryBuilder
            ->delete('fe_sessions')
            ->where(
                $queryBuilder->expr()->gte('ses_userid', 0)
            );

        try {
            $queryBuilder->executeStatement();

            return 'All frontend sessions deleted';
        } catch (DBALException $dbalException) {
            $errorMsg = $dbalException->getMessage();
        }

        return 'Could not delete fe_sessions. ' . $errorMsg;
    }
}
