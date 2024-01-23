<?php

namespace EventManager\PostToSchema;

use EventManager\Helper\Arrayable;
use EventManager\Services\WPService\WPService;
use Spatie\SchemaOrg\BaseType;
use WP_Post;

class PostToEventSchema implements Arrayable
{
    protected WPService $wp;
    protected BaseType $event;
    protected WP_Post $post;
    private bool $allowRecurse;

    public function __construct(
        WPService $wp,
        WP_Post $post,
        $allowRecurse = true
    ) {
        $this->wp           = $wp;
        $this->post         = $post;
        $this->allowRecurse = $allowRecurse;
        $this->event        = new \Spatie\SchemaOrg\Event();
        $this->addPropertiesToEvent();
    }

    private function addPropertiesToEvent()
    {
        $this->setIdentifier();
        $this->setName();
        $this->setDescription();
        $this->setAbout();
        $this->setImage();
        $this->setIsAccessibleForFree();
        $this->setOffers();
        $this->setLocation();
        $this->setUrl();
        $this->setAudience();
        $this->setTypicalAgeRange();
        $this->setOrganizer();

        $this->setStartDate();
        $this->setPreviousStartDate();
        $this->setEndDate();
        $this->setDuration();

        if ($this->allowRecurse) {
            $this->setSuperEvent();
            $this->setSubEvents();
        }
    }

    public function toArray(): array
    {
        return $this->event->toArray();
    }

    private function setIdentifier(): void
    {
        $this->event->identifier($this->post->ID);
    }

    private function setName(): void
    {
        $this->event->name($this->post->post_title);
    }

    private function setDescription(): void
    {
        $this->event->description($this->wp->getPostMeta($this->post->ID, 'description', true) ?: null);
    }

    private function setAbout(): void
    {
        $this->event->about($this->wp->getPostMeta($this->post->ID, 'about', true) ?: null);
    }

    private function setImage(): void
    {
        $this->event->image($this->wp->getThePostThumbnailUrl($this->post->ID) ?: null);
    }

    private function setUrl(): void
    {
        $this->event->url($this->wp->getPermalink($this->post->ID));
    }

    private function setIsAccessibleForFree(): void
    {
        $this->event->isAccessibleForFree((bool)$this->wp->getPostMeta($this->post->ID, 'isAccessibleForFree', true));
    }

    private function setLocation(): void
    {
        $locationMeta = $this->wp->getPostMeta($this->post->ID, 'location', true) ?: null;

        if (!$locationMeta) {
            return;
        }

        // Address
        $address      = new \Spatie\SchemaOrg\PostalAddress();
        $streetName   = $locationMeta['street_name'] ?? '';
        $streetNumber = $locationMeta['street_number'] ?? '';
        $address->streetAddress("{$streetName} {$streetNumber}");
        $address->addressLocality($locationMeta['city'] ?? null);
        $address->postalCode($locationMeta['post_code'] ?? null);
        $address->addressCountry($locationMeta['country_short'] ?? null);

        // Location
        $location = new \Spatie\SchemaOrg\Place();
        $location->address($address);
        $location->longitude($locationMeta['lng'] ?? null);
        $location->latitude($locationMeta['lat'] ?? null);

        $this->event->location($location);
    }

    private function setDuration(): void
    {
        $startDate = $this->event->getProperty('startDate');
        $endDate   = $this->event->getProperty('endDate');
        $duration  = null;

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate   = new \DateTime($endDate);

            $duration = $startDate->diff($endDate)->format('P%yY%mM%dDT%hH%iM%sS');
        }

