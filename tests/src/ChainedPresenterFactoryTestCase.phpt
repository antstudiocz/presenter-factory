<?php declare(strict_types = 1);

namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class ChainedPresenterFactoryTestCase extends Tester\TestCase
{

	public function setUp()
	{
	}


	public function testChain()
	{
		$chainedPresenterFactory = new Librette\Application\PresenterFactory\ChainedPresenterFactory(new Mocks\PresenterObjectFactoryMock());
		$chainedPresenterFactory->addPresenterFactory(new FailingPresenterFactory());
		$chainedPresenterFactory->addPresenterFactory(new FooPresenterFactory());
		$name = 'Foo';
		Assert::same('FooPresenter', $chainedPresenterFactory->getPresenterClass($name));
	}


	public function testChainFail()
	{
		$chainedPresenterFactory = new Librette\Application\PresenterFactory\ChainedPresenterFactory(new Mocks\PresenterObjectFactoryMock());
		$chainedPresenterFactory->addPresenterFactory(new FailingPresenterFactory());
		$chainedPresenterFactory->addPresenterFactory(new FailingPresenterFactory());
		Assert::exception(function () use ($chainedPresenterFactory) {
			$name = 'Foo';
			$chainedPresenterFactory->getPresenterClass($name);
		}, 'Nette\Application\InvalidPresenterException');
	}
}




class FailingPresenterFactory implements Nette\Application\IPresenterFactory
{

	function getPresenterClass(&$name): string
	{
		throw new Nette\Application\InvalidPresenterException("Unable to create presenter '$name'");
	}


	function createPresenter($name): Nette\Application\IPresenter
	{
	}
}


class FooPresenterFactory implements Nette\Application\IPresenterFactory
{

	function getPresenterClass(&$name): string
	{
		return $name . 'Presenter';
	}


	function createPresenter($name): Nette\Application\IPresenter
	{
	}

}


\run(new ChainedPresenterFactoryTestCase());
