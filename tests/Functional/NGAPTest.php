<?php


namespace App\Tests\Functional;


use App\Entity\Disease;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


class NGAPTest extends ApiTestCase
{


    public function testSearchData()
    {
        $data = $this->get("ngaps", ['search' => "Cons"]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals("Consultation", $data['hydra:member'][0]['description']);
        $this->assertEquals("C", $data['hydra:member'][0]['code']);

        $this->assertCount(1, $data['hydra:member']);
    }


    public function testGetData()
    {
        $data = $this->get("ngaps/C");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Consultation", $data['description']);
    }

}
