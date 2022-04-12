<?php


namespace App\Tests\Functional;


use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


/**
 * Class RPPSTest
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
    public function testGetRppsData()
    {

        $data = $this->get("rpps");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'],"firstName",["Bastien","Julien","Emilie","Jérémie"]);
        $this->assertCollectionKeyContains($data['hydra:member'],"lastName",["TEST"]);

    }


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchRppsData()
    {

        $data = $this->get("rpps",[
            'search' => "Bastien"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'],"firstName",["Bastien"]);
        $this->assertCollectionKeyNotContains($data['hydra:member'],"firstName",["Julien","Emilie","Jérémie"]);
        $this->assertCollectionKeyContains($data['hydra:member'],"lastName",["TEST"]);

        $this->assertCount(1,$data['hydra:member']);
    }





    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithDemoTrueReturnsRppsDemoData()
    {

        $data = $this->get("rpps",[
            'demo' => true
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'],"firstName",["Emilie"]);
        $this->assertCollectionKeyNotContains($data['hydra:member'],"firstName",["Julien","Jérémie"]);

    }



    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithDemoFalseDoesNotReturnRppsDemoData()
    {

        $data = $this->get("rpps",[
            'demo' => false
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'],"firstName",["Julien","Jérémie"]);
        $this->assertCollectionKeyNotContains($data['hydra:member'],"firstName",["Emilie"]);

    }



    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetOneRppsData()
    {

        $data = $this->get("rpps/111111111111");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Bastien",$data['firstName']);
        $this->assertEquals("TEST",$data['lastName']);

    }

}
