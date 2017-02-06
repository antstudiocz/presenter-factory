<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory;

use Nette;

/**
 * @author David Matejka
 */
interface IPresenterObjectFactory
{

	/**
	 * Creates new presenter instance.
	 *
	 * @param  string presenter class name
	 * @return Nette\Application\IPresenter
	 */
	public function createPresenter($class);

}
