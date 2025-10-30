<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use app\controller\autenticacao\LoginController;

final class AutenticacaoLoginControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/autenticacao/login');

        self::assertResponseIsSuccessful();
    }
}
