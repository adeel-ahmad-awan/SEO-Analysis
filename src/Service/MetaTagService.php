<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Entity\MetaTag;
use App\Repository\MetaTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;

/**
 * MetaTagService
 */
#[AllowDynamicProperties] class MetaTagService
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var MetaTagRepository
     */
    private MetaTagRepository $metaTagRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param MetaTagRepository $metaTagRepository
     */
    public function __construct(EntityManagerInterface $entityManager, MetaTagRepository $metaTagRepository)
    {
        $this->entityManager = $entityManager;
        $this->metaTagRepository = $metaTagRepository;
    }

    /**
     * @param $page
     * @param $pageTags
     * @return void
     */
    public function saveMetaTags($page, $pageTags): void
    {
        foreach ($pageTags as $tagName => $tagContent) {
            $existingMetaTag = $this->entityManager->getRepository(MetaTag::class)
                ->findOneBy(['page' => $page, 'name' => $tagName]);

            if ($existingMetaTag) {
                $existingMetaTag->setContent($tagContent);
            } else {
                $metaTag = new MetaTag();
                $metaTag->setName($tagName);
                $metaTag->setContent($tagContent);
                $metaTag->setPage($page);
                $this->entityManager->persist($metaTag);
            }
        }
        $this->entityManager->flush();
    }


    /**
     * @param $pageId
     * @return array
     */
    public function getMetaTagsByPageId($pageId): array
    {
     return $this->metaTagRepository->findMetaTagsByPageId($pageId);
    }

    /**
     * I created my own function rather than using builtin function get_meta_tags
     * as some websites don't allow this functionality abd throw 403 error
     *
     * @param $html
     * @return array
     */
    function getMetaTags($html): array
    {
        if($html === null) {
            return [];
        }
        $dom = new DOMDocument();
        // Disable warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $metaTags = [];

        foreach ($dom->getElementsByTagName('meta') as $metaTag) {
            $name = $metaTag->getAttribute('name');
            $content = $metaTag->getAttribute('content');

            // If 'name' attribute is not present, try 'property' attribute
            if (empty($name)) {
                $name = $metaTag->getAttribute('property');
            }

            if (!empty($name) && !empty($content)) {
                $metaTags[$name] = $content;
            }
        }
        return $metaTags;
    }

    /**
     * @param $metaTags
     * @return array
     */
    function analyzeMetaTags($metaTags): array
    {
        if (empty($metaTags)) {
            return [];
        }

        $expectedTags = [
            'description' => [
                'description' => 'Provides a short description of the page. This description is often used in the snippet shown in search results.',
                'example' => '<meta name="description" content="Make your business visible online with 55+ tools for SEO, PPC, content, social media, competitive research, and more.">'
            ],
            'robots' => [
                'description' => 'Controls how search engines crawl and index your pages. You can use "index" and "follow" to allow both actions. Or "noindex" and "nofollow" to prevent them. If you don’t specify anything, "index" and "follow" are the default values.',
                'example' => '<meta name="robots" content="index,follow">'
            ],
            'googlebot' => [
                'description' => 'Similar to robots, but specific to Google.',
                'example' => '<meta name="googlebot" content="index,follow">'
            ],
            'google' => [
                'description' => 'Disables some optional Google features for your site, such as the sitelinks search box that allows users to search within your site from Google, and the Google text-to-speech services that enable users to hear your webpages. The supported values are "nositelinkssearchbox" and "nopagereadaloud."',
                'example' => '<meta name="google" content="nositelinkssearchbox">'
            ],
            'google-site-verification' => [
                'description' => 'Verifies the ownership of the website for Google Search Console.',
                'example' => '<meta name="google-site-verification" content="+nxGUDJ4QpAZ5l9Bsjdi102tLVC21AIh5d1Nl23908vVuFHs34=">'
            ],
            'Content-Type and charset' => [
                'description' => 'Specifies the content type and character set for the webpage. This is important for rendering non-ASCII characters correctly.',
                'example' => '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
            ],
            'refresh' => [
                'description' => 'Tells the browser to automatically reload the page after a specified number of seconds. It can also be used to redirect the user to another URL after a certain time.',
                'example' => '<meta http-equiv="refresh" content="5; url=https://website.com">'
            ],
            'rating' => [
                'description' => 'Indicates that a webpage contains explicit content.',
                'example' => '<meta name="rating" content="adult">'
            ],
            'viewport' => [
                'description' => 'Controls how the webpage is displayed on mobile devices.',
                'example' => '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            ],
        ];

        $results = [];
        foreach ($expectedTags as $tagName => $tagInfo) {
            $tagFound = $this->findTag($metaTags, $tagName);

            if (!$tagFound) {
                $results[$tagName] = [
                    'error' => "Missing $tagName meta tag.",
                    'description' => $tagInfo['description'],
                ];
            }
        }
        return $results;
    }

    function findTag(array $metaTags, string $tagName): bool
    {
        if (in_array($tagName, $metaTags, true)) {
            return true;
        }
        return false;
    }
}