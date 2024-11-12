<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Repository;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetFirstViewHelper
 */
class GetFirstViewHelper extends AbstractViewHelper
{
    /**
     * Call getFirst() method of object storage
     */
    public function render(): ?object
    {
        $objects = $this->arguments['objects'];
        if (method_exists($objects, 'getFirst')) {
            return $objects->getFirst();
        }

        return null;
    }

    /**
     * Initialize the arguments.
     *
     * @api
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('objects', 'object ', 'Call getFirst() method of object storage', true);
    }
}
