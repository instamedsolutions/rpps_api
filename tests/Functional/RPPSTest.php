<?php


namespace App\Tests\Functional;


use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


/**
 * @group
 */
class RPPSTest extends ApiTestCase
{

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetRppsData(): void
    {
        $data = $this->get("rpps");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains(
            $data['hydra:member'],
            "firstName",
            ["Bastien", "Julien", "Emilie", "Jérémie"]
        );
        $this->assertCollectionKeyContains($data['hydra:member'], "lastName", ["TEST"]);

        // Removing Infirmier
        $this->assertCollectionKeyNotContains($data['hydra:member'], "firstName", ["Achile"]);
    }


    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchRppsData(): void
    {
        $data = $this->get("rpps", [
            'search' => "Bastien"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'], "firstName", ["Bastien"]);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "firstName", ["Julien", "Emilie", "Jérémie"]);
        $this->assertCollectionKeyContains($data['hydra:member'], "lastName", ["TEST"]);

        // Legacy specialty
        $this->assertCollectionKeyContains($data['hydra:member'], "specialty", ["Médecine Générale"]);

        // Specialty v2
        $specialty = $data['hydra:member'][0]['specialtyEntity'];
        $this->assertEquals("Médecine Générale", $specialty['name']);
        $this->assertEquals("medecine-generale", $specialty['canonical']);
        $this->assertEquals("Médecin généraliste", $specialty['specialistName']);

        // Legacy city
        $this->assertCollectionKeyContains($data['hydra:member'], "city", ["Paris"]);

        // City v2
        $specialty = $data['hydra:member'][0]['cityEntity'];
        $this->assertEquals("Paris", $specialty['name']);
        $this->assertEquals("Paris 04", $specialty['subCityName']);
        $this->assertEquals("75004", $specialty['postalCode']);

        $this->assertCount(1, $data['hydra:member']);
    }


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithDemoTrueReturnsRppsDemoData()
    {
        $data = $this->get("rpps", [
            'demo' => true
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'], "firstName", ["Emilie"]);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "firstName", ["Julien", "Jérémie"]);
    }


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithDemoFalseDoesNotReturnRppsDemoData()
    {
        $data = $this->get("rpps", [
            'demo' => false
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'], "firstName", ["Julien", "Jérémie"]);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "firstName", ["Emilie"]);
    }


    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetOneRppsData()
    {
        $data = $this->get("rpps/11111111111");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals("Bastien", $data['firstName']);
        $this->assertEquals("TEST", $data['lastName']);
    }

}
