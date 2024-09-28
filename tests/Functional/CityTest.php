<?php

namespace App\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @group
 */
class CityTest extends ApiTestCase
{
    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCityData(): void
    {
        $data = $this->get("cities");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyContains($data['hydra:member'], "name", ["Paris", "Bouligneux", "Bourg-en-Bresse"]);
    }

    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCityById(): void
    {
        $city = $this->getCity("paris-13eme");
        $data = $this->get("cities/" . $city->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Check that the response contains expected keys
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertArrayHasKey('canonical', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('inseeCode', $data);
        $this->assertArrayHasKey('postalCode', $data);
        $this->assertArrayHasKey('latitude', $data);
        $this->assertArrayHasKey('longitude', $data);
        $this->assertArrayHasKey('population', $data);
        $this->assertArrayHasKey('department', $data);

        // Check specific field values
        $this->assertEquals($city->getCanonical(), $data['canonical']);
        $this->assertEquals($city->getName(), $data['name']);
        $this->assertEquals($city->getInseeCode(), $data['inseeCode']);
        $this->assertEquals($city->getPostalCode(), $data['postalCode']);
        $this->assertEquals($city->getLatitude(), $data['latitude']);
        $this->assertEquals($city->getLongitude(), $data['longitude']);
        $this->assertEquals($city->getPopulation(), $data['population']);

        // Check that the department object has the correct structure
        $this->assertArrayHasKey('@type', $data['department']);
        $this->assertArrayHasKey('@id', $data['department']);
        $this->assertArrayHasKey('name', $data['department']);
        $this->assertArrayHasKey('codeDepartment', $data['department']);
        $this->assertArrayHasKey('region', $data['department']);
        $this->assertArrayHasKey('departmentType', $data['department']);

        // Check the values inside the department
        $department = $city->getDepartment();
        $this->assertEquals('Department', $data['department']['@type']);
        $this->assertEquals($department->getName(), $data['department']['name']);
        $this->assertEquals($department->getCodeDepartment(), $data['department']['codeDepartment']);
        $this->assertEquals($department->getDepartmentType()->value, $data['department']['departmentType']);

        // Check the region object within the department
        $this->assertArrayHasKey('@type', $data['department']['region']);
        $this->assertArrayHasKey('@id', $data['department']['region']);
        $this->assertArrayHasKey('name', $data['department']['region']);
        $this->assertArrayHasKey('codeRegion', $data['department']['region']);

        // Check the values inside the region
        $region = $department->getRegion();
        $this->assertEquals('Region', $data['department']['region']['@type']);
        $this->assertEquals($region->getName(), $data['department']['region']['name']);
        $this->assertEquals($region->getCodeRegion(), $data['department']['region']['codeRegion']);

        // Add assertions for mainCity
        $this->assertArrayHasKey('mainCity', $data); // Check that mainCity exists

        // Check specific field values of mainCity
        $mainCity = $city->getMainCity();
        $this->assertEquals('City', $data['mainCity']['@type']);
        $this->assertEquals($mainCity->getName(), $data['mainCity']['name']);
        $this->assertEquals($mainCity->getId(), $data['mainCity']['id']);
    }

    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCityByName(): void
    {
        // Test by exact city name
        $data = $this->get("cities", ["name" => "Bouligneux"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(1, count($data['hydra:member']));

        // Test by partial name search
        $data = $this->get("cities", ["name" => "Bou"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(3, count($data['hydra:member']));
        $this->assertCollectionKeyContains(
            $data['hydra:member'],
            "name",
            ["Bourg-en-Bresse", "Bourg-Saint-Christophe", "Bouligneux"]
        );
    }


    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSubCities(): void
    {
        $mainCityParis = $this->getCity();

        // Fetch the sub_cities endpoint
        $data = $this->get("cities/" . $mainCityParis->getId() . "/sub_cities");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Check that the response contains the expected number of subCities
        $this->assertEquals(20, count($data['hydra:member'][0]['subCities']));

        // Check that each subCity has the minimal fields
        foreach ($data['hydra:member'] as $subCityData) {
            $this->assertArrayHasKey('@id', $subCityData);
            $this->assertArrayHasKey('@type', $subCityData);
            $this->assertArrayHasKey('name', $subCityData);
            $this->assertArrayHasKey('postalCode', $subCityData);
            $this->assertArrayHasKey('id', $subCityData);

            // Optionally, check specific values
            $this->assertEquals('Paris', $subCityData['name']);
        }
    }

    /**
     * 1. Test for City with Latitude and Longitude (e.g., "Bourg-en-Bresse")
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSimilarCities1(): void
    {
        $cityWithCoordinates = $this->getCity('bourg-en-bresse');
        $this->assertNotNull($cityWithCoordinates, 'City with coordinates should exist');
        $this->assertNotNull($cityWithCoordinates->getLongitude());
        $this->assertNotNull($cityWithCoordinates->getLatitude());

        $data = $this->get("cities/" . $cityWithCoordinates->getId() . "/similar");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // By data fixtures only 2 cities match. The other cities are too far away or with too low population.
        $this->assertEquals(2, count($data['hydra:member']));
        $this->assertCollectionKeyContains($data['hydra:member'], "name", ["Bolozon", "Bourg-Saint-Christophe"]);
    }

    /**
     * 2. Test for City without Coordinates but with Sub-city with Coordinates (e.g., "Paris")
     *
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSimilarCities2(): void
    {
        $cityWithoutCoordinates = $this->getCity();
        $this->assertNotNull($cityWithoutCoordinates, 'City without coordinates should exist');
        $this->assertNull($cityWithoutCoordinates->getLongitude());
        $this->assertNull($cityWithoutCoordinates->getLatitude());

        $data = $this->get("cities/" . $cityWithoutCoordinates->getId() . "/similar");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Paris has no coordinates but has sub-cities with coordinates.
        // We have 10 results here, that are 10 arrondissements of Paris. ( similar name - subname is different)
        $this->assertLessThanOrEqual(10, count($data['hydra:member']));
        foreach ($data['hydra:member'] as $similarCityData) {
            $this->assertEquals('Paris', $similarCityData['name']);
        }
    }

    /**
     * 3. Test for City without Coordinates or Sub-city with Coordinates
     *
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSimilarCities3(): void
    {
        $cityWithoutCoordinatesNoSubCity = $this->getCity(
            'boissey'
        ); // Assuming "Boissey" has no coordinates and no sub-city with coordinates
        $this->assertNotNull($cityWithoutCoordinatesNoSubCity, 'City without coordinates or sub-city should exist');
        $this->assertNull($cityWithoutCoordinatesNoSubCity->getLongitude());
        $this->assertNull($cityWithoutCoordinatesNoSubCity->getLatitude());
        $this->assertTrue($cityWithoutCoordinatesNoSubCity->getSubCities()->isEmpty());

        $data = $this->get("cities/" . $cityWithoutCoordinatesNoSubCity->getId() . "/similar");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Validate response: similar cities should be from the same department
        // Only 4 cities match the criteria from the data fixtures
        $this->assertEquals(4, count($data['hydra:member']));
        foreach ($data['hydra:member'] as $similarCityData) {
            // The api does not return all the city data, so we need to fetch in the database
            $city = $this->getCity($similarCityData['canonical']);
            $this->assertEquals(
                $cityWithoutCoordinatesNoSubCity->getDepartment()->getId(),
                $city->getDepartment()->getId(),
            );
        }
    }
}
