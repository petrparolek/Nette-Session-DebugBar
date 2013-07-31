<?php

namespace Kdyby\SessionPanel\DI;

use Nette;



if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (!class_exists('Nette\PhpGenerator\ClassType')) {
	class_alias('Nette\Utils\PhpGenerator\ClassType', 'Nette\PhpGenerator\ClassType');	
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']);
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

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
				'Nette\Diagnostics\Debugger::' . (method_exists('Nette\Diagnostics\Debugger', 'getBar') ? 'getBar()' : '$bar') . '->addPanel(?);',
				Nette\DI\Compiler::filterArguments(array(new Nette\DI\Statement($this->prefix('@panel'))))
			));
		}
	}

	/**
	 * @param \Nette\Configurator $configurator
	 */
	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('debugger.session', new static());
		};
	}

}
