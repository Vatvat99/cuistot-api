<?php

namespace Tests\AppBundle;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

class ApiTestCase extends KernelTestCase
{
    private static $staticClient;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Surcharge la fonction setUpBeforeClass de PHPUnit_Framework_TestCase
     * Méthode appelée avant que les tests soient lancés
     */
    public static function setUpBeforeClass()
    {
        // Charge le krenel de symfony pour pouvoir accéder au service container
        self::bootKernel();

        // On vide la bdd et on la remplit avec les fixtures
        $em = self::getService('doctrine')->getManager();
        $purger = new ORMPurger($em);
        $purger->purge();
        $loader = new Loader();
        $loader->loadFromDirectory('./src/AppBundle/DataFixtures');
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());

        // On crée le client http nécessaire pour interroger l'api
        self::$staticClient =  new Client([
            'base_uri' => getenv('TEST_BASE_URL'),
            'defaults' => [
                'exceptions' => false
            ]
        ]);
    }

    /**
     * Surcharge la fonction setUp de PHPUnit_Framework_TestCase
     * Méthode appellée avant l'exécution de chaque test
     */
    protected function setUp()
    {
        $this->client = self::$staticClient;
    }

    /**
     * Surcharge la méthode de KernelTestCase
     * Méthode appellée après l'exécution de chaque test
     */
    protected function tearDown()
    {
        // On n'appelle pas la classe parent pour éviter qu'elle ferme le kernel
    }

    /**
     * Charge un service depuis le service container
     */
    protected static function getService($service)
    {
        return self::$kernel->getContainer()->get($service);
    }

}
