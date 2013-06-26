<?php

namespace Kdyby\Diagnostics\SessionPanel;

use Nette;



if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SessionPanelExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		if ($builder->parameters['debugMode']) {
			$builder->addDefinition($this->prefix('panel'))
				->setClass('Kdyby\Diagnostics\SessionPanel\SessionPanel');
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$builder = $this->getContainerBuilder();
		if ($builder->parameters['debugMode']) {
			$getter = method_exists('Nette\Diagnostics\Debugger', 'getBar') ? 'getBar()' : '$bar';
			$class->methods['initialize']->addBody($builder->formatPhp(
				'Nette\Diagnostics\Debugger::' . $getter . '->addPanel(?);',
				Nette\DI\Compiler::filterArguments(array(new Nette\DI\Statement($this->prefix('@panel'))))
			));
		}
	}

}
