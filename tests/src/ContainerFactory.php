<?php

namespace LibretteTests\Application\PresenterFactory;

/**
 * @internal
 */
class ContainerFactory
{
	use \Nette\SmartObject;

	private static $container;

	protected $meta = [
		'types' => [
			'LibretteTests\Application\PresenterFactory\PresenterMock' => [
				1 => ['fooPresenter'],
			],
		],
	];

	private function __construct()
	{
		//Cannot be initialized
	}


	final public static function create(bool $new = FALSE, array $config = []): \Nette\DI\Container
	{
		if ($new || self::$container === NULL) {
			$configurator = new \Nette\Configurator();
			$configurator->addParameters($config);

			$configurator->onCompile[] = function (\Nette\Configurator $configurator, \Nette\DI\Compiler $compiler) use ($config) {
				$compiler->addConfig($config);
			};

			$configurator->setTempDirectory(__DIR__ . '/_temp'); // shared container for performance purposes
			$configurator->setDebugMode(FALSE);

			self::$container = $configurator->createContainer();
		}
		return self::$container;
	}

	final public function __clone()
	{
		throw new \Exception('Clone is not allowed');
	}


	final public function __wakeup(): void
	{
		throw new \Exception('Unserialization is not allowed');
	}

}
