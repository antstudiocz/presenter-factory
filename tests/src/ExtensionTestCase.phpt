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
class ExtensionTestCase extends Tester\TestCase
{

	/** @var Nette\Configurator */
	protected $configurator;


	public function setUp()
	{
		$this->configurator = new Nette\Configurator();
		$this->configurator->setTempDirectory(TEMP_DIR);
	}


	public function testBasic()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/basic.neon');
		$this->configurator->setDebugMode(TRUE);
		$container = $this->configurator->createContainer();

		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		Assert::type('Librette\Application\PresenterFactory\PresenterFactory', $presenterFactory = $container->getByType('Nette\Application\IPresenterFactory'));
		Assert::same(['*'     => [['', '*Module\\', '*Presenter']],
		              'Nette' => [['NetteModule\\', '*\\', '*Presenter']]], $presenterFactory->getMapping());

		$objectFactory = $container->getByType('Librette\Application\PresenterFactory\IPresenterObjectFactory');
		$rc = new \ReflectionClass($objectFactory);
		$rp = $rc->getProperty('invalidLinkMode');
		$rp->setAccessible(TRUE);
		Assert::same(Nette\Application\UI\Presenter::INVALID_LINK_WARNING, $rp->getValue($objectFactory));
	}


	public function testProduction()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/basic.neon');
		$this->configurator->setDebugMode(FALSE);
		$container = $this->configurator->createContainer();

		$objectFactory = $container->getByType('Librette\Application\PresenterFactory\IPresenterObjectFactory');
		$rc = new \ReflectionClass($objectFactory);
		$rp = $rc->getProperty('invalidLinkMode');
		$rp->setAccessible(TRUE);
		Assert::same(Nette\Application\UI\Presenter::INVALID_LINK_SILENT, $rp->getValue($objectFactory));
	}


	public function testOriginalMapping()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/originalMapping.neon');
		$container = $this->configurator->createContainer();
		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		$presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		Assert::same(['*'     => [['App\\', '*Module\\', 'Presenters\\*Presenter']],
		              'Nette' => [['NetteModule\\', '*\\', '*Presenter']]], $presenterFactory->getMapping());
	}


	public function testNewMapping()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/newMapping.neon');
		$container = $this->configurator->createContainer();
		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		$presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		Assert::same(['*'     => [['App\\', '*Module\\', 'Presenters\\*Presenter'], ['', '*Module\\', '*Presenter']],
		              'Nette' => [['NetteModule\\', '*\\', '*Presenter']]], $presenterFactory->getMapping());
	}


	public function testBothMappingFail()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/bothMapping.neon');

		Assert::exception(function () {
			$this->configurator->createContainer();
		}, \Librette\Application\PresenterFactory\InvalidStateException::class);
	}


	public function testMappingProvider()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/mappingProvider.neon');
		$container = $this->configurator->createContainer();
		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		$presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		Assert::same(['*'     => [['', '*Module\\', '*Presenter']],
		              'Nette' => [['NetteModule\\', '*\\', '*Presenter']],
		              'Foo'   => [['Foo\\', '*Module\\', '*Presenter']],
		              'Bar'   => [['', '*Module\\', '*Presenter'], ['Bar\\', '*\\', '*']]], $presenterFactory->getMapping());
	}

}


class MyExtension extends Nette\DI\CompilerExtension implements Librette\Application\PresenterFactory\DI\IPresenterMappingProvider
{


	public function getPresenterMappings(): array
	{
		return ['Foo' => 'Foo\\*Module\\*Presenter', 'Bar' => ['*Presenter', 'Bar\\*\\*']];
	}

}


\run(new ExtensionTestCase());
