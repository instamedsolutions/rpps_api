<?php


namespace App\Tests\Functional;


use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


/**
 * Class DocumentListResourceTest
 *
 * @package App\Tests\Functional
 */
class DrugsTest extends ApiTestCase
{


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchDrugsData()
    {

        $data = $this->get("drugs",['search' => "Paracétamol"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("PARACETAMOL 50,0 mg",$data['hydra:member'][0]['name']);
        $this->assertEquals("68634033",$data['hydra:member'][0]['cisId']);

        $this->assertCount(1,$data['hydra:member']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetDrugsData()
    {

        $data = $this->get("drugs/68634000");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("ADVIL 200 mg",$data['name']);
        $this->assertEquals(['orale'],$data['administrationForms']);

    }

}
