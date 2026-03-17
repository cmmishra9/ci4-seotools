<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Schema;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Schema\AbstractSchema;
use RcsCodes\SEOTools\Schema\SchemaGraph;
use RcsCodes\SEOTools\Schema\Types\Article;
use RcsCodes\SEOTools\Schema\Types\BreadcrumbList;
use RcsCodes\SEOTools\Schema\Types\Course;
use RcsCodes\SEOTools\Schema\Types\Event;
use RcsCodes\SEOTools\Schema\Types\FAQPage;
use RcsCodes\SEOTools\Schema\Types\HowTo;
use RcsCodes\SEOTools\Schema\Types\JobPosting;
use RcsCodes\SEOTools\Schema\Types\LocalBusiness;
use RcsCodes\SEOTools\Schema\Types\NewsArticle;
use RcsCodes\SEOTools\Schema\Types\Offer;
use RcsCodes\SEOTools\Schema\Types\Organization;
use RcsCodes\SEOTools\Schema\Types\Product;
use RcsCodes\SEOTools\Schema\Types\Recipe;
use RcsCodes\SEOTools\Schema\Types\Review;
use RcsCodes\SEOTools\Schema\Types\SoftwareApplication;
use RcsCodes\SEOTools\Schema\Types\VideoObject;

/**
 * @covers \RcsCodes\SEOTools\Schema\AbstractSchema
 * @covers \RcsCodes\SEOTools\Schema\SchemaGraph
 * @covers \RcsCodes\SEOTools\Schema\Types\Article
 * @covers \RcsCodes\SEOTools\Schema\Types\BreadcrumbList
 * @covers \RcsCodes\SEOTools\Schema\Types\Course
 * @covers \RcsCodes\SEOTools\Schema\Types\Event
 * @covers \RcsCodes\SEOTools\Schema\Types\FAQPage
 * @covers \RcsCodes\SEOTools\Schema\Types\HowTo
 * @covers \RcsCodes\SEOTools\Schema\Types\JobPosting
 * @covers \RcsCodes\SEOTools\Schema\Types\NewsArticle
 * @covers \RcsCodes\SEOTools\Schema\Types\Organization
 * @covers \RcsCodes\SEOTools\Schema\Types\Product
 * @covers \RcsCodes\SEOTools\Schema\Types\Recipe
 * @covers \RcsCodes\SEOTools\Schema\Types\Review
 * @covers \RcsCodes\SEOTools\Schema\Types\SoftwareApplication
 * @covers \RcsCodes\SEOTools\Schema\Types\VideoObject
 */
class SchemaTypesTest extends TestCase
{
    // ── AbstractSchema base ───────────────────────────────────────────────────

    public function testToArrayHasContextAndType(): void
    {
        $art = new Article();
        $arr = $art->toArray();
        $this->assertSame('https://schema.org', $arr['@context']);
        $this->assertSame('Article', $arr['@type']);
    }

    public function testToEmbeddedArrayHasNoContext(): void
    {
        $arr = (new Article())->toEmbeddedArray();
        $this->assertArrayNotHasKey('@context', $arr);
        $this->assertArrayHasKey('@type', $arr);
    }

    public function testSetId(): void
    {
        $art = new Article();
        $art->setId('https://example.com/#article');
        $this->assertSame('https://example.com/#article', $art->toArray()['@id']);
    }

    public function testMagicSet(): void
    {
        $art = new Article();
        $art->setCustomField('custom-value');
        $this->assertSame('custom-value', $art->toArray()['customField']);
    }

    public function testMagicSetUnknownMethodThrows(): void
    {
        $this->expectException(\BadMethodCallException::class);
        (new Article())->doSomethingUnexpected('x');
    }

    public function testAppend(): void
    {
        $art = new Article();
        $art->setSameAs('https://twitter.com/x')->setSameAs('https://linkedin.com/x');
        // setSameAs uses append() internally
        $arr = $art->toArray();
        $this->assertIsArray($arr['sameAs']);
        $this->assertCount(2, $arr['sameAs']);
    }

    public function testValidationThrowsInTesting(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/headline/');
        (new Article())->validate(); // missing headline, author, datePublished
    }

    public function testGenerateOutputsScriptTag(): void
    {
        $art = new Article();
        $art->setHeadline('H')->setAuthor('A')->setDatePublished('2024-01-01');
        $out = $art->generate();
        $this->assertStringContainsString('<script type="application/ld+json">', $out);
        $this->assertStringContainsString('</script>', $out);
    }

