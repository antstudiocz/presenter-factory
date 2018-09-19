<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory;

use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenterFactory;
use Nette\Object;
use Nette;

/**
 * @author David Matejka
 */
class ChainedPresenterFactory implements IPresenterFactory
{

    use Nette\SmartObject;

	/** @var IPresenterObjectFactory */
	protected $presenterObjectFactory;

	/** @var IPresenterFactory[] */
	protected $presenterFactories = [];


	/**
	 * @param IPresenterObjectFactory
	 */
	public function __construct(IPresenterObjectFactory $presenterObjectFactory)
	{
		$this->presenterObjectFactory = $presenterObjectFactory;
	}


	/**
	 * @param IPresenterFactory
	 */
	public function addPresenterFactory(IPresenterFactory $presenterFactory)
	{
		$this->presenterFactories[] = $presenterFactory;
	}


	public function getPresenterClass(string &$name): string
	{
		$exceptionMessages = [];
		$lastException = NULL;
		foreach ($this->presenterFactories as $factory) {
			try {
				return $factory->getPresenterClass($name);
			} catch (InvalidPresenterException $lastException) {
				$exceptionMessages[] = $lastException->getMessage();
			}
		}
		$exceptionMessage = "Cannot load presenter '$name'.' All " . count($exceptionMessages) . ' presenter factories have failed: ' . implode(';', $exceptionMessages);
		throw new InvalidPresenterException($exceptionMessage, 0, $lastException);
	}


	public function createPresenter(string $name): Nette\Application\IPresenter
	{
		return $this->presenterObjectFactory->createPresenter($this->getPresenterClass($name));
	}


}
