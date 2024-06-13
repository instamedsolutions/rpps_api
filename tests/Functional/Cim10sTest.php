<?php


namespace App\Tests\Functional;


use App\Entity\Disease;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


/**
 * Class DiseasesTest
 *
 * @package App\Tests\Functional
 */
class Cim10sTest extends ApiTestCase
{


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchData() : void
    {
        $data = $this->get("cim10s", ['search' => "Cholera"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals("A Vibrio cholerae 01, biovar cholerae", $data['hydra:member'][0]['name']);
        $this->assertEquals("A000", $data['hydra:member'][0]['cim']);

        $this->assertEquals("Cholera", $data['hydra:member'][1]['name']);
        $this->assertEquals("A00", $data['hydra:member'][1]['cim']);


        $this->assertCount(2, $data['hydra:member']);


        $data = $this->get("cim10s", ['search' => "Vibrio biovar"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("A Vibrio cholerae 01, biovar cholerae", $data['hydra:member'][0]['name']);
        $this->assertEquals("A000", $data['hydra:member'][0]['cim']);

        $this->assertCount(1, $data['hydra:member']);
    }


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testFilterData() : void
    {
        $data = $this->get("cim10s", ['hierarchyLevel[lte]' => 3]);

        $this->assertCollectionKeyContains($data['hydra:member'], "cim", ['A00']);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "cim", ['A000']);

        $data = $this->get("cim10s", ['hierarchyLevel[gt]' => 3]);

        $this->assertCollectionKeyContains($data['hydra:member'], "cim", ['A000']);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "cim", ['A00']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetData() : void
    {
        $data = $this->get("cim10s/A000");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("A Vibrio cholerae 01, biovar cholerae", $data['name']);

    }

}