    public function testNestedSchemaConvertedInToArray(): void
    {
        // P0 fix: nested AbstractSchema instances in arrays must be converted
        $offer = new Offer();
        $offer->setPrice(9.99)->setPriceCurrency('USD');
        $product = new Product();
        $product->setName('Widget')->setDescription('A widget')->setImage('img.jpg')
                ->setOffers($offer);
        $arr = $product->toArray();
        $this->assertIsArray($arr['offers']);
        $this->assertSame('Offer', $arr['offers']['@type']);
        $this->assertArrayNotHasKey('@context', $arr['offers']);
    }

    public function testNestedAbstractSchemaArrayConverted(): void
    {
        // P0 fix: array containing multiple AbstractSchema instances
        $art = new Article();
        $p1  = new Organization();
        $p2  = new Organization();
        $p1->setName('Org A');
        $p2->setName('Org B');
        $art->set('contributor', [$p1, $p2]);
        $arr = $art->toArray();
        $this->assertIsArray($arr['contributor']);
        $this->assertSame('Organization', $arr['contributor'][0]['@type']);
        $this->assertSame('Organization', $arr['contributor'][1]['@type']);
        $this->assertArrayNotHasKey('@context', $arr['contributor'][0]);
    }

    public function testAbstractSchemaReset(): void
    {
        $art = new Article();
        $art->setHeadline('H')->setAuthor('A');
        $art->reset();
        $arr = $art->toArray();
        $this->assertArrayNotHasKey('headline', $arr);
        $this->assertArrayNotHasKey('author', $arr);
    }

    // ── Article ───────────────────────────────────────────────────────────────

    public function testArticleMinimal(): void
    {
        $art = new Article();
        $art->setHeadline('Breaking News')->setAuthor('Jane')->setDatePublished('2024-06-01');
        $data = json_decode(strip_tags($art->generate()), true);
        $this->assertSame('Breaking News', $data['headline']);
        $this->assertSame('Jane', $data['author']);
    }

    public function testArticleKeywordsArray(): void
    {
        $art = new Article();
        $art->setKeywords(['php', 'seo']);
        $this->assertSame('php, seo', $art->toArray()['keywords']);
    }

    // ── Product + Offer ───────────────────────────────────────────────────────

    public function testProductWithOffer(): void
    {
        $offer = (new Offer())
            ->setPrice(29.99)
            ->setPriceCurrency('GBP')
            ->setAvailability('InStock');
        $product = (new Product())
            ->setName('Widget')
            ->setDescription('A fancy widget')
            ->setImage('https://example.com/img.jpg')
            ->setOffers($offer);
        $data = json_decode(strip_tags($product->generate()), true);
        $this->assertSame('Widget', $data['name']);
        $this->assertSame('Offer', $data['offers']['@type']);
        $this->assertSame('GBP', $data['offers']['priceCurrency']);
        $this->assertStringContainsString('InStock', $data['offers']['availability']);
    }

    public function testProductBrand(): void
    {
        $product = (new Product())->setName('X')->setDescription('Y')->setImage('img.jpg')->setBrand('Acme');
        $arr = $product->toArray();
        $this->assertSame('Brand', $arr['brand']['@type']);
        $this->assertSame('Acme', $arr['brand']['name']);
    }

    // ── BreadcrumbList ────────────────────────────────────────────────────────

    public function testBreadcrumbListItems(): void
    {
        $bc = new BreadcrumbList();
        $bc->addItem('Home', 'https://example.com/')
           ->addItem('Blog', 'https://example.com/blog/')
           ->addItem('Post', 'https://example.com/blog/post');
        $data = json_decode(strip_tags($bc->generate()), true);
        $this->assertCount(3, $data['itemListElement']);
        $this->assertSame(1, $data['itemListElement'][0]['position']);
        $this->assertSame('Blog', $data['itemListElement'][1]['name']);
    }

    public function testBreadcrumbResetClearsItems(): void
    {
        // P0 fix: private $items must be cleared on reset()
        $bc = new BreadcrumbList();
        $bc->addItem('Home', 'https://example.com/');
        $bc->reset();
        $data = json_decode(strip_tags($bc->generate()), true);
        $this->assertEmpty($data['itemListElement']);
    }

    // ── FAQPage ───────────────────────────────────────────────────────────────

    public function testFAQPage(): void
    {
        $faq = new FAQPage();
        $faq->addQuestion('What is PHP?', 'A server-side scripting language.')
            ->addQuestion('What is CI4?', 'CodeIgniter 4 framework.');
        $data = json_decode(strip_tags($faq->generate()), true);
        $this->assertCount(2, $data['mainEntity']);
        $this->assertSame('Question', $data['mainEntity'][0]['@type']);
    }

    public function testFAQPageResetClearsQuestions(): void
    {
        // P0 fix: private $questions must be cleared on reset()
        $faq = new FAQPage();
        $faq->addQuestion('Q?', 'A.');
        $faq->reset();
        $data = json_decode(strip_tags($faq->generate()), true);
        $this->assertEmpty($data['mainEntity']);
    }

