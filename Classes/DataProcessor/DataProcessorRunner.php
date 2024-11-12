<?php

declare(strict_types=1);

namespace In2code\Femanager\DataProcessor;

use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use UnexpectedValueException;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class DataProcessorRunner
 */
class DataProcessorRunner
{
    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * TypoScript settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $interface = DataProcessorInterface::class;

    /**
     * Call classes after submit but before action
     *
     * @throws \Exception
     */
    public function callClasses(
        array $settings,
        ContentObjectRenderer $contentObject,
        Arguments $controllerArguments
    ): void {
        foreach ($this->getClasses($settings) as $configuration) {
            $class = $configuration['class'];
            if (!class_exists($class)) {
                throw new UnexpectedValueException(
                    'DataProcessor class ' . $class . ' does not exists - check if file is loaded correctly',
                    1516373818752
                );
            }

            if (is_subclass_of($class, $this->interface)) {
                /** @var AbstractDataProcessor $dataProcessor */
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $dataProcessor = GeneralUtility::makeInstance(
                    $class,
                    $configuration['config'] ?? [],
                    $settings,
                    $contentObject,
                    $controllerArguments
                );
                $dataProcessor->initializeDataProcessor();
                $dataProcessor->process();
            } else {
                throw new UnexpectedValueException('Finisher does not implement ' . $this->interface, 1516373829946);
            }
        }
    }

    /**
     * Get all classes to this event from typoscript and sort them
     */
    protected function getClasses(array $settings): array
    {
        $allDataProcessors = ConfigurationUtility::getValue('dataProcessors', $settings);
        ksort($allDataProcessors);
        $dataProcessors = [];
        foreach ($allDataProcessors as $dataProcessor) {
            if (isset($dataProcessor['events']) && $dataProcessor['events'] !== []) {
                foreach ($dataProcessor['events'] as $controllerName => $actionList) {
                    if ($controllerName !== FrontendUtility::getControllerName()) {
                        continue;
                    }

                    if (!GeneralUtility::inList($actionList, FrontendUtility::getActionName())) {
                        continue;
                    }

                    $dataProcessors[] = $dataProcessor;
                }
            }
        }

        return $dataProcessors;
    }
}
