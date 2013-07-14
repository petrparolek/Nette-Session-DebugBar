# Nette Session panel

## Requirements ##

[Nette Framework 2.1](http://nette.org) or higher and PHP 5.3 or higher.

## Documentation ##
Simple DebugBar to show contents of session.

## Examples ##

To load SessionPanel into the DebugBar insert following code into config.neon.
```neon
extensions:
	debugger.session: Kdyby\SessionPanel\DI\SessionPanelExtension
```

You can also specify sections to hide in the DebugBar.
```neon
debugger.session:
	hiddenSections:
		- 'Nette.Http.UserStorage/'
		- 'Nette.Forms.Form/CSRF'
```
