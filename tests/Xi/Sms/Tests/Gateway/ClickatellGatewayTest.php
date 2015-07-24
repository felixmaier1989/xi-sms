<?php

namespace Xi\Sms\Tests\Gateway;

use Xi\Sms\SmsMessage;
use Xi\Sms\SmsService;
use Xi\Sms\SmsException;
use Xi\Sms\Gateway\ClickatellGateway;
use Buzz\Message\Response;

class ClickatellGatewayTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function parseResponse6()
	{
		$response = ClickatellGateway::parseResponse("OK: CE07B3BFEFF35F4E2667B3A47116FDD2");
		$this->assertEquals('CE07B3BFEFF35F4E2667B3A47116FDD2', $response['OK']);
	}

	/**
	 * @test
	 */
	public function authenticate2()
	{
		$gateway = new ClickatellGateway('lussavain', 'lussuta', 'tussia');

		$browser = $this->getMockBuilder('Buzz\Browser')
			->disableOriginalConstructor()
			->getMock();

		$gateway->setClient($browser);

		$browser
			->expects($this->once())
			->method('get')
			->with(
				$this->callback(function($actual) {
						$url = parse_url($actual);
						parse_str($url['query'], $query);
						return
							$url['path'] === '/http/auth' &&
							$query['api_id'] === 'lussavain' &&
							$query['user'] === 'lussuta' &&
							$query['password'] === 'tussia';
					}),
				array()
			)
			->will($this->returnValue(''));

		$this->setExpectedException('Xi\Sms\RuntimeException');
		$ret = $gateway->authenticate();
	}

	/**
	 * @test
	 */
	public function authenticate1()
	{
		$gateway = new ClickatellGateway('lussavain', 'lussuta', 'tussia', 'http://api.dr-kobros.com');

		$browser = $this->getMockBuilder('Buzz\Browser')
			->disableOriginalConstructor()
			->getMock();

		$gateway->setClient($browser);

		$browser
			->expects($this->once())
			->method('get')
			->with(
				$this->callback(function($actual) {
					$url = parse_url($actual);
					parse_str($url['query'], $query);
					return
						$url['path'] === '/http/auth' &&
						$query['api_id'] === 'lussavain' &&
						$query['user'] === 'lussuta' &&
						$query['password'] === 'tussia';
				}),
				array()
			)
			->will($this->returnValue('OK: QWERTYUI12345678'));

		$ret = $gateway->authenticate();
		$this->assertEquals('QWERTYUI12345678', $ret);
	}

	/**
	 * @test
	 */
	public function sendMultiple2()
	{
		$gateway = new ClickatellGateway('lussavain', 'lussuta', 'tussia', 'http://api.dr-kobros.com');

		$browser = $this->getMockBuilder('Buzz\Browser')
			->disableOriginalConstructor()
			->getMock();

		$gateway->setClient($browser);

		$addressees = array();
		for ($i = 0; $i < 345; $i++) {
			$addressees[] = rand();
		}

		$browser
			->expects($this->exactly(4))
			->method('get')
			->with(
				$this->callback(function($actual) {
						$url = parse_url($actual);
						parse_str($url['query'], $query);
						return count(explode(',', $query['to'])) === 100 ||
							count(explode(',', $query['to'])) === 45;
					}),
				$this->isType('array')
			)
			->will($this->returnValue("ID: QWERTYUI12345678 To: 358503028030\nID: 12345678QWERTYUI To: 49123456789"));

		$message = new \Xi\Sms\SmsMessage(
			'Pekkis tassa lussuttaa.',
			'358503028030',
			$addressees
		);
		$ret = $gateway->send($message);
	}

	/**
	 * @test
	 */
	public function sendMultiple1()
	{
		$gateway = new ClickatellGateway('lussavain', 'lussuta', 'tussia', 'http://api.dr-kobros.com');

		$browser = $this->getMockBuilder('Buzz\Browser')
			->disableOriginalConstructor()
			->getMock();

		$gateway->setClient($browser);

		$browser
			->expects($this->once())
			->method('get')
			->with(
				$this->callback(function($actual) {
					$url = parse_url($actual);
					parse_str($url['query'], $query);
					return $query['to'] === '358503028030,49123456789';
				}),
				$this->isType('array')
			)
			->will($this->returnValue("ID: QWERTYUI12345678 To: 358503028030\nID: 12345678QWERTYUI To: 49123456789"));

		$message = new \Xi\Sms\SmsMessage(
			'Pekkis tassa lussuttaa.',
			'358503028030',
			array('358503028030', '49123456789')
		);
		$ret = $gateway->send($message);
	}

	/**
	 * @test
	 */
	public function parseResponse5()
	{
		$response = ClickatellGateway::parseResponse("ERR: 114, Cannot route message To: 49123456789\nERR: 567, Bla bla bla To: 4987654321");
		$this->assertEquals('114, Cannot route message', $response['ERR']['49123456789']);
		$this->assertEquals('567, Bla bla bla', $response['ERR']['4987654321']);
	}

	/**
	 * @test
	 */
	public function parseResponse4()
	{
		$response = ClickatellGateway::parseResponse("ID: CE07B3BFEFF35F4E2667B3A47116FDD2 To: 49123456789\nID: QWERTYUIO123456789ASDFGHJK To: 4987654321");
		$this->assertEquals('CE07B3BFEFF35F4E2667B3A47116FDD2', $response['ID']['49123456789']);
		$this->assertEquals('QWERTYUIO123456789ASDFGHJK', $response['ID']['4987654321']);
	}

	/**
	 * @test
	 */
	public function parseResponse3()
	{
		$this->setExpectedException('Xi\Sms\RuntimeException');
		$response = ClickatellGateway::parseResponse('foo bar');
	}

	/**
	 * @test
	 */
	public function parseResponse2()
	{
		$response = ClickatellGateway::parseResponse('ID: CE07B3BFEFF35F4E2667B3A47116FDD2');
		$this->assertEquals('CE07B3BFEFF35F4E2667B3A47116FDD2', $response['ID']);
	}

	/**
	 * @test
	 */
	public function parseResponse1()
	{
		$response = ClickatellGateway::parseResponse('ERR: 114, Cannot route message');
		$this->assertEquals('114, Cannot route message', $response['ERR']);
	}

    /**
     * @test
     */
    public function sendsRequest()
    {
        $gateway = new ClickatellGateway('lussavain', 'lussuta', 'tussia', 'http://api.dr-kobros.com');

        $browser = $this->getMockBuilder('Buzz\Browser')
            ->disableOriginalConstructor()
            ->getMock();

        $gateway->setClient($browser);

        $browser
            ->expects($this->once())
            ->method('get')
            ->with(
				$this->callback(function($actual) {
					$url = parse_url($actual);
					parse_str($url['query'], $query);
					return
						$url['scheme'] === 'http' &&
						$url['host'] === 'api.dr-kobros.com' &&
						$url['path'] === '/http/sendmsg' &&
						$query['api_id'] === 'lussavain' &&
						$query['user'] === 'lussuta' &&
						$query['password'] === 'tussia' &&
						$query['to'] === '358503028030' &&
						urldecode($query['text']) === 'Pekkis tassa lussuttaa.' &&
						$query['from'] === '358503028030';
				}),
                array()
            )
			->will($this->returnValue('ID: QWERTYUI12345678'));

        $message = new \Xi\Sms\SmsMessage(
            'Pekkis tassa lussuttaa.',
            '358503028030',
            '358503028030'
        );

        $ret = $gateway->send($message);
        $this->assertEquals('QWERTYUI12345678', $ret);
    }
}