        $this->event->duration($duration);
    }

    private function setOrganizer(): void
    {
        $organizationId = $this->wp->getPostMeta($this->post->ID, 'organizer', true) ?: null;

        if (!$organizationId || !is_numeric($organizationId)) {
            return;
        }

        $organization = new \Spatie\SchemaOrg\Organization();
        $organization->identifier((int)$organizationId);
        $organization->name(get_the_title($organizationId));
        $organization->url($this->wp->getPostMeta($organizationId, 'url', true) ?: null);
        $organization->email($this->wp->getPostMeta($organizationId, 'email', true) ?: null);
        $organization->telephone($this->wp->getPostMeta($organizationId, 'telephone', true) ?: null);

        $this->event->organizer($organization);
    }

    private function setOffers(): void
    {
        if ($this->event->getProperty('isAccessibleForFree') === true) {
            return;
        }

        $offers      = [];
        $nbrOfOffers = $this->wp->getPostMeta($this->post->ID, 'offers', true) ?: 0;

        for ($i = 0; $i < $nbrOfOffers; $i++) {
            $offer = new \Spatie\SchemaOrg\Offer();
            $offer->name($this->wp->getPostMeta($this->post->ID, "offers_{$i}_name", true) ?: null);
            $offer->url($this->wp->getPostMeta($this->post->ID, "offers_{$i}_url", true) ?: null);
            $offer->price($this->wp->getPostMeta($this->post->ID, "offers_{$i}_price", true) ?: null);

            if ($offer->getProperty('price') !== null) {
                $offer->priceCurrency("SEK");
            }

            $offers[] = $offer;
        }

        $this->event->offers($offers);
    }

    private function setAudience(): void
    {
        $audienceId = $this->wp->getPostMeta($this->post->ID, 'audience', true) ?: null;

        if (!$audienceId) {
            return;
        }

        // Get audience term
        $audienceTerm = $this->wp->getTerm($audienceId);
        $audience     = new \Spatie\SchemaOrg\Audience();
        $audience->identifier((int)$audienceTerm->term_id);
        $audience->name($audienceTerm->name);

        $this->event->audience($audience);
    }

    private function setTypicalAgeRange(): void
    {
        $audience = $this->event->getProperty('audience');
        $range    = null;

        if (!$audience || !$audience->getProperty('identifier')) {
            return;
        }

        $termId     = $audience->getProperty('identifier');
        $rangeStart = $this->wp->getTermMeta($termId, 'typicalAgeRangeStart', true) ?: null;
        $rangeEnd   = $this->wp->getTermMeta($termId, 'typicalAgeRangeEnd', true) ?: null;

        if ($rangeStart && $rangeEnd) {
            $range = "{$rangeStart}-{$rangeEnd}";
        } elseif ($rangeStart) {
            $range = "{$rangeStart}-";
        }

        $this->event->typicalAgeRange($range);
    }

    private function setPreviousStartDate(): void
    {
        $eventStatus          = $this->event->getProperty('eventStatus');
        $previousStartDate    = null;
        $startDate            = $this->wp->getPostMeta($this->post->ID, 'startDate', true) ?: null;
        $rescheduledStartDate = $this->wp->getPostMeta($this->post->ID, 'rescheduledStartDate', true) ?: null;


        if ($eventStatus === 'https://schema.org/EventRescheduled') {
            $previousStartDate = $startDate;
        } elseif ($startDate && $rescheduledStartDate) {
            $previousStartDate = $startDate;
        }

        $this->event->previousStartDate($previousStartDate);
    }

    private function setStartDate(): void
    {
        $eventStatus          = $this->event->getProperty('eventStatus');
        $previousStartDate    = $this->wp->getPostMeta($this->post->ID, 'startDate', true) ?: null;
        $rescheduledStartDate = $this->wp->getPostMeta($this->post->ID, 'rescheduledStartDate', true) ?: null;
        $startDate            = null;

        if ($eventStatus === 'https://schema.org/EventRescheduled') {
            $startDate = $rescheduledStartDate;
        } else {
            $startDate = $previousStartDate;
        }

        $this->event->startDate($startDate);
    }

    private function setEndDate(): void
    {
        $this->event->endDate($this->wp->getPostMeta($this->post->ID, 'endDate', true) ?: null);
    }

    private function setSuperEvent(): void
    {
        $superEventPost = $this->wp->getPostParent($this->post->ID);

        if (!$superEventPost) {
            return;
        }

        $superEvent = new self($this->wp, $superEventPost, false);

        $this->event->superEvent($superEvent->toArray());
    }

    private function setSubEvents(): void
    {
        $subEventPosts = $this->wp->getPosts([
            'post_parent' => $this->post->ID,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return;
        }

        $subEvents = array_map(function ($subPost) {
            $subEvent = new self($this->wp, $subPost, false);

            return $subEvent->toArray();
        }, $subEventPosts);

        $this->event->subEvents($subEvents);
    }
}
