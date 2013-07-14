<?php

namespace Kdyby\SessionPanel\DI;

use Nette;



/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SessionPanelExtension extends Nette\DI\CompilerExtension
{

	/** @var array */
	public $defaults = array(
		'hiddenSections' => array(),
	);

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if ($builder->parameters['debugMode']) {
			$builder->addDefinition($this->prefix('panel'))
				->setClass('Kdyby\SessionPanel\Diagnostics\SessionPanel');

			Nette\Utils\Validators::assertField($config, 'hiddenSections', 'array');

			foreach ($config['hiddenSections'] as $section) {
				$builder->getDefinition($this->prefix('panel'))
					->addSetup('hideSection', array($section));
			}
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$builder = $this->getContainerBuilder();
		if ($builder->parameters['debugMode']) {
			$class->methods['initialize']->addBody($builder->formatPhp(
				'Nette\Diagnostics\Debugger::getBar()->addPanel(?);',
				Nette\DI\Compiler::filterArguments(array(new Nette\DI\Statement($this->prefix('@panel'))))
			));
		}
	}

}
