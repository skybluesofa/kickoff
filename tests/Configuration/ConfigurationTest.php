<?php
namespace Frickelbruder\KickOff\Tests\Configuration;

use Frickelbruder\KickOff\Configuration\Configuration;
use Frickelbruder\KickOff\Yaml\Yaml;

class ConfigurationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp() {
        $this->configuration = new Configuration( new Yaml());
        $this->configuration->build( __DIR__ . '/files/config.yml' );
    }

    public function testBase() {


        $sections = $this->configuration->getSections();

        $this->assertCount(2, $sections);
        $this->assertArrayHasKey('main', $sections);
        $this->assertArrayHasKey('second', $sections);

    }

    public function testBaseSectionTargetUrlOverride() {
        $sections = $this->configuration->getSections();

        $mainSection = $sections['main'];
        $targetURL = $mainSection->getTargetUrl();

        $expected = 'https://test.host/';

        $this->assertEquals($expected, $targetURL->getUrl());

        $subSection = $sections['second'];
        $targetURL = $subSection->getTargetUrl();

        $expected = 'https://test2.host:8080/';

        $this->assertEquals($expected, $targetURL->getUrl());
    }

    public function testBaseSectionRules() {
        $sections = $this->configuration->getSections();

        $mainSection = $sections['main'];
        $rules = $mainSection->getRules();

        $this->assertCount(3, $rules);

        $this->assertArrayHasKey('HttpHeaderXSSProtection', $rules);
        $this->assertArrayHasKey('HttpHeaderResourceIsGzipped', $rules);
        $this->assertArrayHasKey('HttpRequestTime', $rules);

        $this->assertInstanceOf('\Frickelbruder\KickOff\Rules\HttpHeaderPresent', $rules['HttpHeaderXSSProtection']);
        $this->assertInstanceOf('\Frickelbruder\KickOff\Rules\HttpHeaderResourceIsGzipped', $rules['HttpHeaderResourceIsGzipped']);
        $this->assertInstanceOf('\Frickelbruder\KickOff\Rules\HttpRequestTime', $rules['HttpRequestTime']);

        $secondSection = $sections['second'];
        $rules = $secondSection->getRules();

        $this->assertCount(2, $rules, 'Not matching rules count');

        $this->assertArrayHasKey('HttpHeaderExposeLanguage', $rules);
        $this->assertArrayHasKey('HttpRequestTime', $rules);

        $this->assertInstanceOf('\Frickelbruder\KickOff\Rules\HttpHeaderNotPresent', $rules['HttpHeaderExposeLanguage']);
        $this->assertInstanceOf('\Frickelbruder\KickOff\Rules\HttpRequestTime', $rules['HttpRequestTime']);
    }

    public function testConfigurationOfRule() {
        $sections = $this->configuration->getSections();

        $mainSection = $sections['main'];
        $rules = $mainSection->getRules();

        $configuredRule = $rules['HttpRequestTime'];
        $this->assertEquals(22500, $configuredRule->maxTransferTime);
    }

    public function testConfigurationOfRuleDoesNotAffectSameRuleInOtherSection() {
        $sections = $this->configuration->getSections();

        $mainSection = $sections['main'];
        $rules = $mainSection->getRules();

        $configuredRule = $rules['HttpRequestTime'];
        $this->assertEquals(22500, $configuredRule->maxTransferTime);

        $secondSection = $sections['second'];
        $rules = $secondSection->getRules();

        $configuredRule = $rules['HttpRequestTime'];
        $this->assertEquals(1000, $configuredRule->maxTransferTime);
    }

    public function testConfigurationTargetUrlHasAddedHeaders() {
        $sections = $this->configuration->getSections();

        $mainSection = $sections['main'];
        $targetUrl = $mainSection->getTargetUrl();

        $rules = $mainSection->getRules();
        $this->assertInstanceOf('\Frickelbruder\KickOff\Rules\Contracts\RequiresHeaderInterface', $rules['HttpHeaderResourceIsGzipped']);

        $this->assertArrayHasKey('a', $targetUrl->headers);
        $this->assertArrayHasKey('Accept-Encoding', $targetUrl->headers);
    }


}
