<?php

namespace App\EventSubscriber;

use App\Repository\PostRepository;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(UrlGeneratorInterface $urlGenerator, PostRepository $postRepository) {
        $this->urlGenerator = $urlGenerator;
        $this->postRepository = $postRepository;
    }

    public function onPrestaSitemapPopulate(SitemapPopulateEvent $event)
    {
        $posts = $this->postRepository->findAllValidated();
        foreach ($posts as $post) {
            $event->getUrlContainer()->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate(
                        'post_show',
                        ['id' => $post->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'post'
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'presta_sitemap.populate' => 'onPrestaSitemapPopulate',
        ];
    }
}
