<?php
namespace TYPO3\CMS\Soap\Http;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Extbase\Utility\ArrayUtility;

/**
 * Represents a HTTP request
 *
 */
class RequestHeader extends \TYPO3\CMS\Extbase\Http\Request {

	/**
	 * Takes the raw request data and - depending on the request method
	 * maps them into the request object. Afterwards all mapped arguments
	 * can be retrieved by the getArgument(s) method, no matter if they
	 * have been GET, POST or PUT arguments before.
	 *
	 * @param array $getArguments Arguments as found in $_GET
	 * @param array $postArguments Arguments as found in $_POST
	 * @param array $uploadArguments Arguments as found in $_FILES
	 * @return array the unified arguments
	 */
	protected function buildUnifiedArguments(array $getArguments, array $postArguments, array $uploadArguments) {
		$arguments = $getArguments;
			// ignore http-body stuff
		$arguments = ArrayUtility::arrayMergeRecursiveOverrule($arguments, $this->untangleFilesArray($uploadArguments));
		return $arguments;
	}
}

?>