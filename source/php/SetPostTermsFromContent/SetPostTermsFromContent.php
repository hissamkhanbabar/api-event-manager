<?php

namespace EventManager\SetPostTermsFromContent;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;
use EventManager\TagReader\TagReaderInterface;

class SetPostTermsFromContent implements Hookable
{
    private TagReaderInterface $tagReader;
    private WPService $wpService;
    private string $postType;
    private string $taxonomy;

    public function __construct(string $postType, string $taxonomy, TagReaderInterface $tagReader, WPService $wpService)
    {
        $this->tagReader = $tagReader;
        $this->wpService = $wpService;
        $this->postType  = $postType;
        $this->taxonomy  = $taxonomy;
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('save_post', [$this, 'setPostTermsFromContent']);
    }

    public function setPostTermsFromContent(int $postId): void
    {
        $post = $this->wpService->getPost($postId);

        if ($post->post_type !== $this->postType) {
            return;
        }

        $filterTag = 'EventManager\SetPostTermsFromContent\PostContent';
        $content   = $this->wpService->applyFilters($filterTag, $postId, $post->post_content);
        $tagNames  = $this->tagReader->getTags($content);

        $this->ensureFoundTagsExist($tagNames);

        $this->wpService->setPostTerms($postId, $tagNames, $this->taxonomy, false);
    }

    private function ensureFoundTagsExist(array $tagNames): void
    {
        foreach ($tagNames as $tagName) {
            // If the tag does not exist, create it
            if (!$this->wpService->termExists($tagName, $this->taxonomy)) {
                $this->wpService->insertTerm($tagName, $this->taxonomy);
            }
        }
    }
}