    // ── HowTo ─────────────────────────────────────────────────────────────────

    public function testHowTo(): void
    {
        $how = new HowTo();
        $how->setName('How to bake')->setDescription('Simple guide')
            ->addStep('Mix', 'Mix the ingredients.')
            ->addStep('Bake', 'Bake at 180°C for 30 min.');
        $data = json_decode(strip_tags($how->generate()), true);
        $this->assertCount(2, $data['step']);
    }

    public function testHowToResetClearsSteps(): void
    {
        // P0 fix: private $steps must be cleared on reset()
        $how = new HowTo();
        $how->setName('H')->setDescription('D')->addStep('S', 'T');
        $how->reset();
        $arr = $how->toArray();
        $this->assertEmpty($arr['step']);
    }

    // ── Event ─────────────────────────────────────────────────────────────────

    public function testEventWithLocation(): void
    {
        $event = new Event();
        $event->setName('PHP Conference')
              ->setStartDate('2025-09-01T09:00:00')
              ->setLocation('ExCeL London');
        $data = json_decode(strip_tags($event->generate()), true);
        $this->assertSame('Place', $data['location']['@type']);
        $this->assertSame('ExCeL London', $data['location']['name']);
    }

    // ── Organization ──────────────────────────────────────────────────────────

    public function testOrganizationLogo(): void
    {
        $org = (new Organization())->setName('Acme')->setLogo('https://example.com/logo.png');
        $arr = $org->toArray();
        $this->assertSame('ImageObject', $arr['logo']['@type']);
    }

    // ── LocalBusiness ─────────────────────────────────────────────────────────

    public function testLocalBusinessGeo(): void
    {
        $lb = new LocalBusiness();
        $lb->setName('Café')->setAddress('1 High St')->setGeo(51.5074, -0.1278);
        $arr = $lb->toArray();
        $this->assertSame('GeoCoordinates', $arr['geo']['@type']);
        $this->assertSame(51.5074, $arr['geo']['latitude']);
    }

    // ── JobPosting ────────────────────────────────────────────────────────────

    public function testJobPostingRemote(): void
    {
        $job = new JobPosting();
        $job->setTitle('PHP Dev')
            ->setDescription('Write PHP code.')
            ->setHiringOrganization('Acme', 'https://acme.com')
            ->setDatePosted('2024-06-01')
            ->setJobLocation('London', 'GB')
            ->setRemote();
        $data = json_decode(strip_tags($job->generate()), true);
        $this->assertSame('TELECOMMUTE', $data['jobLocationType']);
    }

    // ── Recipe ────────────────────────────────────────────────────────────────

    public function testRecipe(): void
    {
        $recipe = new Recipe();
        $recipe->setName('Pasta')->setImage('img.jpg')->setAuthor('Chef')
               ->setDatePublished('2024-01-01')->setDescription('Tasty pasta.')
               ->setPrepTime('PT15M')->setCookTime('PT10M')
               ->setRecipeIngredient(['200g pasta', '100g sauce'])
               ->setRecipeYield('2 servings');
        $data = json_decode(strip_tags($recipe->generate()), true);
        $this->assertSame('Person', $data['author']['@type']);
        $this->assertCount(2, $data['recipeIngredient']);
    }

    // ── VideoObject ───────────────────────────────────────────────────────────

    public function testVideoObject(): void
    {
        $video = new VideoObject();
        $video->setName('Demo')->setDescription('Watch this.')
              ->setThumbnailUrl('https://example.com/thumb.jpg')
              ->setUploadDate('2024-01-01')
              ->setDuration('PT4M30S')
              ->setContentUrl('https://example.com/video.mp4');
        $data = json_decode(strip_tags($video->generate()), true);
        $this->assertSame('PT4M30S', $data['duration']);
    }

    // ── Course (new) ──────────────────────────────────────────────────────────

    public function testCourse(): void
    {
        $course = new Course();
        $course->setName('CI4 Mastery')
               ->setDescription('Master CodeIgniter 4.')
               ->setProvider('Acme Academy', 'https://acme.com')
               ->setEducationalLevel('Intermediate')
               ->setTimeRequired('PT6H');
        $data = json_decode(strip_tags($course->generate()), true);
        $this->assertSame('Course', $data['@type']);
        $this->assertSame('Organization', $data['provider']['@type']);
        $this->assertSame('Intermediate', $data['educationalLevel']);
    }

    public function testCourseFreeOffer(): void
    {
        $course = (new Course())->setName('X')->setDescription('Y')->setProvider('Z');
        $course->setCoursePrice('Free');
        $arr = $course->toArray();
        $this->assertSame('0', $arr['offers']['price']);
        $this->assertSame('Free', $arr['offers']['category']);
    }

