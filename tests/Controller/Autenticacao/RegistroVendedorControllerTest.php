<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class Autenticacao/RegistroVendedorControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/autenticacao/registro/vendedor');

        self::assertResponseIsSuccessful();
    }
}
