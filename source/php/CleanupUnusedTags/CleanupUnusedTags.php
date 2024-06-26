<?php

namespace EventManager\CleanupUnusedTags;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\DeleteTerm;
use EventManager\Services\WPService\GetTerms;

class CleanupUnusedTags implements Hookable
{
    public function __construct(private string $taxonomy, private GetTerms&DeleteTerm&AddAction $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('save_post', [$this, 'cleanupUnusedTags']);
    }

    public function cleanupUnusedTags(): void
    {
        $terms = $this->wpService->getTerms([$this->taxonomy, 'hide_empty' => false]);

        foreach ($terms as $term) {
            if ($term->count === 0) {
                $this->wpService->deleteTerm($term->term_id, $this->taxonomy);
            }
        }
    }
}
