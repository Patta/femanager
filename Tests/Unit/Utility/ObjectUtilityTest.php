<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ObjectUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\ObjectUtility
 */
class ObjectUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    /**
     * @covers ::getQueryBuilder
     */
    public function testGetQueryBuilder(): void
    {
        $this->expectExceptionCode(1459422492);
        ObjectUtility::getQueryBuilder('tt_content');
    }

    /**
     * @covers ::implodeObjectStorageOnProperty
     */
    public function testImplodeObjectStorageOnProperty(): void
    {
        $objectStorage = new ObjectStorage();
        $user1 = new User();
        $user1->_setProperty('uid', 123);

        $objectStorage->attach($user1);
        $user2 = new User();
        $user2->_setProperty('uid', 852);

        $objectStorage->attach($user2);
        self::assertSame('123, 852', ObjectUtility::implodeObjectStorageOnProperty($objectStorage));
        self::assertSame('', ObjectUtility::implodeObjectStorageOnProperty($objectStorage, 'uidx'));
    }
}
