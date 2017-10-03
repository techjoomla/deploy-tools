<?php
/**
 * @package    Deployment_Plugin
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

/**
 * plgSystemDeployTools
 *
 * @package  Deployment_Plugin
 * @since    1.0
 */
class PlgSystemDeployTools extends JPlugin
{
	protected var $_cache = null;

	/**
	 * Function on beforeCompileHead
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function onBeforeCompileHead()
	{
		$version_filename = $this->params->get('version_filename');
		$version_file = JPATH_SITE . '/' . $version_filename;
		$excluded_files = $this->params->get('remove_external_files');
		$excluded_files = explode("\n", $excluded_files);

		// Find out version number. If no version number, exit
		if ($this->params->get('version_no'))
		{
			$version = $this->params->get('version_no');
		}
		elseif (JFile::exists($version_file))
		{
			$version = trim(JFile::read($version_file));
		}
		else
		{
			return false;
		}

		define("DEPLOYTOOLS_VERSION", $version);
		$document = JFactory::getDocument();
		$headerstuff = $document->getHeadData();
		$head = array();

		if ($this->params->get('process_css', 1))
		{
			$newarray = array();

			foreach ($headerstuff['styleSheets'] as $key => $value)
			{
				$str = (strstr($key, '?')) ? $key . "&amp;" . $version : $key . "?" . $version;
				$newarray[$str] = $value;
			}

			$head['styleSheets'] = $newarray;
		}

		if ($this->params->get('process_js', 1))
		{
			$newarray = array();

			foreach ($headerstuff['scripts'] as $key => $value)
			{
				// If condition added to work accordion under help menu, abbreviations

				if (JRequest::getVar('option') != 'com_content')
				{
					if ($this->params->get('remove_external_files') && in_array($key, $excluded_files))
					{
						continue;
					}
				}

				$str = (strstr($key, '?')) ? $key . "&amp;" . $version : $key . "?" . $version;
				$newarray[$str] = $value;
			}

			$head['scripts'] = $newarray;
		}

		if ($this->params->get('process_custom', 1))
		{
			$newarray = array();

			foreach ($headerstuff['custom'] as $key => $value)
			{
				// Replce string '.js' with '.js.DEPLOYTOOL_VERSION'
				$outputAfterjsChange = str_replace(".js", ".js?" . $version, $value);

				// Replce string '.css' with '.css.DEPLOYTOOL_VERSION'
				$newarray[$key] = str_replace(".css", ".css?" . $version, $outputAfterjsChange);
			}

			// After replcement, copy new array to cutom key
			$head['custom'] = $newarray;
		}

		$document->setHeadData($head);
	}
}
