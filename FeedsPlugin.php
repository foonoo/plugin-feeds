<?php

namespace nyansapow\plugins\contrib\feeds;

use nyansapow\events\SiteWriteStarted;
use nyansapow\Plugin;
use nyansapow\events\PagesReady;
use nyansapow\content\SerialContentInterface;

class FeedsPlugin extends Plugin
{
    private $feedItems = [];
    private $siteDetails = [];

    public function getEvents()
    {
        return [
            PagesReady::class => [$this, 'generateFeeds'],
            SiteWriteStarted::class => [$this, 'setSiteDetails']
        ];
    }

    public function setSiteDetails(SiteWriteStarted $event)
    {
        $siteDetails  = $event->getSite()->getMetaData();
        $this->siteDetails =
         [
            'title' => $siteDetails['name'] ?? '',
            'url' => $siteDetails['url'] ?? '',
            'description' => $siteDetails['description'] ?? '',
            'author' => $siteDetails['author'] ?? ''
        ];
    }

    public function generateFeeds(PagesReady $event)
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
        $feedPage = $event->getContentFactory()->create(__DIR__ . "/templates/feed.tpl.php", "feed.xml");
        $feedData = $this->siteDetails;
        $feedData['posts'] = $this->feedItems;
        $feedPage->setData($feedData);
        $feedPage->setLayout(false);
        $event->addPage($feedPage);
    }
}
