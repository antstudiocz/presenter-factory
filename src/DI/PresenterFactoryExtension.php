<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory\DI;

use Librette\Application\PresenterFactory\InvalidStateException;
use Nette;

/**
 * @author David Matějka
 */
class PresenterFactoryExtension extends Nette\DI\CompilerExtension
{

	protected $defaults = [
		'mapping'         => [
			'*'     => '*Module\\*Presenter',
			'Nette' => 'NetteModule\\*\\*Presenter',
		],
		'invalidLinkMode' => [
			'debug'      => Nette\Application\UI\Presenter::INVALID_LINK_WARNING,
			'production' => Nette\Application\UI\Presenter::INVALID_LINK_SILENT,
		],
	];


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$mappings = $this->getMappings($this->getMappingConfig());
		$builder->removeDefinition('nette.presenterFactory');
		$builder->addDefinition('nette.presenterFactory')
		        ->setFactory('Librette\Application\PresenterFactory\PresenterFactory')
		        ->addSetup('setMapping', [$mappings]);


		$config = $this->validateConfig($this->defaults);
		$env = $builder->parameters['debugMode'] ? 'debug' : 'production';
		$invalidLinkMode = $config['invalidLinkMode'][$env];
		$builder->addDefinition($this->prefix('presenterObjectFactory'))
		        ->setFactory('Librette\Application\PresenterFactory\PresenterObjectFactory', [1 => $invalidLinkMode])
		        ->addSetup('setAlwaysCallInjects', [$this->shouldAlwaysCallInject()]);

	}


	protected function getMappings($mappings)
	{
		$result = [];
		$this->addMappings($result, $mappings);

		foreach ($this->compiler->getExtensions('Librette\Application\PresenterFactory\DI\IPresenterMappingProvider') as $ext) {
			/** @var IPresenterMappingProvider $ext */
			$this->addMappings($result, $ext->getPresenterMappings());
		}

		return $result;
	}


	protected function addMappings(&$current, $mappings)
	{
		if (empty($mappings)) {
			return;
		}
		foreach ($mappings as $key => $mapping) {
			if (!is_array($mapping)) {
				$current[$key][] = $mapping;
			} else {
				$current[$key] = array_merge(isset($current[$key]) ? $current[$key] : [], $mapping);
			}
		}
	}

	/**
	 * @return array
	 * @throws \Librette\Application\PresenterFactory\InvalidStateException
	 */
	protected function getMappingConfig()
	{
		$applicationExtConfig = [];
		$localExtConfig = [];
		/** @var Nette\DI\CompilerExtension[] $extensions */
		$extensions = $this->compiler->getExtensions();

		if (!empty($extensions['application'])) {
			$applicationExtConfig = (array) $extensions['application']->getConfig();
		}
		if (!empty($extensions[$this->name])) {
			$localExtConfig = (array) $extensions[$this->name]->getConfig();
		}
		if (!empty($applicationExtConfig['mapping']) && !empty($localExtConfig['mapping'])) {
			throw new InvalidStateException("You cannot use both application.mapping and {$this->name}.mapping config section, choose one.");
		}
		$userConfig = $localExtConfig['mapping'] ?? $applicationExtConfig['mapping'] ?? [];
		$config = Nette\DI\Config\Helpers::merge($userConfig, $this->defaults['mapping']);

		return $config;
	}

	private function shouldAlwaysCallInject()
	{
		$serviceDef = new Nette\DI\ServiceDefinition();

		return $serviceDef->getTag(Nette\DI\Extensions\InjectExtension::TAG_INJECT);
	}

}
