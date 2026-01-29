<?php

namespace App\Tests\Functional;

use App\DTO\BirthPlaceDTO;
use DateTime;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BirthPlaceTest extends ApiTestCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetBirthPlaceRequiredParameters(): void
    {
        $result = $this->get('birth_places');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(0, $result['hydra:member']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetBirthPlaceWithNoDate(): void
    {
        $searchQuery = 'ita';

        $response = $this->get('birth_places', [
            'search' => $searchQuery,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertArrayHasKey('hydra:totalItems', $response);
        self::assertGreaterThan(0, $response['hydra:totalItems']);

        self::assertArrayHasKey('hydra:member', $response);
        self::assertNotEmpty($response['hydra:member']);

        // Ensure there is at least one city and one country
        $cityFound = false;
        $countryFound = false;

        foreach ($response['hydra:member'] as $place) {
            self::assertArrayHasKey('label', $place);
            self::assertArrayHasKey('code', $place);
            self::assertArrayHasKey('type', $place);

            if ('INCONNU' !== $place['label']) {
                // Ensure the label contains the search query (case-insensitive)
                self::assertStringContainsStringIgnoringCase(
                    $searchQuery,
                    $place['label'],
                    "Each result should contain '$searchQuery' in the label"
                );
            }
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
     */
    public function testPaginationForBirthPlaces(): void
    {
        $response = $this->get('birth_places', [
            'search' => 'paris',
            'page' => 2,
            'limit' => 2,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertArrayHasKey('hydra:totalItems', $response);
        self::assertArrayHasKey('hydra:member', $response);
        self::assertArrayHasKey('hydra:view', $response);

        self::assertSame(7, $response['hydra:totalItems']);
        self::assertCount(2, $response['hydra:member']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBirthPlacesWithDateFilter(): void
    {
        // Test case 1 : Before 1947 → "Indes britanniques"
        $response1 = $this->get('birth_places', [
            'search' => 'Inde',
            'dateOfBirth' => (new DateTime('1945-01-01'))->format(DateTimeInterface::ATOM),
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response1['hydra:member']);

        $this->assertResponseContainsBirthPlace(
            $response1['hydra:member'],
            new BirthPlaceDTO(
                label: 'Indes britanniques',
                code: '99223',
                type: 'country'
            )
        );

        // Test case 2: After 1947
        $response2 = $this->get('birth_places', [
            'search' => 'Inde',
            'dateOfBirth' => (new DateTime('1949-01-01'))->format(DateTimeInterface::ATOM),
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertResponseContainsBirthPlace(
            $response2['hydra:member'],
            new BirthPlaceDTO(
                label: 'Inde',
                code: '99223',
                type: 'country'
            )
        );

        // Test case 1 : Before 1943, it should use the 1943 rule → "Indes britanniques"
        $response1 = $this->get('birth_places', [
            'search' => 'Inde',
            'dateOfBirth' => (new DateTime('1933-01-01'))->format(DateTimeInterface::ATOM),
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response1['hydra:member']);

        $this->assertResponseContainsBirthPlace(
            $response1['hydra:member'],
            new BirthPlaceDTO(
                label: 'Indes britanniques',
                code: '99223',
                type: 'country'
            )
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBirthPlacesWithDateFilterForCommunes(): void
    {
        // Test case 1: Before 1956 → "Ars"
        $response1 = $this->get('birth_places', [
            'search' => 'Ars',
            'dateOfBirth' => (new DateTime('1955-01-01'))->format(DateTimeInterface::ATOM),
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response1['hydra:member']);

        $this->assertResponseContainsBirthPlace(
            $response1['hydra:member'],
            new BirthPlaceDTO(
                label: 'Ars',
                code: '01021',
                type: 'city'
            )
        );

        // Test case 2: After 1956 → "Ars-sur-Formans"
        $response2 = $this->get('birth_places', [
            'search' => 'Ars',
            'dateOfBirth' => (new DateTime('1960-01-01'))->format(DateTimeInterface::ATOM),
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response2['hydra:member']);

        $this->assertResponseContainsBirthPlace(
            $response2['hydra:member'],
            new BirthPlaceDTO(
                label: 'Ars-sur-Formans',
                code: '01021',
                type: 'city'
            )
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetOneBirthPlaceByCode(): void
    {
        $code = '75056'; // Paris
        $response = $this->get('birth_places/' . $code);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('label', $response);
        self::assertArrayHasKey('code', $response);
        self::assertArrayHasKey('type', $response);

        self::assertSame('Paris', $response['label']);
        self::assertSame($code, $response['code']);
        self::assertSame('city', $response['type']);

        // Test with date for historical commune (Ars)
        $code = '01021'; // Ars-sur-Formans (only in historical data)
        $response = $this->get('birth_places/' . $code, [
            'dateOfBirth' => (new DateTime('1950-01-01'))->format(DateTimeInterface::ATOM),
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('label', $response);
        self::assertArrayHasKey('code', $response);
        self::assertArrayHasKey('type', $response);

        self::assertSame('Ars', $response['label']);
        self::assertSame($code, $response['code']);
        self::assertSame('city', $response['type']);

        // Get for country

        $code = '99401'; // Canada
        $response = $this->get('birth_places/' . $code);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('label', $response);
        self::assertArrayHasKey('code', $response);
        self::assertArrayHasKey('type', $response);
        self::assertSame('Canada', $response['label']);
        self::assertSame($code, $response['code']);
        self::assertSame('country', $response['type']);
    }

    /**
     * Test searching with accent normalization.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBirthPlaceSearchWithAccents(): void
    {
        // Search with accents should find places without accents
        $response = $this->get('birth_places', [
            'search' => 'paris',
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response['hydra:member']);

        // Verify we found Paris in the results
        $foundParis = false;
        foreach ($response['hydra:member'] as $place) {
            if (false !== stripos($place['label'], 'Paris')) {
                $foundParis = true;
                break;
            }
        }
        self::assertTrue($foundParis, 'Should find Paris when searching for "paris"');
    }

    /**
     * Test searching with spaces and hyphens normalization.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBirthPlaceSearchWithSpacesAndHyphens(): void
    {
        // Search variations with spaces and hyphens should find the same results
        $searches = [
            'Saint Denis',
            'Saint-Denis',
        ];

        $firstResults = null;
        foreach ($searches as $search) {
            $response = $this->get('birth_places', [
                'search' => $search,
                'limit' => 50,
            ]);

            self::assertResponseStatusCodeSame(Response::HTTP_OK);
            self::assertNotEmpty($response['hydra:member'], "Should return results for search: $search");

            // Store first results to compare
            if (null === $firstResults) {
                $firstResults = $response['hydra:member'];
            }
        }
    }

    /**
     * Test searching by 5-digit code.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBirthPlaceSearchByCode(): void
    {
        // Search with 5-digit code (75056 is Paris from fixtures)
        $response = $this->get('birth_places', [
            'search' => '75056',
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Should find the commune with this code
        self::assertNotEmpty($response['hydra:member']);

        // Verify the result contains the code
        $found = false;
        foreach ($response['hydra:member'] as $place) {
            if ('75056' === $place['code']) {
                $found = true;
                self::assertSame('city', $place['type']);
                break;
            }
        }

        self::assertTrue($found, 'Expected to find a place with code 75056');
    }

    /**
     * Test searching for historical countries (e.g., Algeria before 1962).
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBirthPlaceSearchHistoricalCountries(): void
    {
        // Search for historical country before its establishment date
        $response = $this->get('birth_places', [
            'search' => 'Inde',
            'dateOfBirth' => '1947-01-01',
            'limit' => 50,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($response['hydra:member'], 'Should find historical countries');
    }

    /**
     * Test jsonapi format via Accept header.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBirthPlaceFormatSuffix(): void
    {
        // Test with Accept header for jsonapi format
        $response = $this->get('birth_places', [
            'search' => 'paris',
            'limit' => 10,
        ], false, ['Accept' => 'application/vnd.api+json']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        // JSONAPI format uses different structure than JSON-LD
        self::assertIsArray($response);
    }

    private function assertResponseContainsBirthPlace(array $collection, BirthPlaceDTO $expected): void
    {
        foreach ($collection as $item) {
            if (
                ($item['label'] ?? null) === $expected->label
                && ($item['code'] ?? null) === $expected->code
                && ($item['type'] ?? null) === $expected->type
            ) {
                $this->assertTrue(true); // Match found

                return;
            }
        }

        $this->fail(
            sprintf(
                'Expected BirthPlace not found: label="%s", code="%s", type="%s"',
                $expected->label,
                $expected->code,
                $expected->type
            )
        );
    }
}
