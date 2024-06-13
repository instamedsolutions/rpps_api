<?php


namespace App\Tests\Functional;


use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


class Cim11sTest extends ApiTestCase
{


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchData(): void
    {

        $data = $this->get("cim11s", ['search' => "cancer du sein"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Autres tumeurs malignes du sein", $data['hydra:member'][0]['name']);
        $this->assertEquals("C50.9", $data['hydra:member'][0]['cim10Code']);
        $this->assertEquals("2C6Y", $data['hydra:member'][0]['code']);

        $this->assertCount(1, $data['hydra:member']);


        $data = $this->get("cim11s", ['search' => "tumeurs malignes du sein"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Autres tumeurs malignes du sein", $data['hydra:member'][0]['name']);
        $this->assertEquals("C50.9", $data['hydra:member'][0]['cim10Code']);
        $this->assertEquals("2C6Y", $data['hydra:member'][0]['code']);

        $this->assertCount(1, $data['hydra:member']);
    }


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testFilterData(): void
    {
        $data = $this->get("cim11s", ['ids' => "2C6Y"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(1, $data['hydra:member']);

        $this->assertCollectionKeyContains($data['hydra:member'], "code", ['2C6Y']);


        $data = $this->get("cim11s", ['ids' => ["2C6Y"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(1, $data['hydra:member']);

        $this->assertCollectionKeyContains($data['hydra:member'], "code", ['2C6Y']);

    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetData(): void
    {
        $data = $this->get("cim11s/CA00-0");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("CA00.0", $data['code']);

    }

}
