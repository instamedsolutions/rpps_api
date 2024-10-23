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
     * @group
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
            ["Bastien", "Emilie", "Jérémie"]
        );
        $this->assertCollectionKeyContains($data['hydra:member'], "lastName", ["TEST"]);

        // Removing Paramedical
        $this->assertCollectionKeyNotContains($data['hydra:member'], "firstName", ["Achile","Julien"]);
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

        // Search by first name - partial match
        $data = $this->get("rpps", [
            'search' => "Bas"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCollectionKeyContains($data['hydra:member'], "firstName", ["Bastien"]);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "firstName", ["Julien", "Emilie", "Jérémie"]);
        $this->assertCollectionKeyContains($data['hydra:member'], "lastName", ["TEST"]);

        $this->assertCollectionKeyContains($data['hydra:member'], "canonical", ["fixture-canonical-0"]);

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
    public function testWithDemoTrueReturnsRppsDemoData() : void
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
    public function testWithDemoFalseDoesNotReturnRppsDemoData() : void
    {
        $data = $this->get("rpps", [
            'demo' => false,
            'include_paramedical' => true
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

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchByRppsNumber(): void
    {
        $data = $this->get("rpps", [
            'search' => "12222222222"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(1, $data['hydra:member']);

        $rpps = $data['hydra:member'][0];
        $this->assertEquals("Jérémie", $rpps['firstName']);
        $this->assertEquals("TEST", $rpps['lastName']);
        $this->assertEquals("12222222222", $rpps['idRpps']);


        //Check partial search not working on idRpps
        $data = $this->get("rpps", [
            'search' => "1222222222"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $data['hydra:member']);
    }

    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testExcludedRppsFilter(): void
    {
        //Exclude single syntax
        $data = $this->get("rpps", ['excluded_rpps' => '12222222222']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "idRpps", ["12222222222"]);

        // Exclude multiple syntax
        $excludedRpps = ["12222222222", "13333333333"];
        $data = $this->get("rpps", ['excluded_rpps' => $excludedRpps]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyNotContains($data['hydra:member'], "idRpps", $excludedRpps);
    }

}
