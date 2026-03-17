<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Enterprise;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Enterprise\AiBotManager;
use RcsCodes\SEOTools\Enterprise\EEATMarkup;
use RcsCodes\SEOTools\Enterprise\MultiTenantManager;
use RcsCodes\SEOTools\Enterprise\SpeakableMarkup;

/**
 * @covers \RcsCodes\SEOTools\Enterprise\AiBotManager
 * @covers \RcsCodes\SEOTools\Enterprise\EEATMarkup
 * @covers \RcsCodes\SEOTools\Enterprise\MultiTenantManager
 * @covers \RcsCodes\SEOTools\Enterprise\SpeakableMarkup
 */
class EnterpriseTest extends TestCase
{
    protected function setUp(): void
    {
        \TestConfig::reset();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
        unset($_SERVER['HTTP_HOST']);
    }

    // ── AiBotManager ─────────────────────────────────────────────────────────

    public function testBlockAllAiBotsProducesRobotRules(): void
    {
        $mgr = new AiBotManager();
        $mgr->blockRetrieval()->blockTraining();
        $rules = $mgr->toRobotsTxtRules();
        $this->assertNotEmpty($rules);

        foreach ($rules as $bot => $botRules) {
            $this->assertStringContainsString('Disallow', \implode("\n", $botRules));
        }
    }

    public function testAllowRetrievalOnlyPreset(): void
    {
        $mgr = new AiBotManager();
        $mgr->applyPreset('retrieval');
        $rules = $mgr->toRobotsTxtRules();
        // GPTBot is a retrieval+training bot — in 'retrieval' preset it should be allowed
        $this->assertArrayHasKey('GPTBot', $rules);
        $gptRules = \implode("\n", $rules['GPTBot']);
        $this->assertStringContainsString('Allow', $gptRules);
    }

    public function testRestrictivePresetBlocksAll(): void
    {
        $mgr = new AiBotManager();
        $mgr->applyPreset('restrictive');
        $rules = $mgr->toRobotsTxtRules();

        foreach ($rules as $botRules) {
            $text = \implode("\n", $botRules);
            $this->assertStringContainsString('Disallow', $text);
        }
    }

    public function testMetaRobotsContent(): void
    {
        $mgr = new AiBotManager();
        $mgr->blockRetrieval()->blockTraining();
        $content = $mgr->toMetaContent();
        $this->assertStringContainsString('noindex', $content);
    }

    public function testPerBotOverride(): void
    {
        $mgr = new AiBotManager();
        $mgr->applyPreset('restrictive');
        $mgr->setBot('GPTBot', ['Allow: /public/']);
        $rules = $mgr->toRobotsTxtRules();
        $this->assertContains('Allow: /public/', $rules['GPTBot']);
    }

    public function testApplyHeadersSetsXRobotsTag(): void
    {
        $mockResponse = new class () {
            public array $headers = [];
            public function setHeader(string $k, string $v): static
            {
                $this->headers[$k] = $v;

                return $this;
            }
        };
        $mgr = new AiBotManager();
        $mgr->blockRetrieval();
        $mgr->applyHeaders($mockResponse);
        $this->assertArrayHasKey('X-Robots-Tag', $mockResponse->headers);
    }

    public function testKnownBotConstantsPopulated(): void
    {
        $this->assertNotEmpty(AiBotManager::RETRIEVAL_BOTS);
        $this->assertNotEmpty(AiBotManager::TRAINING_BOTS);
        $this->assertContains('GPTBot', AiBotManager::RETRIEVAL_BOTS);
    }

    // ── EEATMarkup ────────────────────────────────────────────────────────────

    public function testEEATAuthorSchema(): void
    {
        $eeat = new EEATMarkup();
        $eeat->setAuthor('Jane Doe', 'https://jane.com')
            ->setAuthorJobTitle('Senior Engineer')
            ->addAuthorSameAs('https://linkedin.com/in/jane')
            ->addAuthorCredential('PhD Computer Science')
        ;
        $json  = $eeat->generateAuthorSchema();
        $data  = \json_decode(\strip_tags($json), true);
        $this->assertSame('Person', $data['@type']);
        $this->assertSame('Jane Doe', $data['name']);
        $this->assertSame('Senior Engineer', $data['jobTitle']);
        $this->assertContains('https://linkedin.com/in/jane', (array) $data['sameAs']);
    }

