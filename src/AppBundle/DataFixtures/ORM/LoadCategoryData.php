<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Category;

class LoadCategoryData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $categories = [
            1 => [
                'title' => 'entrées',
            ],
            2 => [
                'title' => 'plats principaux',
            ],
            3 => [
                'title' => 'desserts',
            ],
        ];

        $i = 1;
        foreach ($categories as $category_data) {
            $category = new Category();
            $category->setTitle($category_data['title']);

            $manager->persist($category);

            $this->setReference('category_' . $i, $category);
            $i++;
        }

        $manager->flush();
    }

    /* public function getOrder()
    {
        return 2;
    } */
}