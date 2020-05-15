<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  __DEPLOY_VERSION__
 */
class CssIdentifierRule extends FormRule
{
	/**
	 * Method to test if the file path is valid
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		$value = trim($value);

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		// Make sure we allow multible classes to be added
		$cssIdentifiers = explode(' ', $value);

		foreach ($cssIdentifiers as $identifier)
		{
			/**
			 * The folowing regex rules are based on the Html::cleanCssIdentifier method from Drupal
			 * https://github.com/drupal/drupal/blob/8.8.5/core/lib/Drupal/Component/Utility/Html.php#L116-L130
			 */

			/**
			 * Valid characters in a CSS identifier are:
			 * - the hyphen (U+002D)
			 * - a-z (U+0030 - U+0039)
			 * - A-Z (U+0041 - U+005A)
			 * - the underscore (U+005F)
			 * - 0-9 (U+0061 - U+007A)
			 * - ISO 10646 characters U+00A1 and higher
			 */
			if (preg_match('/[^\\x{002D}\\x{0030}-\\x{0039}\\x{0041}-\\x{005A}\\x{005F}\\x{0061}-\\x{007A}\\x{00A1}-\\x{FFFF}]/u', $identifier))
			{
				return false;
			}

			// Identifiers cannot start with a digit, two hyphens, or a hyphen followed by a digit.
			if (preg_match('/^[0-9]/', $identifier) || preg_match('/^(-[0-9])|^(--)/', $identifier))
			{
				return false;
			}
		}

		return true;
	}
}
