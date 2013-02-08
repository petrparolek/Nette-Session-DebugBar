<?php

namespace Kdyby\Diagnostics\SessionPanel;

use Nette;
use Nette\Http\Request;
use Nette\Iterators\Mapper;
use Nette\Iterators\Filter;



/**
 * Nette Debug Session Panel
 *
 * @author Pavel Železný <info@pavelzelezny.cz>
 * @author Filip Procházka <email@filip-prochazka.cz>
 */
class SessionPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{
	const SIGNAL = 'nette-session-panel-delete-session';

	/**
	 * @var \Nette\Http\Session
	 */
	private $session;

	/**
	 * @var \Nette\Http\UrlScript
	 */
	private $url;

	/**
	 * @var array
	 */
	private $hiddenSections = array(
		'Nette.Http.UserStorage/'
	);



	/**
	 * @param \Nette\Http\Session $session
	 * @param Request $httpRequest
	 */
	public function __construct(Nette\Http\Session $session, Request $httpRequest)
	{
		$this->session = $session;
		$this->url = clone $httpRequest->url;
		$this->processSignal($httpRequest);
	}



	/**
	 * @param \Nette\Http\Request $httpRequest
	 */
	private function processSignal(Request $httpRequest)
	{
		if ($httpRequest->getQuery('do') !== self::SIGNAL) {
			return;
		}

		if (!$this->session->isStarted()) {
			$this->session->start();
		}

		if ($section = $httpRequest->getQuery(self::SIGNAL)) {
			$this->session->getSection($section)->remove();

		} else {
			$this->session->destroy();
		}

		$query = $httpRequest->getQuery();
		unset($query['do'], $query[self::SIGNAL]);
		$this->url->setQuery($query);

		$response = new Nette\Http\Response();
		$response->redirect($this->url);
		exit(0);
	}



	/**
	 * Add section name in list of hidden
	 * @param string $sectionName
	 */
	public function hideSection($sectionName)
	{
		$this->hiddenSections[] = $sectionName;
	}



	/**
	 * Html code for DebuggerBar Tab
	 * @return string
	 */
	public function getTab()
	{
		return self::render(__DIR__ . '/templates/tab.phtml', array(
			'src' => function ($file) {
				return Nette\Templating\Helpers::dataStream(file_get_contents($file));
			},
			'esc' => callback('Nette\Templating\Helpers::escapeHtml')
		));
	}



	/**
	 * Html code for DebuggerBar Panel
	 * @return string
	 */
	public function getPanel()
	{
		$url = $this->url;
		return self::render(__DIR__ . '/templates/panel.phtml', array(
			'time' => callback(get_called_class() . '::time'),
			'esc' => callback('Nette\Templating\Helpers::escapeHtml'),
			'click' => callback(function ($variable) {
				return Nette\Diagnostics\Dumper::toHtml($variable, array(Nette\Diagnostics\Dumper::COLLAPSE => TRUE));
			}),
			'del' => function ($section = NULL) use ($url) {
				$url = clone $url;
				$url->appendQuery(array(
					'do' => SessionPanel::SIGNAL,
					SessionPanel::SIGNAL => $section,
				));
				return (string)$url;
			},
			'sections' => $this->createSessionIterator(),
			'sessionMaxTime' => $this->session->options['gc_maxlifetime']
		));
	}



	/**
	 * @return \Iterator
	 */
	protected function createSessionIterator()
	{
		$hidden = $this->hiddenSections;
		$sections = new Filter($this->session->getIterator(), function ($sectionName) use ($hidden) {
			return !in_array($sectionName, $hidden);
		});
		return new Mapper($sections, function ($sectionName) {
			$data = $_SESSION['__NF']['DATA'][$sectionName];

			$section = (object)array(
				'title' => $sectionName,
				'data' => $data,
				'expiration' => 'inherited'
			);

			$meta = isset($_SESSION['__NF']['META'][$sectionName])
				? $_SESSION['__NF']['META'][$sectionName]
				: array();

			if (isset($meta['']['T'])) {
				$section->expiration = SessionPanel::time($meta['']['T'] - time());
			} elseif (isset($meta['']['B']) && $meta['']['B'] === TRUE) {
				$section->expiration = 'Browser';
			}

			return $section;
		});
	}



	/**
	 * @param string $file
	 * @param array $vars
	 * @return string
	 */
	public static function render($file, $vars)
	{
		ob_start();
		Nette\Utils\LimitedScope::load(str_replace('/', DIRECTORY_SEPARATOR, $file), $vars);
		return ob_get_clean();
	}



	/**
	 * @param int $seconds
	 * @return string
	 */
	public static function time($seconds)
	{
		static $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		static $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

		$difference = $seconds > Nette\DateTime::YEAR ? time() - $seconds : $seconds;
		for ($j = 0; $difference >= $lengths[$j]; $j++) {
			$difference /= $lengths[$j];
		}
		$multiply = ($difference = round($difference)) != 1;
		return "$difference {$periods[$j]}" . ($multiply ? 's' : '');
	}

}
