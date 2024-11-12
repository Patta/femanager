<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Finisher\AbstractFinisher;
use In2code\Femanager\Finisher\FinisherInterface;
use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use UnexpectedValueException;

/**
 * Class FinisherService
 */
class FinisherService
{
    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * Classname
     *
     * @var string
     */
    protected $class = '';

    /**
     * Path that should be required
     *
     * @var string|null
     */
    protected $requirePath;

    /**
     * Finisher Configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Controller actionName - usually "createAction" or "confirmationAction"
     *
     * @var string
     */
    protected $actionMethodName;

    /**
     * @var string
     */
    protected $finisherInterface = FinisherInterface::class;

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class): static
    {
        $this->class = $class;
        return $this;
    }

    public function getRequirePath(): ?string
    {
        return $this->requirePath;
    }

    /**
     * Set require path and do a require_once
     */
    public function setRequirePath(?string $requirePath): static
    {
        $this->requirePath = $requirePath;
        if ($this->getRequirePath() && file_exists($this->getRequirePath())) {
            require_once($this->getRequirePath());
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration($configuration): static
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    public function getActionMethodName()
    {
        return $this->actionMethodName;
    }

    public function setActionMethodName(string $actionMethodName): FinisherService
    {
        $this->actionMethodName = $actionMethodName;
        return $this;
    }

    /**
     * Start implementation
     *
     * @throws \Exception
     */
    public function start(): void
    {
        if (!class_exists($this->getClass())) {
            throw new UnexpectedValueException(
                'Class ' . $this->getClass() . ' does not exists - check if file was loaded with autoloader',
                1516373888508
            );
        }

        if (is_subclass_of($this->getClass(), $this->finisherInterface)) {
            /** @var AbstractFinisher $finisher */
            $finisher = GeneralUtility::makeInstance(
                $this->getClass(),
                $this->getUser(),
                $this->getConfiguration(),
                $this->getSettings(),
                $this->getActionMethodName(),
                $this->contentObject
            );
            $finisher->initializeFinisher();
            $this->callFinisherMethods($finisher);
        } else {
            throw new UnexpectedValueException(
                'Finisher does not implement ' . $this->finisherInterface,
                1516373899775
            );
        }
    }

    /**
     * Call methods in finisher class
     */
    protected function callFinisherMethods(AbstractFinisher $finisher)
    {
        foreach (get_class_methods($finisher) as $method) {
            if (!StringUtility::endsWith($method, 'Finisher')) {
                continue;
            }

            if (str_starts_with($method, 'initialize')) {
                continue;
            }

            $this->callInitializeFinisherMethod($finisher, $method);
            $finisher->{$method}();
        }
    }

    /**
     * Call initializeFinisherMethods like "initializeSaveFinisher()"
     *
     * @param string $finisherMethod
     */
    protected function callInitializeFinisherMethod(AbstractFinisher $finisher, $finisherMethod)
    {
        if (method_exists($finisher, 'initialize' . ucfirst($finisherMethod))) {
            $finisher->{'initialize' . ucfirst($finisherMethod)}();
        }
    }

    public function init(User $user, array $settings, ContentObjectRenderer $contentObject): void
    {
        $this->setUser($user);
        $this->setSettings($settings);
        $this->contentObject = $contentObject;
    }
}
