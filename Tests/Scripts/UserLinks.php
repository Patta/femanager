<?php

namespace In2code\Femanager\Tests\Scripts;

use Doctrine\DBAL\DBALException;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\HashUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class UserLinks
 */
class UserLinks
{
    /**
     * @var ContentObjectRenderer
     */
    protected $cObj;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->username = $GLOBALS['TYPO3_REQUEST']->getQueryParams()['username'] ?? null;
        if ($this->username === null) {
            $this->username = $this->getLastUsername();
        }

        $this->pid = $GLOBALS['TYPO3_REQUEST']->getQueryParams()['pid'] ?? null;
        if ($this->pid === null) {
            throw new \Exception('pid missing like ?pid=123', 5974318372);
        }
    }

    /**
     * @throws Exception
     */
    public function links(): string
    {
        $row = $this->getUserData($this->username);
        $content = '<h2>Get confirmation links of one FE_User</h2>';
        $content .= 'username: ' . $row['username'] . '<br />';
        $content .= 'email: ' . $row['email'] . '<br />';
        $content .= 'uid: ' . $row['uid'] . '<br />';
        $content .= 'status: ' . ($row['disable'] === 0 ? 'enabled' : 'disabled') . '<br />';
        $content .= '<a href="' . $this->getAdminConfirmationUri($row) . '">Admin confirmation link</a><br />';

        return $content . ('<a href="' . $this->getUserConfirmationUri($row) . '">User confirmation link</a><br />');
    }

    /**
     * For
     * <f:uri.action action="confirmCreateRequest" controller="New" absolute="1"
     *      arguments="{user:user, hash:hash, status:'adminConfirmation'}" />
     *
     * @return string
     */
    public function getAdminConfirmationUri(array $row)
    {
        $params = '';
        $params .= '&tx_femanager_registration[user]=' . $row['uid'];
        $params .= '&tx_femanager_registration[hash]=' . $this->getHash($row);
        $user = new User();
        $user->setUsername($row['username']);
        $params .= '&tx_femanager_registration[adminHash]=' . HashUtility::createHashForUser($user, 'admin');
        $params .= '&tx_femanager_registration[status]=adminConfirmation';
        $params .= '&tx_femanager_registration[action]=confirmCreateRequest';
        $params .= '&tx_femanager_registration[controller]=New';
        $configuration = [
            'parameter' => (int)$this->pid,
            'additionalParams' => $params,
            'returnLast' => 'url',
            'useCacheHash' => '1',
        ];

        return $this->cObj->typoLink_URL($configuration);
    }

    /**
     * For
     * <f:uri.action action="confirmCreateRequest" controller="New" absolute="1"
     *      arguments="{user:user, hash:hash, status:'userConfirmation'}" />
     *
     * @return string
     */
    public function getUserConfirmationUri(array $row)
    {
        $params = '';
        $params .= '&tx_femanager_registration[user]=' . $row['uid'];
        $params .= '&tx_femanager_registration[hash]=' . $this->getHash($row);
        $params .= '&tx_femanager_registration[status]=userConfirmation';
        $params .= '&tx_femanager_registration[action]=confirmCreateRequest';
        $params .= '&tx_femanager_registration[controller]=New';
        $configuration = [
            'parameter' => (int)$this->pid,
            'additionalParams' => $params,
            'returnLast' => 'url',
            'useCacheHash' => '1',
        ];

        return $this->cObj->typoLink_URL($configuration);
    }

    protected function getHash(array $row, $suffix = ''): string
    {
        $user = new User();
        $user->setUsername($row['username']);
        return HashUtility::createHashForUser($user, $suffix = '');
    }

    /**
     * @param $username
     */
    protected function getUserData($username): array|false|null
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->select('*')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)),
                $queryBuilder->expr()->eq('pid', 5)
            )
            ->setMaxResults(1);
        try {
            return $queryBuilder->executeQuery()->fetchAssociative();
        } catch (DBALException $dbalException) {
            $errorMsg = $dbalException->getMessage();

            return 'Could not fetch fe_users. ' . $errorMsg;
        }
    }

    protected function getLastUsername(): string|false|null
    {
        $content = null;
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->select('username')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq('pid', 5)
            )
            ->orderBy('uid', 'desc')
            ->setMaxResults(1);

        try {
            $res = $queryBuilder->executeQuery();
            if ($res) {
                $row = $res->fetchAssociative();
                $content = $row['username'];
            }
        } catch (DBALException $dbalException) {
            $content = 'error: ' . $dbalException->getMessage();
        }

        return $content;
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
