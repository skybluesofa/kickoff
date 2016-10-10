<?php
namespace Frickelbruder\KickOff\Rules;

use Frickelbruder\KickOff\Rules\Contracts\ConfigurableRuleBase;

class OgPropertyPresent extends ConfigurableRuleBase {

    public $name = 'OgPropertyPresent';

    protected $errorMessage = 'The required open graph property is not present.';

    protected $requiredProperties = array( 'title', 'description', 'url', 'image' );

    protected $configurableField = array( 'requiredProperties' );

    public function validate() {
        $body = $this->httpResponse->getBody();
        $xml = $this->getResponseBodyAsXml( $body );

        return $this->checkRequiredProperties( $xml );
    }

    /**
     * @param \SimpleXMLElement $body
     *
     * @return bool
     */
    private function checkRequiredProperties($body) {
        foreach( $this->requiredProperties as $propertyName ) {
            $propertyItemValue = $body->xpath( '/html/head/meta[@property="og:' . $propertyName . '"]/ @content' );
            if( !is_array( $propertyItemValue ) || empty( $propertyItemValue ) ) {
                $this->errorMessage = 'The open graph property "' . $propertyName . '" was not found on the site.';

                return false;
            }
            $length = mb_strlen( $propertyItemValue[0]['content'], 'UTF-8' );

            if( $length == 0 ) {
                $this->errorMessage = 'The open graph property "' . $propertyName . '" was found but is empty.';

                return false;
            }
        }

        return true;
    }
}
