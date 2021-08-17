<?php


namespace App\Tests\Functional;


use App\Entity\Disease;
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
class CCAMTest extends ApiTestCase
{


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchData()
    {

        $data = $this->get("ccams",['search' => "Électromyographie"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Électromyographie par électrode de surface, sans enregistrement vidéo",$data['hydra:member'][0]['name']);
        $this->assertEquals("AHQP001",$data['hydra:member'][0]['code']);
        $this->assertEquals("01.01",$data['hydra:member'][0]['group']['code']);
        $this->assertEquals("Actes diagnostiques sur le système nerveux",$data['hydra:member'][0]['group']['name']);

        $this->assertCount(1,$data['hydra:member']);


        $data = $this->get("ccams",['search' => "électrode"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(2,$data['hydra:member']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetData()
    {

        $data = $this->get("ccams/AHQB026");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Pyrographie de 3 à 6 muscles striés au repos et à l'effort avec stimulodétection, par électrode aiguille",$data['name']);

        $this->assertEquals(86.4,$data['rate1']);
        $this->assertEquals(86.4,$data['rate2']);
        $this->assertEquals("ATM",$data['regroupementCode']);

    }

}
