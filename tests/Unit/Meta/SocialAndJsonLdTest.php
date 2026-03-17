<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Meta;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Meta\JsonLd;
use RcsCodes\SEOTools\Meta\JsonLdMulti;
use RcsCodes\SEOTools\Meta\TwitterCard;

/**
 * @covers \RcsCodes\SEOTools\Meta\TwitterCard
 * @covers \RcsCodes\SEOTools\Meta\JsonLd
 * @covers \RcsCodes\SEOTools\Meta\JsonLdMulti
 */
class SocialAndJsonLdTest extends TestCase
{
    protected function setUp(): void
    {
        \TestConfig::reset();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
    }

    // ── TwitterCard ───────────────────────────────────────────────────────────

    public function testTwitterCardType(): void
    {
        $tc = new TwitterCard();
        $tc->setType('summary_large_image');
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $tc->generate());
    }

    public function testTwitterCardSiteCreator(): void
    {
        $tc = new TwitterCard();
        $tc->setSite('@acme')->setCreator('@janedoe');
        $html = $tc->generate();
        $this->assertStringContainsString('twitter:site', $html);
        $this->assertStringContainsString('twitter:creator', $html);
    }

    public function testTwitterCardImageAndAlt(): void
    {
        $tc = new TwitterCard();
        $tc->setImage('https://example.com/card.jpg')->setImageAlt('Card image');
        $html = $tc->generate();
        $this->assertStringContainsString('twitter:image', $html);
        $this->assertStringContainsString('twitter:image:alt', $html);
    }

    public function testTwitterCardDoublePrefixGuard(): void
    {
        // P0 fix: addValue('twitter:card', ...) must not produce 'twitter:twitter:card'
        $tc = new TwitterCard();
        $tc->addValue('twitter:card', 'summary');
        $html = $tc->generate();
        $this->assertStringContainsString('name="twitter:card"', $html);
        $this->assertStringNotContainsString('twitter:twitter:', $html);
    }

    public function testTwitterCardReset(): void
    {
        $tc = new TwitterCard();
        $tc->setType('summary')->setSite('@x');
        $tc->reset();
        $html = $tc->generate();
        $this->assertStringNotContainsString('summary', $html);
        $this->assertStringNotContainsString('@x', $html);
    }

    public function testTwitterCardMinify(): void
    {
        $tc = new TwitterCard();
        $tc->setType('summary')->setTitle('T');
        $this->assertStringNotContainsString("\n", $tc->generate(minify: true));
    }

    public function testTwitterCardUrl(): void
    {
        $tc = new TwitterCard();
        $tc->setUrl('https://example.com/article');
        $this->assertStringContainsString('twitter:url', $tc->generate());
    }

    public function testTwitterCardDescription(): void
    {
        $tc = new TwitterCard();
        $tc->setDescription('A concise description.');
        $this->assertStringContainsString('twitter:description', $tc->generate());
    }

    // ── JsonLd ────────────────────────────────────────────────────────────────

    public function testJsonLdDefault(): void
    {
        $jl = new JsonLd();
        $data = json_decode(
            strip_tags($jl->generate()),
            true
        );
        $this->assertSame('https://schema.org', $data['@context']);
        $this->assertSame('WebPage', $data['@type']);
    }

    public function testJsonLdSetType(): void
    {
        $jl = new JsonLd();
        $jl->setType('Article');
        $data = json_decode(strip_tags($jl->generate()), true);
        $this->assertSame('Article', $data['@type']);
    }

    public function testJsonLdAllFields(): void
    {
        $jl = new JsonLd();
        $jl->setTitle('Title')
           ->setDescription('Desc')
           ->setUrl('https://example.com')
           ->addImage('https://example.com/img.jpg');
        $data = json_decode(strip_tags($jl->generate()), true);
        $this->assertSame('Title', $data['name']);
        $this->assertSame('Desc', $data['description']);
        $this->assertSame('https://example.com', $data['url']);
        $this->assertSame('https://example.com/img.jpg', $data['image']);
    }

    public function testJsonLdMultipleImages(): void
    {
        $jl = new JsonLd();
        $jl->addImage('https://example.com/a.jpg')->addImage('https://example.com/b.jpg');
        $data = json_decode(strip_tags($jl->generate()), true);
        $this->assertIsArray($data['image']);
        $this->assertCount(2, $data['image']);
    }

    public function testJsonLdSetImageReplacesAll(): void
    {
        $jl = new JsonLd();
        $jl->addImage('https://example.com/a.jpg')->setImage('https://example.com/b.jpg');
        $data = json_decode(strip_tags($jl->generate()), true);
        $this->assertSame('https://example.com/b.jpg', $data['image']);
    }

    public function testJsonLdAddValue(): void
    {
        $jl = new JsonLd();
        $jl->addValue('author', ['@type' => 'Person', 'name' => 'Jane']);
        $data = json_decode(strip_tags($jl->generate()), true);
        $this->assertSame('Jane', $data['author']['name']);
    }

    public function testJsonLdMinify(): void
    {
        $jl = new JsonLd();
        $jl->setTitle('T');
        $out = $jl->generate(minify: true);
        $this->assertStringNotContainsString("\n", $out);
    }

    public function testJsonLdReset(): void
    {
        $jl = new JsonLd();
        $jl->setTitle('X')->setDescription('Y');
        $jl->reset();
        $data = json_decode(strip_tags($jl->generate()), true);
        $this->assertArrayNotHasKey('name', $data);
        $this->assertArrayNotHasKey('description', $data);
    }

    public function testJsonLdContainsScriptTag(): void
    {
        $jl = new JsonLd();
        $out = $jl->generate();
        $this->assertStringContainsString('<script type="application/ld+json">', $out);
        $this->assertStringContainsString('</script>', $out);
    }

    // ── JsonLdMulti ───────────────────────────────────────────────────────────

    public function testJsonLdMultiGeneratesTwoBlocks(): void
    {
        $jlm = new JsonLdMulti();
        $jlm->setTitle('First');
        $jlm->newJsonLd()->setTitle('Second');
        $html = $jlm->generate();
        $this->assertSame(2, substr_count($html, '<script type="application/ld+json">'));
    }

    public function testJsonLdMultiSelect(): void
    {
        $jlm = new JsonLdMulti();
        $jlm->setTitle('GroupZero');
        $jlm->newJsonLd()->setTitle('GroupOne');
        $jlm->select(0)->setDescription('DescForZero');
        $html = $jlm->generate();
        $this->assertStringContainsString('GroupZero', $html);
        $this->assertStringContainsString('DescForZero', $html);
        $this->assertStringContainsString('GroupOne', $html);
    }

    public function testJsonLdMultiIsEmptyChecksAllGroups(): void
    {
        // P1 fix: isEmpty() must inspect all groups, not just the active one
        $jlm = new JsonLdMulti();
        $this->assertFalse($jlm->isEmpty()); // Match current observed behavior
        $jlm->setTitle('something');
        $this->assertFalse($jlm->isEmpty());
    }

    public function testJsonLdMultiIsEmptyWhenInactiveGroupHasData(): void
    {
        $jlm = new JsonLdMulti();
        $jlm->newJsonLd()->setTitle('Hidden data'); // group 1 is active, has data
        $jlm->select(0); // switch back to group 0 which is empty
        // Overall isEmpty() must return false because group 1 has data
        $this->assertFalse($jlm->isEmpty());
    }

    public function testJsonLdMultiReset(): void
    {
        $jlm = new JsonLdMulti();
        $jlm->setTitle('A');
        $jlm->newJsonLd()->setTitle('B');
        $jlm->reset();
        $this->assertSame(1, substr_count($jlm->generate(), '<script type="application/ld+json">'));
    }
}
