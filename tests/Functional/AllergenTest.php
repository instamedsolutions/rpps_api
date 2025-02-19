<?php

namespace App\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AllergenTest extends ApiTestCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchData() : void
    {
        $data = $this->get('allergens', ['search' => 'Chymopapa']);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals('Chymopapaïne', $data['hydra:member'][0]['name']);
        $this->assertEquals('c209', $data['hydra:member'][0]['code']);
        $this->assertEquals('Médicaments', $data['hydra:member'][0]['group']);

        $this->assertCount(1, $data['hydra:member']);

        $data = $this->get('allergens', ['search' => 'Chymopapa'],false,[
            'Accept-Language' => 'en'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals('Chymopapain', $data['hydra:member'][0]['name']);
        $this->assertEquals('c209', $data['hydra:member'][0]['code']);
        $this->assertEquals('Drugs', $data['hydra:member'][0]['group']);

        $this->assertCount(1, $data['hydra:member']);


        $data = $this->get('allergens', ['search' => 'Chymopapaïne'],false,[
            'Accept-Language' => 'en'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0,$data['hydra:member']);

        $data = $this->get('allergens', ['search' => 'Chymopapain'],false,[
            'Accept-Language' => 'en'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals('Chymopapain', $data['hydra:member'][0]['name']);
        $this->assertEquals('c209', $data['hydra:member'][0]['code']);
        $this->assertEquals('Drugs', $data['hydra:member'][0]['group']);

        $this->assertCount(1, $data['hydra:member']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetData() : void
    {
        $data = $this->get('allergens/c209');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals('Chymopapaïne', $data['name']);
    }
}
