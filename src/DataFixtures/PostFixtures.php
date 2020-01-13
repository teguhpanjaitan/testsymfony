<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Post;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        for ($i = 1; $i <= 50; $i++) {
            $post = new Post();
            $post->setSlug("slug-" . $i);
            $post->setTitle("Title " . $i);
            $post->setContent("The content " . $i);

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
