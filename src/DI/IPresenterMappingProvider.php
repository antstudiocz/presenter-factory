<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory\DI;

/**
 * @author David Matějka
 */
interface IPresenterMappingProvider
{

	public function getPresenterMappings(): array;

}
