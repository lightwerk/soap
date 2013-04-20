<?php
namespace TYPO3\CMS\Soap\Tests\Unit;

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
 * Unit test for RequestHandler
 */
class RequestHandlerTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * Check if the PHP soap extension was loaded
	 */
	protected function setUp() {
		parent::setUp();

		if (!extension_loaded('soap')) {
			$this->markTestSkipped('Test does not run without SOAP extension');
		}
	}

	/**
	 * @test
	 */
	public function canHandleHandlesPostRequestWithSoapactionHeader() {
		$server = array(
			'HTTP_SOAPACTION' => 'Foo'
		);
		$httpRequest = \TYPO3\CMS\Soap\Http\RequestHeader::create(new \TYPO3\CMS\Extbase\Http\Uri('http://request-host/service/soap/test'), 'POST', array(), array(), $server);

		$requestHandler = $this->getMock('\TYPO3\CMS\Soap\RequestHandler', array('getRequestHeader'));
		$requestHandler->expects($this->once())->method('getRequestHeader')->will($this->returnValue($httpRequest));

		$result = $requestHandler->canHandleRequest();

		$this->assertSame(TRUE, $result);
	}

}
?>
