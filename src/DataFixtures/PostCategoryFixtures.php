<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\PostCategory;

class PostCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        for ($i = 1; $i <= 50; $i++) {

            $post = new PostCategory();
            $post->setName("Post category name " . $i);
            $post->setModifiedOn(new \DateTime("now"));

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
