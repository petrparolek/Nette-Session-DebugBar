# Nette Session panel

## Requirements ##

[Nette Framework 2.1](http://nette.org) or higher and PHP 5.3 or higher.

## Documentation ##
Simple DebugBar to show contents of session.

## Examples ##

To load SessionPanel into the DebugBar insert following code into config.neon.
```neon
extensions:
	debugger.session: Kdyby\Diagnostics\SessionPanel\SessionPanelExtension
```

You can also specify a section to hide in the DebugBar by add setup section in service definition.
```neon
services:
	debugger.session.panel:
		setup:
			- hideSection('Nette.Http.UserStorage/')
			- hideSection('Nette.Forms.Form/CSRF')
```
