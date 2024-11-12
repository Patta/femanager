<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Tests\Unit\Fixture\Utility\BackendUtility as BackendUtilityFixture;
use In2code\Femanager\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class BackendUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\BackendUtility
 */
class BackendUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();

        // ApplicationType needs to be faked to represent backend-mode here
        $request = new ServerRequest();
        $applicationType = SystemEnvironmentBuilder::REQUESTTYPE_BE;
        $request = $request->withAttribute('applicationType', $applicationType);
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getPageIdentifier
     */
    public function testGetPageIdentifier(): void
    {
        $_GET['id'] = 123;
        self::assertSame(123, BackendUtility::getPageIdentifier());
    }

    /**
     * @covers ::getPluginOrModuleString
     */
    public function testGetPluginOrModuleString(): void
    {
        $result = BackendUtility::getPluginOrModuleString();
        self::assertSame('module', $result);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getCurrentParameters
     */
    public function testGetCurrentParameters(): void
    {
        $testParams = ['foo' => ['bar']];
        $_GET = $testParams;
        $_POST['klks'] = ['test'];
        self::assertSame($testParams, BackendUtilityFixture::getCurrentParametersPublic());
    }
}
