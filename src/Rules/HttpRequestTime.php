<?php
namespace Frickelbruder\KickOff\Rules;

use Frickelbruder\KickOff\Rules\Contracts\HttpHeaderConfigurableRule;

class HttpRequestTime extends HttpHeaderConfigurableRule  {

    public $name = "HttpRequestTime";

    protected $errorMessage = 'This resource took too long to respond.';

    public $maxTransferTime = 1000;

    protected $configurableField = array("maxTransferTime");

    public function validate() {
        $duration = $this->httpResponse->getTransferTime();
        if($duration > $this->maxTransferTime) {
            $this->errorMessage = 'The resources took ' . $duration . ' ms to download. The max. allowed time is ' . $this->maxTransferTime . 'ms.';
            return false;
        }
        return true;
    }



}
