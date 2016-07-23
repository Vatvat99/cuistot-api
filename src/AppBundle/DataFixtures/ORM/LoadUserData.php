<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;

class LoadUserData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $users = [
            1 => [
                'email' => 'aurelien.vattant@gmail.com',
                'password' => '$2y$10$L9uK2r/s8ULt2YqZ4zYSwe9aaMoZGwzP0/wec.e.cRp5hR44UP6Ju', // Aurélien
                'picture' => null,
            ],
            2 => [
                'email' => 'george.raymont@gmail.com',
                'password' => '$2y$13$m8bzDyya3qYx/JxwhaNWpuC83vtOOaY.VEFnhd2a3ypQTyzTQuzLm', // George
                'picture' => null,
            ],
        ];

        $i = 1;
        foreach ($users as $user_data) {
            $user = new User();
            $user->setEmail($user_data['email']);
            $user->setPassword($user_data['password']);

            $manager->persist($user);

            $this->setReference('user_' . $i, $user);
            $i++;
        }

        $manager->flush();
    }
    /*
    public function getOrder()
    {
        return 1;
    }
    */
}