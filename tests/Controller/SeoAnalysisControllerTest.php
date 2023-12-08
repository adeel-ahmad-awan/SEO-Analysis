<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * SeoAnalysisControllerTest
 */
class SeoAnalysisControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testPreviewFormSubmissionOnValidUrl()
    {
        $client = static::createClient();

        $client->request('GET', '/preview');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Submit the form
        $crawler = $client->submitForm('Generate Preview', [
            'preview[url]' => 'https://www.google.com/',
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(
            0,
            $crawler->filter('.preview-form-url-image')->count()
        );
    }

    /**
     * @return void
     */
    public function testPreviewFormSubmissionOnInvalidUrl()
    {
        $client = static::createClient();

        $client->request('GET', '/preview');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Submit the form with an invalid URL
        $crawler = $client->submitForm('Generate Preview', [
            'preview[url]' => 'invalid url',
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check if the error flash message is displayed
        $this->assertGreaterThan(
            0,
            $crawler->filter('.form-error-message')->count()
        );
    }


    /**
     * @return void
     */
    public function testApiAnalyzeEndpointOnValidUrl()
    {
        $client = static::createClient();

        // Submit a POST request
        $client->request('POST', '/api/analyze', ['url' => 'https://www.google.com']);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('Url', $responseData);
        $this->assertArrayHasKey('title', $responseData);
        $this->assertArrayHasKey('description', $responseData);
        $this->assertArrayHasKey('issues', $responseData);
        $this->assertArrayHasKey('meta tags', $responseData);
    }

    /**
     * @return void
     */
    public function testApiAnalyzeEndpointOnMissingUrl()
    {
        $client = static::createClient();
        $client->request('POST', '/api/analyze');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('URL is required.', $responseData['error']);
    }

    /**
     * @return void
     */
    public function testApiAnalyzeEndpointOnInvalidUrl()
    {
        $client = static::createClient();

        // Submit a POST request
        $client->request('POST', '/api/analyze', ['url' => 'invalid url']);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid page url provided, please provide a valid url', $responseData['error']);
    }
}