    public function testEEATOrganizationSchema(): void
    {
        $eeat = new EEATMarkup();
        $eeat->setOrganization('Acme Corp', 'https://acme.com', 'https://acme.com/logo.png')
            ->addOrganizationSameAs('https://twitter.com/acme')
        ;
        $json = $eeat->generateOrganizationSchema();
        $data = \json_decode(\strip_tags($json), true);
        $this->assertSame('Organization', $data['@type']);
        $this->assertSame('Acme Corp', $data['name']);
    }

    public function testEEATAuthorToArray(): void
    {
        $eeat = new EEATMarkup();
        $eeat->setAuthor('Bob', 'https://bob.com');
        $arr = $eeat->authorToArray();
        $this->assertSame('Person', $arr['@type']);
        $this->assertSame('Bob', $arr['name']);
    }

    public function testEEATOrganizationToArray(): void
    {
        $eeat = new EEATMarkup();
        $eeat->setOrganization('Corp', 'https://corp.com');
        $arr = $eeat->organizationToArray();
        $this->assertSame('Organization', $arr['@type']);
    }

    // ── SpeakableMarkup ───────────────────────────────────────────────────────

    public function testSpeakableCssSelectorSchema(): void
    {
        $sm = new SpeakableMarkup();
        $sm->addCssSelector('.headline')->addCssSelector('.summary')
            ->setUrl('https://example.com/article')
        ;
        $arr = $sm->toArray();
        $this->assertSame('SpeakableSpecification', $arr['@type']);
        $this->assertContains('.headline', $arr['cssSelector']);
        $this->assertContains('.summary', $arr['cssSelector']);
    }

    public function testSpeakableXPath(): void
    {
        $sm = new SpeakableMarkup();
        $sm->addXPath('/html/body/article/h1');
        $arr = $sm->toArray();
        $this->assertContains('/html/body/article/h1', $arr['xpath']);
    }

    public function testSpeakableGenerate(): void
    {
        $sm = new SpeakableMarkup();
        $sm->addCssSelector('.article-body');
        $out = $sm->generate();
        $this->assertStringContainsString('<script type="application/ld+json">', $out);
        $decoded = \json_decode(\strip_tags($out), true);
        $this->assertSame('SpeakableSpecification', $decoded['@type']);
    }

    public function testSpeakableReset(): void
    {
        $sm = new SpeakableMarkup();
        $sm->addCssSelector('.foo');
        $sm->reset();
        $arr = $sm->toArray();
        $this->assertEmpty($arr['cssSelector'] ?? []);
    }

    public function testSpeakableAddCssSelectors(): void
    {
        $sm = new SpeakableMarkup();
        $sm->addCssSelectors(['.a', '.b', '.c']);
        $arr = $sm->toArray();
        $this->assertCount(3, $arr['cssSelector']);
    }

    // ── MultiTenantManager ────────────────────────────────────────────────────

    public function testMultiTenantAppliesMatchingDomain(): void
    {
        \TestConfig::merge(['tenants' => [
            'brand.com' => ['meta' => ['defaults' => ['title' => 'Brand Site']]],
        ]]);
        $_SERVER['HTTP_HOST'] = 'brand.com';
        $mtm = new MultiTenantManager();
        $merged = $mtm->apply(\TestConfig::get());
        $this->assertSame('Brand Site', $merged->meta['defaults']['title']);
    }

    public function testMultiTenantNoMatchLeavesConfigUnchanged(): void
    {
        \TestConfig::merge(['tenants' => [
            'other.com' => ['meta' => ['defaults' => ['title' => 'Other']]],
        ]]);
        $_SERVER['HTTP_HOST'] = 'brand.com';
        $mtm    = new MultiTenantManager();
        $merged = $mtm->apply(\TestConfig::get());
        $this->assertFalse($merged->meta['defaults']['title']);
    }
}
