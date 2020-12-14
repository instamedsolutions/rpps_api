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
class RPPSTest extends ApiTestCase
{


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchRppsData()
    {

        $data = $this->get("rpps",array('search' => "Bastien"));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Bastien",$data['hydra:member'][0]['firstName']);
        $this->assertEquals("TEST",$data['hydra:member'][0]['lastName']);

        $this->assertCount(1,$data['hydra:member']);

        self::dump($data);

    }

}
