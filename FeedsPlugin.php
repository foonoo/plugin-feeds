<?php

namespace foonoo\plugins\foonoo\feeds;

use foonoo\events\SiteWriteStarted;
use foonoo\Plugin;
use foonoo\events\ContentReady;
use foonoo\content\SerialContentInterface;

class FeedsPlugin extends Plugin
{
    private $feedItems = [];
    private $siteDetails = [];

    public function getEvents()
    {
        return [
            ContentReady::class => [$this, 'generateFeeds'],
            SiteWriteStarted::class => [$this, 'setSiteDetails']
        ];
    }

    public function setSiteDetails(SiteWriteStarted $event)
    {
        $siteDetails  = $event->getSite()->getMetaData();
        $this->siteDetails = [
            'title' => $siteDetails['name'] ?? '',
            'url' => $siteDetails['url'] ?? '',
            'description' => $siteDetails['description'] ?? '',
            'author' => $siteDetails['author'] ?? ''
        ];
    }

    public function generateFeeds(ContentReady $event)
    {
        foreach($event->getPages() as $page)
        {
            if(!is_a($page, SerialContentInterface::class)) {
                continue;
            }
            $post = $page->getMetaData();
            $post['url'] = $page->getDestination();
            $this->feedItems[] = $post;
        }
        $feedPage = $event->getContentFactory()->create(__DIR__ . "/templates/feed.tplphp", "feed.xml");
        $feedData = $this->siteDetails;
        $feedData['posts'] = $this->feedItems;
        $feedPage->setData($feedData);
        $event->addPage($feedPage);
    }
}
