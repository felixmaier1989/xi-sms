<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms\Gateway;

use Xi\Sms\SmsMessage;

class ClickatellGateway extends BaseHttpRequestGateway
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $endpoint;

    public function __construct(
        $apiKey,
        $user,
        $password,
        $endpoint = 'https://api.clickatell.com'
    ) {
        $this->apiKey = $apiKey;
        $this->user = $user;
        $this->password = $password;
        $this->endpoint = $endpoint;
    }

    /**
     * @see GatewayInterface::send
     * @todo Implement a smarter method of sending (batch)
	 * @param SmsMessage $message
	 * @param bool $urlEncoding To ensure backwards compatibility
     */
    public function send(SmsMessage $message, $urlEncoding = false)
    {
        $body = urlencode(utf8_decode($message->getBody()));
        $from = urlencode($message->getFrom());

        foreach ($message->getTo() as $to) {
			$params = array(
				'api_id' => $this->apiKey,
				'user' => $this->user,
				'password' => $this->password,
				'to' => $to,
				'text' => $body,
				'from' => $from
			);
			$query_string = $urlEncoding ?
				"api_id={$this->apiKey}&user={$this->user}" .
				"&password={$this->password}&to={$to}&text={$body}&from={$from}" :
				http_build_query($params);
			$this->getClient()->get(
				$this->endpoint . '/http/sendmsg?'.$query_string,
				array()
			);
        }
        return true;
    }
}
