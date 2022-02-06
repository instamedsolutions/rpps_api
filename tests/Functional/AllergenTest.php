<?php


namespace App\Tests\Functional;


use App\Entity\Disease;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


/**
 * Class AllergenTest
 *
 * @package App\Tests\Functional
 */
class AllergenTest extends ApiTestCase
{


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchData()
    {

        $data = $this->get("allergens",['search' => "Chymopapa"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals("Chymopapaïne",$data['hydra:member'][0]['name']);
        $this->assertEquals("c209",$data['hydra:member'][0]['code']);
        $this->assertEquals("Médicaments",$data['hydra:member'][0]['group']);

        $this->assertCount(1,$data['hydra:member']);

    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetData()
    {

        $data = $this->get("allergens/c209");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Chymopapaïne",$data['name']);

    }

}