    public function testCourseValidationRequiresProvider(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/provider/');
        (new Course())->setName('X')->setDescription('Y')->validate();
    }

    // ── Review (new) ──────────────────────────────────────────────────────────

    public function testReview(): void
    {
        $review = new Review();
        $review->setItemReviewed('My PHP Book')
               ->setReviewRating(4.5)
               ->setAuthor('Jane Doe')
               ->setReviewBody('Excellent read.')
               ->setDatePublished('2024-06-01');
        $data = json_decode(strip_tags($review->generate()), true);
        $this->assertSame('Review', $data['@type']);
        $this->assertSame('Rating', $data['reviewRating']['@type']);
        $this->assertSame(4.5, $data['reviewRating']['ratingValue']);
        $this->assertSame('Person', $data['author']['@type']);
    }

    public function testReviewItemReviewedAsArray(): void
    {
        $review = (new Review())
            ->setItemReviewed(['@type' => 'Product', 'name' => 'Widget'])
            ->setReviewRating(5.0)
            ->setAuthor('Bob');
        $arr = $review->toArray();
        $this->assertSame('Product', $arr['itemReviewed']['@type']);
    }

    // ── NewsArticle (new) ─────────────────────────────────────────────────────

    public function testNewsArticle(): void
    {
        $na = new NewsArticle();
        $na->setHeadline('Breaking: PHP 9 Released')
           ->setImage('https://example.com/img.jpg')
           ->setDatePublished('2025-01-01')
           ->setAuthor('Reporter')
           ->setPublisher('The Daily PHP')
           ->setArticleSection('Technology');
        $data = json_decode(strip_tags($na->generate()), true);
        $this->assertSame('NewsArticle', $data['@type']);
        $this->assertSame('NewsMediaOrganization', $data['publisher']['@type']);
        $this->assertSame('Technology', $data['articleSection']);
    }

    // ── SoftwareApplication (new) ─────────────────────────────────────────────

    public function testSoftwareApplication(): void
    {
        $app = new SoftwareApplication();
        $app->setName('SEOTools')
            ->setOperatingSystem('Web')
            ->setApplicationCategory('UtilitiesApplication')
            ->setSoftwareVersion('1.0.0')
            ->setOffers('Free')
            ->setAggregateRating(4.8, 250);
        $data = json_decode(strip_tags($app->generate()), true);
        $this->assertSame('SoftwareApplication', $data['@type']);
        $this->assertSame('1.0.0', $data['softwareVersion']);
        $this->assertSame('0', $data['offers']['price']);
        $this->assertSame(4.8, $data['aggregateRating']['ratingValue']);
    }

    // ── SchemaGraph ───────────────────────────────────────────────────────────

    public function testSchemaGraphContainsAllTypes(): void
    {
        $art = (new Article())->setHeadline('H')->setAuthor('A')->setDatePublished('2024-01-01');
        $bc  = (new BreadcrumbList())->addItem('Home', 'https://example.com/');
        $org = (new Organization())->setName('Acme');

        $graph = new SchemaGraph();
        $graph->add($art)->add($bc)->add($org);

        $data = json_decode(strip_tags($graph->generate()), true);
        $this->assertArrayHasKey('@graph', $data);
        $this->assertCount(3, $data['@graph']);

        $types = array_column($data['@graph'], '@type');
        $this->assertContains('Article', $types);
        $this->assertContains('BreadcrumbList', $types);
        $this->assertContains('Organization', $types);
    }

    public function testSchemaGraphEmbeddedItemsHaveNoContext(): void
    {
        $graph = (new SchemaGraph())
            ->add((new Article())->setHeadline('H')->setAuthor('A')->setDatePublished('2024-01-01'));
        $data = json_decode(strip_tags($graph->generate()), true);
        $this->assertArrayNotHasKey('@context', $data['@graph'][0]);
    }

    public function testSchemaGraphRemove(): void
    {
        $graph = new SchemaGraph();
        $graph->add(new Article())->add(new Organization());
        $graph->remove(0);
        $this->assertSame(1, $graph->count());
    }

    public function testSchemaGraphReset(): void
    {
        $graph = new SchemaGraph();
        $graph->add(new Article());
        $graph->reset();
        $this->assertTrue($graph->isEmpty());
    }

    // ── JSON_THROW_ON_ERROR via generate() ───────────────────────────────────

    public function testGenerateProducesValidJson(): void
    {
        $art = (new Article())->setHeadline('H')->setAuthor('A')->setDatePublished('2024-01-01');
        $json = strip_tags($art->generate());
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
    }
}
