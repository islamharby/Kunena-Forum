<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Crypsis
 * @subpackage      Template
 *
 * @copyright       Copyright (C) 2008 - 2018 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

/**
 * Crypsis template.
 *
 * @since  K4.0
 */
class KunenaTemplateCrypsis extends KunenaTemplate
{
	/**
	 * List of parent template names.
	 *
	 * This template will automatically search for missing files from listed parent templates.
	 * The feature allows you to create one base template and only override changed files.
	 *
	 * @var array
	 * @since Kunena
	 */
	protected $default = array('crypsis');

	/**
	 * Template initialization.
	 *
	 * @return void
	 * @throws Exception
	 * @since Kunena
	 */
	public function initialize()
	{
		// Template requires Bootstrap javascript
		HTMLHelper::_('bootstrap.framework');

		// Template also requires jQuery framework.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('bootstrap.tooltip');

		if (version_compare(JVERSION, '4.0', '>'))
		{
			HTMLHelper::_('bootstrap.renderModal');
		}
		else
		{
			HTMLHelper::_('bootstrap.modal');
		}

		// Load JavaScript.
		$this->addScript('assets/js/main.js');

		$ktemplate = KunenaFactory::getTemplate();
		$storage   = $ktemplate->params->get('storage');

		if ($storage)
		{
			$this->addScript('assets/js/localstorage.js');
		}

		// Compile CSS from LESS files.
		$this->compileLess('assets/less/crypsis.less', 'kunena.css');
		$this->addStyleSheet('kunena.css');

		$filenameless = JPATH_SITE . '/components/com_kunena/template/crypsis/assets/less/custom.less';

		if (file_exists($filenameless) && 0 != filesize($filenameless))
		{
			$this->compileLess('assets/less/custom.less', 'kunena-custom.css');
			$this->addStyleSheet('kunena-custom.css');
		}

		$filename = JPATH_SITE . '/components/com_kunena/template/crypsis/assets/css/custom.css';

		if (file_exists($filename))
		{
			$this->addStyleSheet('assets/css/custom.css');
		}

		$bootstrap = $ktemplate->params->get('bootstrap');
		$doc       = Factory::getDocument();

		if ($bootstrap)
		{
			$doc->addStyleSheet(\Joomla\CMS\Uri\Uri::base(true) . '/media/jui/css/bootstrap.min.css');
			$doc->addStyleSheet(\Joomla\CMS\Uri\Uri::base(true) . '/media/jui/css/bootstrap-extended.css');
			$doc->addStyleSheet(\Joomla\CMS\Uri\Uri::base(true) . '/media/jui/css/bootstrap-responsive.min.css');

			if ($ktemplate->params->get('icomoon'))
			{
				$doc->addStyleSheet(\Joomla\CMS\Uri\Uri::base(true) . '/media/jui/css/icomoon.css');
			}
		}

		$fontawesome = $ktemplate->params->get('fontawesome');

		if ($fontawesome)
		{
			$doc->addScript('https://use.fontawesome.com/releases/v5.2.0/js/all.js', array(), array('defer' => true));
			$doc->addScript('https://use.fontawesome.com/releases/v5.2.0/js/v4-shims.js', array(), array('defer' => true));
		}

		// Load template colors settings
		$styles    = <<<EOF
		/* Kunena Custom CSS */
EOF;
		$iconcolor = $ktemplate->params->get('IconColor');

		if ($iconcolor)
		{
			$styles .= <<<EOF
		.layout#kunena [class*="category"] i,
		.layout#kunena .glyphicon-topic,
		.layout#kunena h3 i,
		.layout#kunena #kwho i.icon-users,
		.layout#kunena#kstats i.icon-bars { color: {$iconcolor}; }
EOF;
		}

		$iconcolornew = $ktemplate->params->get('IconColorNew');

		if ($iconcolornew)
		{
			$styles .= <<<EOF
		.layout#kunena [class*="category"] .knewchar { color: {$iconcolornew} !important; }
		.layout#kunena sup.knewchar { color: {$iconcolornew} !important; }
		.layout#kunena .topic-item-unread { border-left-color: {$iconcolornew} !important;}
		.layout#kunena .topic-item-unread .icon { color: {$iconcolornew} !important;}
		.layout#kunena .topic-item-unread i.fa { color: {$iconcolornew} !important;}
		.layout#kunena .topic-item-unread svg { color: {$iconcolornew} !important;}
EOF;
		}

		$document = Factory::getDocument();
		$document->addStyleDeclaration($styles);

		parent::initialize();
	}

	/**
	 * @param   string $filename filename
	 * @param   string $group    group
	 *
	 * @return \Joomla\CMS\Document\Document
	 * @since Kunena
	 */
	public function addStyleSheet($filename, $group = 'forum')
	{
		$filename = $this->getFile($filename, false, '', "media/kunena/cache/{$this->name}/css");

		return Factory::getDocument()->addStyleSheet(\Joomla\CMS\Uri\Uri::root(true) . "/{$filename}");
	}
}
