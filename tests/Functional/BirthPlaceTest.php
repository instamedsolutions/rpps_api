<?php

namespace App\Tests\Functional;

use DateTime;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BirthPlaceTest extends ApiTestCase
{
    /**
     * @throws ExceptionInterface
     */
    protected function loadInseeData(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $output = new BufferedOutput();

        // Run the INSEE import command
        $command = $application->find('app:insee:import');
        $command->run(new ArrayInput([]), $output);

        // echo $output->fetch();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetBirthPlaceRequiredParameters(): void
    {
        $this->get('birth_places');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExceptionInterface
     */
    public function testGetBirthPlaceWithNoDate(): void
    {
        $this->loadInseeData();

        $searchQuery = 'spagn';
        $response = $this->get('birth_places', ['search' => $searchQuery]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertArrayHasKey('total_items', $response);
        self::assertGreaterThan(0, $response['total_items']);

        self::assertArrayHasKey('total_pages', $response);
        self::assertGreaterThan(0, $response['total_pages']);

        self::assertArrayHasKey('data', $response);
        self::assertNotEmpty($response['data']);

        // Ensure there is at least one city and one country
        $cityFound = false;
        $countryFound = false;

        foreach ($response['data'] as $place) {
            self::assertArrayHasKey('label', $place);
            self::assertArrayHasKey('code', $place);
            self::assertArrayHasKey('type', $place);

            // Ensure the label contains the search query (case-insensitive)
            self::assertStringContainsStringIgnoringCase(
                $searchQuery,
                $place['label'],
                "Each result should contain '$searchQuery' in the label"
            );

            if ('city' === $place['type']) {
                $cityFound = true;
            } elseif ('country' === $place['type']) {
                $countryFound = true;
            }
        }

        // Assert that both a city and a country were found
        self::assertTrue($cityFound, 'Expected at least one city in the results');
        self::assertTrue($countryFound, 'Expected at least one country in the results');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExceptionInterface
     */
    public function testPaginationForBirthPlaces(): void
    {
        $this->loadInseeData();

        // Perform the request with page=2 and limit=50
        $response = $this->get('birth_places', [
            'search' => 'spa',
            'page' => 2,
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Expected only 3 results on page 2
        self::assertArrayHasKey('total_items', $response);
        self::assertArrayHasKey('total_pages', $response);
        self::assertArrayHasKey('data', $response);

        self::assertSame(53, $response['total_items'], 'Total items should be 53');
        self::assertSame(2, $response['total_pages'], 'Total pages should be 2');
        self::assertCount(3, $response['data'], 'Page 2 should contain exactly 3 results');

        // Define expected results
        $expectedResults = [
            [
                'label' => 'Spay',
                'code' => '72344',
                'type' => 'city',
            ],
            [
                'label' => 'Territoires espagnols en Afrique du Nord',
                'code' => '99313',
                'type' => 'country',
            ],
            [
                'label' => 'Villespassans',
                'code' => '34339',
                'type' => 'city',
            ],
        ];

        // Check each expected result is in the response
        foreach ($expectedResults as $expected) {
            self::assertContains($expected, $response['data']);
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExceptionInterface
     */
    public function testBirthPlacesWithDateFilter(): void
    {
        $this->loadInseeData();

        // Test case 1 : Before 1947 → "Indes britanniques"
        $response1 = $this->get('birth_places', [
            'search' => 'Inde',
            'dateOfBirth' => (new DateTime('1945-01-01'))->format(DateTimeInterface::ATOM),
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response1['data']);

        $expected1 = [
            'label' => 'Indes britanniques',
            'code' => '99223',
            'type' => 'country',
        ];
        self::assertContains($expected1, $response1['data']);

        // Test case 2: After 1947
        $response2 = $this->get('birth_places', [
            'search' => 'Inde',
            'dateOfBirth' => (new DateTime('1949-01-01'))->format(DateTimeInterface::ATOM),
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $expected2 = [
            'label' => 'Inde',
            'code' => '99223',
            'type' => 'country',
        ];
        self::assertContains($expected2, $response2['data']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExceptionInterface
     */
    public function testBirthPlacesWithDateFilterForCommunes(): void
    {
        $this->loadInseeData();

        // Test case 1: Before 1956 → "Ars"
        $response1 = $this->get('birth_places', [
            'search' => 'Ars',
            'dateOfBirth' => (new DateTime('1955-01-01'))->format(DateTimeInterface::ATOM),
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response1['data']);

        $expectedCommuneBefore = [
            'label' => 'Ars',
            'code' => '01021',
            'type' => 'city',
        ];
        self::assertContains($expectedCommuneBefore, $response1['data']);

        // Test case 2: After 1956 → "Ars-sur-Formans"
        $response2 = $this->get('birth_places', [
            'search' => 'Ars',
            'dateOfBirth' => (new DateTime('1960-01-01'))->format(DateTimeInterface::ATOM),
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response2['data']);

        $expectedCommuneAfter = [
            'label' => 'Ars-sur-Formans',
            'code' => '01021',
            'type' => 'city',
        ];
        self::assertContains($expectedCommuneAfter, $response2['data']);
    }
}
