<?php
/**
 * Kunena Component
 * @package         Kunena.Site
 * @subpackage      Controller.Category
 *
 * @copyright       Copyright (C) 2008 - 2018 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Class ComponentKunenaControllerCategorySubscriptionsDisplay
 *
 * @since  K4.0
 */
class ComponentKunenaControllerCategorySubscriptionsDisplay extends KunenaControllerDisplay
{
	/**
	 * @var string
	 * @since Kunena
	 */
	protected $name = 'Category/List';

	/**
	 * @var
	 * @since Kunena
	 */
	public $total;

	/**
	 * @var
	 * @since Kunena
	 */
	public $pagination;

	/**
	 * @var array
	 * @since Kunena
	 */
	public $categories = array();

	/**
	 * Prepare category subscriptions display.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @throws null
	 * @since Kunena
	 */
	protected function before()
	{
		parent::before();

		$me = KunenaUserHelper::getMyself();

		if (!$me->exists())
		{
			throw new KunenaExceptionAuthorise(Text::_('COM_KUNENA_NO_ACCESS'), 401);
		}

		$limit = $this->input->getInt('limit', 0);

		if ($limit < 1 || $limit > 100)
		{
			$limit = 20;
		}

		$limitstart = $this->input->getInt('limitstart', 0);

		if ($limitstart < 0)
		{
			$limitstart = 0;
		}

		list($total, $this->categories) = KunenaForumCategoryHelper::getLatestSubscriptions($me->userid);

		$topicIds = array();
		$userIds  = array();
		$postIds  = array();

		foreach ($this->categories as $category)
		{
			// Get list of topics.
			if ($category->last_topic_id)
			{
				$topicIds[$category->last_topic_id] = $category->last_topic_id;
			}
		}

		// Pre-fetch topics (also display unauthorized topics as they are in allowed categories).
		$topics = KunenaForumTopicHelper::getTopics($topicIds, 'none');

		// Pre-fetch users (and get last post ids for moderators).
		foreach ($topics as $topic)
		{
			$userIds[$topic->last_post_userid] = $topic->last_post_userid;
			$postIds[$topic->id]               = $topic->last_post_id;
		}

		KunenaUserHelper::loadUsers($userIds);
		KunenaForumMessageHelper::getMessages($postIds);

		// Pre-fetch user related stuff.
		if ($me->exists() && !$me->isBanned())
		{
			// Load new topic counts.
			KunenaForumCategoryHelper::getNewTopics(array_keys($this->categories));
		}

		$this->actions = $this->getActions();

		$this->pagination = new \Joomla\CMS\Pagination\Pagination($total, $limitstart, $limit);

		$this->headerText = Text::_('COM_KUNENA_CATEGORY_SUBSCRIPTIONS');
	}

	/**
	 * Get topic action option list.
	 *
	 * @return array
	 * @since Kunena
	 */
	public function getActions()
	{
		$options   = array();
		$options[] = HTMLHelper::_('select.option', 'none', Text::_('COM_KUNENA_BULK_CHOOSE_ACTION'));
		$options[] = HTMLHelper::_('select.option', 'unsubscribe', Text::_('COM_KUNENA_UNSUBSCRIBE_SELECTED'));

		return $options;
	}

	/**
	 * Prepare document.
	 *
	 * @return void
	 * @throws Exception
	 * @since Kunena
	 */
	protected function prepareDocument()
	{
		$app       = Factory::getApplication();
		$menu_item = $app->getMenu()->getActive();

		$doc    = Factory::getDocument();
		$config = Factory::getConfig();
		$robots = $config->get('robots');

		if ($robots == '')
		{
			$doc->setMetaData('robots', 'index, follow');
		}
		elseif ($robots == 'noindex, follow')
		{
			$doc->setMetaData('robots', 'noindex, follow');
		}
		elseif ($robots == 'index, nofollow')
		{
			$doc->setMetaData('robots', 'index, nofollow');
		}
		else
		{
			$doc->setMetaData('robots', 'nofollow, noindex');
		}

		if ($menu_item)
		{
			$params             = $menu_item->params;
			$params_title       = $params->get('page_title');
			$params_keywords    = $params->get('menu-meta_keywords');
			$params_description = $params->get('menu-meta_description');
			$params_robots      = $params->get('robots');

			if (!empty($params_title))
			{
				$title = $params->get('page_title');
				$this->setTitle($title);
			}
			else
			{
				$title = Text::_('COM_KUNENA_VIEW_CATEGORIES_USER');
				$this->setTitle($title);
			}

			if (!empty($params_keywords))
			{
				$keywords = $params->get('menu-meta_keywords');
				$this->setKeywords($keywords);
			}
			else
			{
				$keywords = Text::_('COM_KUNENA_CATEGORIES');
				$this->setKeywords($keywords);
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$description = Text::_('COM_KUNENA_CATEGORY_SUBSCRIPTIONS') . ' - ' . $this->config->board_title;
				$this->setDescription($description);
			}

			if (!empty($params_robots))
			{
				$robots = $params->get('robots');
				$doc->setMetaData('robots', $robots);
			}
		}
	}
}
