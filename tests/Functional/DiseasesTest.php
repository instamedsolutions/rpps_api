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
class DiseasesTest extends ApiTestCase
{


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchData()
    {

        $data = $this->get("diseases",['search' => "Cholera"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Cholera",$data['hydra:member'][0]['name']);
        $this->assertEquals("A00",$data['hydra:member'][0]['cim']);
        $this->assertEquals("01",$data['hydra:member'][0]['category']['cim']);
        $this->assertEquals("Certaines maladies infectieuses et parasitaires",$data['hydra:member'][0]['category']['name']);
        $this->assertEquals("A00-A09",$data['hydra:member'][0]['group']['cim']);
        $this->assertEquals("Maladies intestinales infectieuses",$data['hydra:member'][0]['group']['name']);

        $this->assertEquals("A Vibrio cholerae 01, biovar cholerae",$data['hydra:member'][1]['name']);
        $this->assertEquals("A000",$data['hydra:member'][1]['cim']);

        $this->assertCount(2,$data['hydra:member']);


        $data = $this->get("diseases",['search' => "Vibrio biovar"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("A Vibrio cholerae 01, biovar cholerae",$data['hydra:member'][0]['name']);
        $this->assertEquals("A000",$data['hydra:member'][0]['cim']);

        $this->assertCount(1,$data['hydra:member']);
    }


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testFilterData()
    {

        $data = $this->get("diseases",['hierarchyLevel[lte]' => 3]);

        $this->assertCollectionKeyContains($data['hydra:member'],"cim",['A00']);
        $this->assertCollectionKeyNotContains($data['hydra:member'],"cim",['A000']);

        $data = $this->get("diseases",['hierarchyLevel[gt]' => 3]);

        $this->assertCollectionKeyContains($data['hydra:member'],"cim",['A000']);
        $this->assertCollectionKeyNotContains($data['hydra:member'],"cim",['A00']);

    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetData()
    {

        $data = $this->get("diseases/A000");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("A Vibrio cholerae 01, biovar cholerae",$data['name']);

        $this->assertEquals("Cholera",$data['parent']['name']);

        $this->assertEquals(Disease::SEX_FEMALE,$data['sex']);

    }

}
