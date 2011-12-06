<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Languages Override Controller
 *
 * @package     Joomla.Administrator
 * @subpackage	com_languages
 * @since       2.5
 */
class LanguagesControllerOverride extends JControllerForm
{
	/**
	 * Method to edit an existing record.
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
   *
	 * @since   2.5
	 */
	public function edit()
	{
		// Initialize variables
		$app		= JFactory::getApplication();
		$cid		= JRequest::getVar('cid', array(), 'post', 'array');
		$context	= "$this->option.edit.$this->context";

		// Get the constant name
		$recordId	= (count($cid) ? $cid[0] : JRequest::getCmd('id'));

		// Access check
		if (!$this->allowEdit())
    {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}

		$app->setUserState($context.'.data', null);
		$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, 'id'));

		return true;
	}

	/**
	 * Method to save a record.
	 *
	 * @return  boolean  True if successful, false otherwise.
	 * @since   11.1
	 */
	public function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$app		    = JFactory::getApplication();
		$model		  = $this->getModel();
		$data		    = JRequest::getVar('jform', array(), 'post', 'array');
		$context	  = "$this->option.edit.$this->context";
		$task		    = $this->getTask();

		$recordId	  = JRequest::getCmd('id');
		$data['id'] = $recordId;

		// Access check
		if (!$this->allowSave($data, 'id'))
    {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}

		// Validate the posted data
		$form = $model->getForm($data, false);
		if (!$form)
    {
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

    // Require helper for filter functions called by JForm
    require_once JPATH_COMPONENT.'/helpers/languages.php';

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
    {
			// Get the validation messages
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i]))
        {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
        {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session
			$app->setUserState($context.'.data', $data);

			// Redirect back to the edit screen
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, 'id'), false));

			return false;
		}

		// Attempt to save the data
		if (!$model->save($validData))
    {
			// Save the data in the session
			$app->setUserState($context.'.data', $validData);

			// Redirect back to the edit screen
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, 'id'), false));

			return false;
		}

    // Add message of success
		$this->setMessage(JText::_('COM_LANGUAGES_VIEW_OVERRIDE_SAVE_SUCCESS'));

		// Redirect the user and adjust session state based on the chosen task
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session
				$recordId = $model->getState($this->context.'.id');
				$app->setUserState($context.'.data', null);

				// Redirect back to the edit screen
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($validData['key'], 'id'), false));
				break;

			case 'save2new':
				// Clear the record id and data from the session
				$app->setUserState($context.'.data', null);

				// Redirect back to the edit screen
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend(null, 'id'), false));
				break;

			default:
				// Clear the record id and data from the session
				$app->setUserState($context.'.data', null);

				// Redirect to the list screen
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
				break;
		}

		return true;
	}
	
	/**
	 * Method to cancel an edit.
	 *
	 * @return  boolean  True
   *
	 * @since   2.5
	 */
	public function cancel()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$app		  = JFactory::getApplication();
		$context  = "$this->option.edit.$this->context";

		$app->setUserState($context.'.data',	null);
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

		return true;
	}
}