<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Recipe;

class LoadRecipeData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $receipts = [
            1 => [
                'title' => 'cake jambon & olives',
                'slug' => 'cake-jambon-et-olives',
                'time' => '20',
                'picture' => 'cake.jpg',
                'ingredients' => [
                    '3 oeufs',
                    '150g de farine',
                    '10cl d\'huile',
                    '12,5cl de lait',
                    '100g de gruyère rapé',
                    '200g de jambon',
                    '75g d\'olives vertes',
                    '1 pincée de sel et 2 de poivre',
                    '1 sachet de levure',
                ],
                'steps' => [
                    'Découper le jambon et les olives en morceaux.',
                    'Dans un saladier, travailler au fouet la farine, la levure, le sel, le poivre et les oeufs.',
                    'Incorporer l\'huile et le lait, mélanger, puis ajouter jambon, olives et gruyère.',
                    'Verser dans un moule non graissé.',
                    'Mettre au four pendant 45min à 180° (thermostat 6).',
                ],
                'category' => $this->getReference('category_2'),
                'user' => $this->getReference('user_1'),
            ],
            2 => [
                'title' => 'crêpes à la bière',
                'slug' => 'crepes-a-la-biere',
                'time' => '15',
                'picture' => 'crepes.jpg',
                'ingredients' => [
                    '250g de farine',
                    '30g de sucre',
                    '25cl d\'eau',
                    '25cl de bière',
                    '2 oeufs',
                    '50g de beurre',
                ],
                'steps' => [
                    'Faire fondre le beurre.',
                    'Mélanger la farine, le sucre et l\'eau.',
                    'Ajouter les oeufs et le beurre fondu.',
                    'Travailler le tout. Ajouter la bière. Bien mélanger.',
                    'Laisser reposer environ 1h.',
                    'Avant cuisson, remélanger, et suivant la consistance de la pâte, rectifier avec un peu d\'eau si besoin.',
                ],
                'category' => $this->getReference('category_3'),
                'user' => $this->getReference('user_1'),
            ],
        ];

        $i = 1;
        foreach ($receipts as $recipe_data) {
            $recipe = new Recipe();
            $recipe->setTitle($recipe_data['title']);
            $recipe->setSlug($recipe_data['slug']);
            $recipe->setTime($recipe_data['time']);
            $recipe->setPicture($recipe_data['picture']);
            $recipe->setIngredients($recipe_data['ingredients']);
            $recipe->setSteps($recipe_data['steps']);
            $recipe->setCategory($recipe_data['category']);
            $recipe->setUser($recipe_data['user']);

            $manager->persist($recipe);

            $this->setReference('recipe_' . $i, $recipe);
            $i++;
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array('AppBundle\DataFixtures\ORM\LoadUserData');
    }
}