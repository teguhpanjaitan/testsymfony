<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Post;
use App\Entity\PostCategory;
use Doctrine\ORM\EntityManagerInterface;

class PostFixtures extends Fixture
{
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(PostCategory::class);
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        for ($i = 1; $i <= 50; $i++) {
            $post = new Post();
            $post->setSlug("slug-" . $i);
            $post->setTitle("Title " . $i);
            $post->setContent("The content " . $i);
            $post->setSeoTitle("SEO title " . $i);
            $post->setSeoDescription("SEO Description " . $i);
            $post->setShortDescription("Short description " . $i);

            $category = $this->repository->findOneBy(['name' => 'Post category name 1']);
            $post->setCategory($category);

            if ($i % 2 == 0) {
                $post->setStatus(false);
            } else {
                $post->setStatus(true);
            }

            $manager->persist($post);
        }

        $manager->flush();
    }
}
