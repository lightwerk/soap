<?php
namespace TYPO3\CMS\Soap;

/*                                                                        *
 * This script belongs to the FLOW3 package "Soap".                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * The SOAP request handler
 */
class RequestHandler implements \TYPO3\CMS\Extbase\Mvc\RequestHandlerInterface {

	const HANDLEREQUEST_OK = 1;
	const HANDLEREQUEST_NOVALIDREQUEST = -1;

	const CANHANDLEREQUEST_OK = 1;
	const CANHANDLEREQUEST_MISSINGSOAPEXTENSION = -1;
	const CANHANDLEREQUEST_NOPOSTREQUEST = -2;
	const CANHANDLEREQUEST_WRONGSERVICEURI = -3;
	const CANHANDLEREQUEST_NOURIBASEPATH = -4;
	const CANHANDLEREQUEST_NOSOAPACTION = -5;

	/**
	 * @inject
	 * @var \TYPO3\CMS\Soap\RequestBuilder
	 */
	protected $requestBuilder;

	/**
	 * @var \TYPO3\CMS\Extbase\Http\Request
	 */
	protected $httpRequest;

	/**
	 * @var mixed
	 */
	protected $lastOperationResult;

	/**
	 * @var \Exception
	 */
	protected $lastCatchedException;

	/**
	 * @var \TYPO3\CMS\Soap\Request
	 */
	protected $request;

	/**
	 * @inject
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @inject
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;


	/**
	 * Handles a SOAP request and sends the response directly to the client.
	 *
	 * @return void
	 */
	public function handleRequest() {
		$this->httpRequest = \TYPO3\CMS\Extbase\Http\Request::createFromEnvironment();

		$settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		$requestBuilder = $this->objectManager->get('TYPO3\CMS\Soap\RequestBuilder');
		$requestBuilder->injectSettings($settings);
		$request = $requestBuilder->build($this->httpRequest);
		if ($request === FALSE) {
			header('HTTP/1.1 404 Not Found');
			echo 'Could not build request - probably no SOAP service matched the given endpoint URI.';
			return self::HANDLEREQUEST_NOVALIDREQUEST;
		}

		$this->processRequest($request);

	}

	/**
	 * Process a SOAP Request and invoke the SoapServer with a ServiceWrapper wrapping
	 * the SOAP service object.
	 *
	 * @param \TYPO3\CMS\Soap\Request $request
	 * @return void
	 */
	public function processRequest(Request $request) {
		$this->request = $request;

		$this->lastOperationResult = NULL;
		$this->lastCatchedException = NULL;

		$settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		$serverOptions = array('soap_version' => SOAP_1_2, 'encoding' => 'UTF-8');
		$soapServer = new \SoapServer((string)$request->getWsdlUri(), $serverOptions);
		$serviceObject = $this->objectManager->get($request->getServiceObjectName());
		$serviceWrapper = $this->objectManager->get('TYPO3\CMS\Soap\ServiceWrapper', $serviceObject);
		$serviceWrapper->injectSettings($settings);
		$serviceWrapper->setRequest($request);
		$soapServer->setObject($serviceWrapper);

		$soapServer->handle($request->getBody());
		if ($serviceWrapper->getCatchedException() !== NULL) {
			$this->lastCatchedException = $serviceWrapper->getCatchedException();
			throw $serviceWrapper->getCatchedException();
		}

		$this->lastOperationResult = $serviceWrapper->getLastOperationResult();
	}

	/**
	 * Checks if the request handler can handle the current request.
	 *
	 * @return boolean TRUE if it can handle the request, otherwise FALSE
	 */
	public function canHandleRequest() {
		$requestHeader = $this->getRequestHeader();
		if (!extension_loaded('soap')) {
			return FALSE;
		}
		if ($requestHeader->getMethod() !== 'POST') {
			return FALSE;
		}
		if (!$requestHeader->getHeaders()->has('Soapaction')) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * getRequestHeader
	 *
	 * @return \TYPO3\CMS\Soap\Http\RequestHeader
	 */
	protected function getRequestHeader() {
		$requestHeader = \TYPO3\CMS\Soap\Http\RequestHeader::createFromEnvironment();
		return $requestHeader;
	}

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request.
	 *
	 * @return integer The priority of the request handler
	 */
	public function getPriority() {
		return 200;
	}

	/**
	 * Get the result of the last operation
	 *
	 * Could be used in functional tests.
	 *
	 * @return mixed
	 */
	public function getLastOperationResult() {
		return $this->lastOperationResult;
	}

	/**
	 * Get the last catched exception
	 *
	 * Could be used in functional tests.
	 *
	 * @return \Exception
	 */
	public function getLastCatchedException() {
		return $this->lastCatchedException;
	}

	/**
	 * getRequest
	 *
	 * @return \TYPO3\Soap\Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Override the HTTP request
	 *
	 * @param \TYPO3\CMS\Extbase\Http\Request $httpRequest
	 * @return void
	 */
	public function setHttpRequest($httpRequest) {
		$this->httpRequest = $httpRequest;
	}

}
?>