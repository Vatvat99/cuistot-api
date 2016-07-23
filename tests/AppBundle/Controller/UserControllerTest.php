<?php

namespace Tests\AppBundle\Controller;

use PHPUnit_Framework_TestCase;
use Tests\AppBundle\ApiTestCase;

class UserControllerTest extends ApiTestCase {

    /**
     * Teste la récupération d'une liste d'utilisateurs
     */
    public function testList()
    {
        // On interroge le client Http pour récupérer une liste d'utilisateurs
        $response = $this->client->get('/app_test.php/users');

        // On vérifie la réponse
        $received_data = json_decode($response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('users', $received_data);
        $this->assertCount(2, $received_data['users']);
        $this->assertEquals('aurelien.vattant@gmail.com', $received_data['users'][0]['email']);
    }

    /**
     * Test la récupération d'un utilisateur
     */
    public function testShow()
    {
        // On interroge le client Http pour récupérer un utilisateur
        $response = $this->client->get('/app_test.php/users/aurelien.vattant@gmail.com');

        // On vérifie la réponse
        $received_data = json_decode($response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'email',
            'password'
        ], array_keys($received_data));
        $this->assertEquals('aurelien.vattant@gmail.com', $received_data['email']);
    }

    /**
     * Test la création d'un nouvel utilisateur
     */
    public function testNew()
    {

        // Préparation des données que l'on va passer au client
        $data = array(
            'email' => 'test@test.fr',
            'plainPassword' => 'plop',
        );

        // On interroge le client Http pour créer un utilisateur
        $response = $this->client->post('/app_test.php/users', [
            'body' => json_encode($data)
        ]);

        // On vérifie la réponse
        $received_data = json_decode($response->getBody(), true);
        $this->assertEquals(201, $response->getStatusCode());
        $location = $response->getHeader('Location');
        $this->assertEquals('/app_test.php/users/test@test.fr', $location[0]);
        $this->assertArrayHasKey('email', $received_data);
        $this->assertEquals('test@test.fr', $received_data['email']);

    }

    /**
     * Teste la modification d'un utilisateur
     */
    public function testUpdate()
    {
        // 1 ) Méthode PUT
        // Préparation des données que l'on va passer au client
        $data = array(
            'email' => 'test@test.fr',
            'plainPassword' => 'plop2',
            'picture' => null,
        );

        // On interroge le client Http pour modifier l'utilisateur
        $response = $this->client->put('/app_test.php/users/test@test.fr', [
            'body' => json_encode($data)
        ]);

        // On vérifie la réponse
        $received_data = json_decode($response->getBody(), true);
        $this->assertEquals('$2y$13$O7GObymodAIv4Z5NBpzv8eASp3sNLdM1mHBtEl3KjwMV60I77n3CO', $received_data['password']);

        // 1 ) Méthode PATCH
        // Préparation des données que l'on va passer au client
        $data = array(
            'email' => 'test@test.fr',
            'plainPassword' => 'plop3',
            'picture' => null,
        );

        // On interroge le client Http pour modifier l'utilisateur
        $response = $this->client->patch('/app_test.php/users/test@test.fr', [
            'body' => json_encode($data)
        ]);

        // On vérifie la réponse
        $received_data = json_decode($response->getBody(), true);
        $this->assertEquals('$2y$13$ke0borsYeplQcoiDIcCRXuGBxLxTsRuYXqBvRJVAdmqj4CU7Wvn8u', $received_data['password']);
    }

    /**
     * Teste la suppression d'un utilisateur
     */
    public function testDelete()
    {
        // On interroge le client Http pour supprimer un utilisateur
        $response = $this->client->delete('/app_test.php/users/test@test.fr');

        // On vérifie la réponse
        $this->assertEquals(204, $response->getStatusCode());
    }

}