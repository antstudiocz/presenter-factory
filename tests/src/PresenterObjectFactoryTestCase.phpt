<?php declare(strict_types = 1);

namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Nette;
use Nette\Application\Request;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class PresenterObjectFactoryTestCase extends Tester\TestCase
{

	public function setUp()
	{
	}


	public function testPresenterInDic()
	{
		$container = ContainerFactory::create(FALSE);
		$presenterObjectFactory = new Librette\Application\PresenterFactory\PresenterObjectFactory($container, 99);

		$object = $presenterObjectFactory->createPresenter($class = 'LibretteTests\Application\PresenterFactory\PresenterMock');
		Assert::type($class, $object);
		Assert::same(99, $object->invalidLinkMode);
	}

	public function testPresenterNotInDic()
	{
		Assert::error(function () {
			$presenterObjectFactory = new Librette\Application\PresenterFactory\PresenterObjectFactory(ContainerFactory::create(FALSE), 1);
			return $presenterObjectFactory->createPresenter('LibretteTests\Application\PresenterFactory\BarPresenterMock');
		}, Nette\DI\MissingServiceException::class);
	}
}

class SystemContainer extends Nette\DI\Container
{

	protected $meta = [
		'types' => [
			'LibretteTests\Application\PresenterFactory\PresenterMock' => [
				1 => ['fooPresenter'],
			],
		],
	];


	public function createServiceFooPresenter(): Nette\Application\UI\Presenter
	{
		return new PresenterMock();
	}
}


class PresenterMock extends Nette\Application\UI\Presenter
{

	function run(Request $request): Nette\Application\IResponse
	{
	}

}


class BarPresenterMock implements Nette\Application\IPresenter
{

	/** @var PresenterMock @inject */
	public $fooPresenter;


	function run(Request $request): Nette\Application\IResponse
	{
	}
}

\run(new PresenterObjectFactoryTestCase());
