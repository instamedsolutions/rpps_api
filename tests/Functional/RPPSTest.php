<?php

namespace App\Tests\Functional;

use App\DataFixtures\LoadRPPS;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
        $this->get('rpps');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $this->get('rpps');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Vérifie la structure Hydra de base
        $this->assertArrayHasKey('hydra:member', $data);
        $this->assertIsArray($data['hydra:member']);
        $this->assertArrayHasKey('hydra:totalItems', $data);
        $this->assertIsInt($data['hydra:totalItems']);
        $this->assertGreaterThanOrEqual(1, $data['hydra:totalItems']);

        // Nous testons la forme des éléments (pas les valeurs exactes).
        $this->assertGreaterThanOrEqual(1, count($data['hydra:member']));

        // Échantillonne 1 élément (le premier) pour valider la forme
        $item = $data['hydra:member'][0];

        // Clés essentielles de l'item
        $this->assertArrayHasKeys($item, [
            '@id',
            '@type',
            'id',
            'canonical',
            'idRpps',
            'lastName',
            'firstName',
            'title',
            'phoneNumber',
            'email',
            'specialty',
            'specialtyEntity',
            'addresses',
            // Legacy flatten
            'address',
            'addressExtension',
            'zipcode',
            'city',
            'latitude',
            'longitude',
            'coordinates',
        ]);

        // Types basiques
        $this->assertIsString($item['idRpps']);
        $this->assertIsString($item['canonical']);
        $this->assertIsString($item['lastName']);
        $this->assertIsString($item['firstName']);
        $this->assertIsArray($item['specialtyEntity']);
        $this->assertArrayHasKey('name', $item['specialtyEntity']);
        $this->assertArrayHasKey('canonical', $item['specialtyEntity']);
        $this->assertIsArray($item['addresses']);

        // Si des adresses existent, vérifie la forme d'une adresse
        if (!empty($item['addresses'])) {
            $addr = $item['addresses'][0];
            $this->assertArrayHasKeys($addr, [
                '@id',
                '@type',
                'id',
                'address',
                'zipcode',
                'originalAddress',
                'city',
                'latitude',
                'longitude',
                'coordinates',
            ]);
            $this->assertIsArray($addr['city']);
            $this->assertArrayHasKey('name', $addr['city']);
            $this->assertArrayHasKey('canonical', $addr['city']);
            $this->assertIsArray($addr['coordinates']);
            $this->assertArrayHasKey('latitude', $addr['coordinates']);
            $this->assertArrayHasKey('longitude', $addr['coordinates']);
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetOneRppsData(): void
    {
        $data = $this->get('rpps/' . LoadRPPS::RPPS_USER_1);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Champs de base
        $this->assertEquals('fixture-canonical-0', $data['canonical']);
        $this->assertEquals(LoadRPPS::RPPS_USER_1, $data['idRpps']);
        $this->assertEquals('Docteur', $data['title']);
        $this->assertEquals('OCHROME', $data['lastName']); // uppercased by accessor
        $this->assertEquals('Mercure', $data['firstName']);
        $this->assertEquals('+33123456789', $data['phoneNumber']);
        $this->assertEquals('mercure.ochrome@example.test', $data['email']);

        // Specialty legacy + v2
        $this->assertEquals('Médecine Générale', $data['specialty']);
        $this->assertArrayHasKey('specialtyEntity', $data);
        $this->assertEquals('Médecine Générale', $data['specialtyEntity']['name']);
        $this->assertEquals('medecine-generale', $data['specialtyEntity']['canonical']);

        // Adresses RPPS (v2)
        $this->assertArrayHasKey('addresses', $data);
        $this->assertNotEmpty($data['addresses']);
        $this->assertGreaterThanOrEqual(2, count($data['addresses']));

        // Indexer par libellé (ordre non garanti)
        $byAddress = [];
        foreach ($data['addresses'] as $addr) {
            $byAddress[$addr['address']] = $addr;
        }

        // Adresse complète 1
        $a1 = $byAddress['10 Rue de la Paix'] ?? null;
        $this->assertNotNull($a1, 'Expected address "10 Rue de la Paix" not found');
        $this->assertEquals('Bât A', $a1['addressExtension']);
        $this->assertEquals('75002', $a1['zipcode']);
        $this->assertEquals('10 Rue de la Paix Bât A 75002 Paris', $a1['originalAddress']);
        $this->assertEquals('Paris', $a1['city']['name']);
        $this->assertEquals('paris-2eme', $a1['city']['canonical']);
        $this->assertEquals(48.8686, $a1['latitude']);
        $this->assertEquals(2.3314, $a1['longitude']);
        $this->assertEquals(48.8686, $a1['coordinates']['latitude']);
        $this->assertEquals(2.3314, $a1['coordinates']['longitude']);

        // Adresse complète 2
        $a2 = $byAddress['25 Avenue des Champs'] ?? null;
        $this->assertNotNull($a2, 'Expected address "25 Avenue des Champs" not found');
        $this->assertEquals('Bât B', $a2['addressExtension']);
        $this->assertEquals('75008', $a2['zipcode']);
        $this->assertEquals('25 Avenue des Champs Bât B 75008 Paris', $a2['originalAddress']);
        $this->assertEquals('Paris', $a2['city']['name']);
        $this->assertEquals('paris-8eme', $a2['city']['canonical']);
        $this->assertEquals(48.870637, $a2['latitude']);
        $this->assertEquals(2.318747, $a2['longitude']);
        $this->assertEquals(48.870637, $a2['coordinates']['latitude']);
        $this->assertEquals(2.318747, $a2['coordinates']['longitude']);

        // Legacy flatten : DOIT refléter la première adresse renvoyée
        $primary = $data['addresses'][0];
        $this->assertEquals($primary['address'], $data['address']);
        $this->assertEquals($primary['zipcode'], $data['zipcode']);
        $this->assertEquals($primary['cityName'] ?? $primary['city']['name'], $data['city']);
        $this->assertEquals($primary['addressExtension'] ?? null, $data['addressExtension'] ?? null);
        $this->assertEquals($primary['latitude'], $data['latitude']);
        $this->assertEquals($primary['longitude'], $data['longitude']);
        $this->assertEquals($primary['coordinates']['latitude'], $data['coordinates']['latitude']);
        $this->assertEquals($primary['coordinates']['longitude'], $data['coordinates']['longitude']);
    }

    /**
     * Purpose: ensure the "demo" filter returns ONLY demo RPPS.
     * Implementation detail: demo RPPS is identified by idRpps starting with "2" (see RPPSFilter::addDemoFilter).
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithDemoTrueReturnsRppsDemoData(): void
    {
        $data = $this->get('rpps', ['demo' => true]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Hydra structure sanity checks
        $this->assertArrayHasKey('hydra:member', $data, 'Missing hydra:member');
        $this->assertIsArray($data['hydra:member'], 'hydra:member must be an array');

        // We expect at least one demo entry
        $this->assertNotEmpty($data['hydra:member'], 'Expected at least one demo RPPS');

        // All returned RPPS must start with "2"
        foreach ($data['hydra:member'] as $item) {
            $this->assertArrayHasKey('idRpps', $item, 'RPPS item must have idRpps');
            $this->assertIsString($item['idRpps']);
            $this->assertStringStartsWith('2', $item['idRpps'], 'All demo results must have idRpps starting with "2"');
        }

        // Optionally: assert at least two demo entries if your fixtures guarantee that
        $this->assertCount(2, $data['hydra:member']);
    }

    /**
     * Purpose: ensure the "demo=false" filter excludes demo RPPS.
     * Implementation detail: demo RPPS are identified by idRpps starting with "2", so they must NOT appear here.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithDemoFalseDoesNotReturnRppsDemoData(): void
    {
        // include_paramedical is orthogonal and should not affect demo filtering; we keep it if needed elsewhere
        $data = $this->get('rpps', [
            'demo' => false,
            'include_paramedical' => true,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Hydra structure sanity checks
        $this->assertArrayHasKey('hydra:member', $data, 'Missing hydra:member');
        $this->assertIsArray($data['hydra:member'], 'hydra:member must be an array');

        // If there are any results, ensure none is a "demo" RPPS
        foreach ($data['hydra:member'] as $item) {
            $this->assertArrayHasKey('idRpps', $item, 'RPPS item must have idRpps');
            $this->assertIsString($item['idRpps']);
            $this->assertFalse(
                str_starts_with($item['idRpps'], '2'),
                'Non-demo results must NOT include idRpps starting with "2"'
            );
        }

        // Optional: ensure we got at least one non-demo entry (depends on your fixtures)
        $this->assertGreaterThanOrEqual(1, count($data['hydra:member']));
    }

    /**
     * Purpose: ensure the "excluded_rpps" filter excludes one or several RPPS ids from the collection.
     * Notes:
     *  - We use ids that are actually present in the current fixtures:
     *      * '10101485653' (RPPS_USER_1 - non-demo)
     *      * '21234567890' (demo).
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testExcludedRppsFilter(): void
    {
        // Single exclusion: exclude one known idRpps (non-demo)
        $excludedSingle = '10101485653';
        $data = $this->get('rpps', ['excluded_rpps' => $excludedSingle]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert the excluded RPPS is not present
        $this->assertCollectionKeyNotContains($data['hydra:member'], 'idRpps', [$excludedSingle]);

        // And we still have results (assuming fixtures contain other RPPS)
        $this->assertGreaterThanOrEqual(1, count($data['hydra:member']), 'Expected  remaining RPPS');

        // Multiple exclusions: exclude one non-demo + one demo idRpps
        $excludedMultiple = ['10101485653', '21234567890'];
        $data = $this->get('rpps', ['excluded_rpps' => $excludedMultiple]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert all excluded RPPS are not present
        $this->assertCollectionKeyNotContains($data['hydra:member'], 'idRpps', $excludedMultiple);

        // Optional: if you expect more entries in fixtures, ensure at least one remains
        $this->assertGreaterThanOrEqual(1, count($data['hydra:member']));
    }

    /**
     * Purpose: validate the "search" filter using the new model (no legacy fields).
     * Notes:
     *  - Search currently matches:
     *      * fullName ("<FirstName> <LASTNAME>") with a prefix match
     *      * fullNameInversed ("<LASTNAME> <FirstName>") with a prefix match
     *      * idRpps (exact match)
     *  - We therefore use a prefix of the first name to ensure a predictable hit.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchRppsData(): void
    {
        // Search by first name prefix (matches fullName prefix): should find "Mercure Ochrome"
        $data = $this->get('rpps', [
            'search' => 'Mer',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // We expect a single item: "Mercure Ochrome"
        $this->assertCount(1, $data['hydra:member'], 'Search should return exactly one RPPS for "Mer"');

        // The one result should be our expected item
        $first = $data['hydra:member'][0];

        // Basic identity checks
        $this->assertArrayHasKeys($first, [
            'firstName',
            'lastName',
            'idRpps',
            'canonical',
            'specialtyEntity',
            'addresses',
        ]);
        $this->assertSame('Mercure', $first['firstName']);
        $this->assertSame('OCHROME', $first['lastName']); // uppercased by accessor
        $this->assertSame('fixture-canonical-0', $first['canonical']);

        // Negative checks
        $this->assertCollectionKeyNotContains($data['hydra:member'], 'firstName', ['Emilie', 'Jeremie']);

        // Tricky case: searching a mid-token should NOT match (prefix logic)
        // "rcu" is inside "Mercure" but not a prefix of fullName/fullNameInversed
        $noMidToken = $this->get('rpps', ['search' => 'rcu']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $noMidToken['hydra:member'], 'Mid-token search should not match with prefix logic');
    }

    /**
     * Purpose: verify that searching by RPPS number works as an exact match (no partial match).
     * We cover:
     *  - Exact match on a non-demo RPPS (10101485653 → Mercure OCHROME)
     *  - Exact match on a demo RPPS (21234567890 → Emilie Demo)
     *  - Negative cases: partial match (should return 0), wrong id (0 results).
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSearchByRppsNumber(): void
    {
        // Case 1: exact idRpps (non-demo)
        $data = $this->get('rpps', ['search' => '10101485653']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(1, $data['hydra:member'], 'Exact idRpps search should return exactly one result');

        $rpps = $data['hydra:member'][0];
        $this->assertArrayHasKeys($rpps, ['firstName', 'lastName', 'idRpps', 'canonical']);
        $this->assertSame('10101485653', $rpps['idRpps']);
        $this->assertSame('Mercure', $rpps['firstName']);
        $this->assertSame('OCHROME', $rpps['lastName']); // accessor uppercases lastName

        // Case 2: exact idRpps (demo)
        $data = $this->get('rpps', ['search' => '21234567890']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(1, $data['hydra:member'], 'Exact idRpps search should return exactly one result');

        $demo = $data['hydra:member'][0];
        $this->assertArrayHasKeys($demo, ['firstName', 'lastName', 'idRpps']);
        $this->assertSame('21234567890', $demo['idRpps']);
        $this->assertSame('Emilie', $demo['firstName']);
        $this->assertSame('DEMO', $demo['lastName']);

        // Negative case 1: partial id should NOT match
        $data = $this->get('rpps', ['search' => '1010148565']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $data['hydra:member'], 'Partial idRpps must not match');

        // Negative case 2: unknown id
        $data = $this->get('rpps', ['search' => '99999999999']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $data['hydra:member'], 'Unknown idRpps should return 0 results');
    }

    /**
     * Validate the "first_letter" filter (prefix on lastName, case-insensitive behavior expected by DB collation).
     * We cover:
     *  - Basic prefix "O" → returns only "OCHROME" (Mercure)
     *  - Prefix "D"/"d" → returns both DEMO entries (Emilie, Jeremie), and excludes others
     *  - Negative case: an unused prefix returns 0 results.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testFilterByFirstLetter(): void
    {
        // Case 1: "O" should return only Ochrome (Mercure)
        $data = $this->get('rpps', ['first_letter' => 'O']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(
            1,
            $data['hydra:member'],
            'Expected exactly one RPPS with lastName starting by "O"'
        );

        $only = $data['hydra:member'][0];
        $this->assertArrayHasKeys($only, ['lastName', 'firstName', 'idRpps']);
        $this->assertSame('OCHROME', $only['lastName']);
        $this->assertSame('Mercure', $only['firstName']);
        $this->assertSame('10101485653', $only['idRpps']);

        // Case 2: "D" should return both DEMO entries (Emilie, Jeremie)
        $data = $this->get('rpps', ['first_letter' => 'D']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertArrayHasKey('hydra:member', $data);
        $this->assertIsArray($data['hydra:member']);
        $this->assertGreaterThanOrEqual(
            2,
            count($data['hydra:member']),
            'Expected at least two RPPS with lastName starting by "D"'
        );

        // All last names must be DEMO
        $this->assertCollectionKeyContains($data['hydra:member'], 'lastName', ['DEMO']);
        // Ensure both demo ids are present
        $this->assertCollectionKeyContains($data['hydra:member'], 'idRpps', ['21234567890', '20987654321']);
        // And Ochrome is not present
        $this->assertCollectionKeyNotContains($data['hydra:member'], 'lastName', ['OCHROME']);

        // Case 2b: lower-case "d" behaves the same (collation/LIKE)
        $data = $this->get('rpps', ['first_letter' => 'd']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyContains($data['hydra:member'], 'idRpps', ['21234567890', '20987654321']);
        $this->assertCollectionKeyNotContains($data['hydra:member'], 'lastName', ['OCHROME']);

        // Negative: "Z" returns no results
        $data = $this->get('rpps', ['first_letter' => 'Z']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $data['hydra:member'], 'Unexpected results for unused prefix "Z"');
    }

    /**
     * Purpose: validate the "specialty" filter using specialty canonical.
     * We cover:
     * - Positive: filtering by 'medecine-generale' returns only RPPS whose specialtyEntity matches it
     * - Negative: filtering by a non-existent canonical returns 0 items.
     *
     * Assumptions (fixtures):
     * - All three RPPS in fixtures are assigned to 'medecine-generale'
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testFilterBySpecialty(): void
    {
        // Positive: filter by an existing specialty canonical
        $canonical = 'medecine-generale';

        $data = $this->get('rpps', ['specialty' => $canonical]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // With current fixtures, we expect 4 entries (2 non-demo, 2 demo)
        $this->assertCount(
            4,
            $data['hydra:member'],
            'Expected exactly 4 RPPS for medecine-generale'
        );

        // All results must have the requested specialty canonical
        foreach ($data['hydra:member'] as $item) {
            $this->assertArrayHasKey('specialtyEntity', $item);
            $this->assertIsArray($item['specialtyEntity']);
            $this->assertArrayHasKey('canonical', $item['specialtyEntity']);
            $this->assertSame(
                $canonical,
                $item['specialtyEntity']['canonical'],
                'Returned RPPS has a different specialty than requested'
            );
        }

        // Negative: filter by an unknown canonical → no result
        $data = $this->get('rpps', ['specialty' => 'unknown-canonical-specialty']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $data['hydra:member'], 'Unknown specialty should return 0 RPPS');
    }

    /**
     * Purpose: validate the "city" filter using the addresses list (RPPSAddress.city), not the legacy RPPS.cityEntity.
     * We cover:
     *  - Positive: filtering by 'paris-2eme' returns the RPPS that has an address in that city
     *  - Positive: filtering by 'paris-8eme' also returns the same RPPS (has multiple addresses)
     *  - Negative: unknown city canonical returns 0 items.
     *
     * Notes:
     *  - Fixtures attach two addresses to RPPS 10101485653: paris-2eme and paris-8eme
     *  - Demo RPPS have no addresses in fixtures and should not appear for city filtering
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testFilterByCityUsingAddresses(): void
    {
        // Case 1: City = paris-2eme
        $data = $this->get('rpps', ['city' => 'paris-2eme']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(1, $data['hydra:member'], 'Expected exactly one RPPS in paris-2eme');

        $item = $data['hydra:member'][0];
        $this->assertArrayHasKeys($item, ['idRpps', 'addresses']);
        $this->assertSame('10101485653', $item['idRpps'], 'Only the RPPS with Paris addresses should match');

        // Ensure at least one matching address has the requested city canonical
        $hasCity = false;
        foreach ($item['addresses'] as $addr) {
            if (($addr['city']['canonical'] ?? null) === 'paris-2eme') {
                $hasCity = true;
                break;
            }
        }
        $this->assertTrue($hasCity, 'At least one address must be in paris-2eme');

        // Case 2: City = paris-8eme (same RPPS should match due to second address)
        $data = $this->get('rpps', ['city' => 'paris-8eme']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(1, $data['hydra:member'], 'Expected exactly one RPPS in paris-8eme');

        $item = $data['hydra:member'][0];
        $this->assertSame('10101485653', $item['idRpps']);

        $hasCity = false;
        foreach ($item['addresses'] as $addr) {
            if (($addr['city']['canonical'] ?? null) === 'paris-8eme') {
                $hasCity = true;
                break;
            }
        }
        $this->assertTrue($hasCity, 'At least one address must be in paris-8eme');

        // Negative: unknown canonical
        $data = $this->get('rpps', ['city' => 'unknown']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $data['hydra:member'], 'Unknown city canonical should return 0 results');
    }

    /**
     * Purpose: validate latitude/longitude filtering uses RPPSAddress coordinates (cabinet),
     * not legacy RPPS coords. We keep the existing preparation logic (fixed 30km radius),
     * and only assert that the right RPPS are returned around known points.
     *
     * Cases:
     *  - Near Paris (within 30km): only RPPS_USER_1 (Mercure Ochrome) should match
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testLatitudeLongitudeFilterUsesRppsAddressCoordinates(): void
    {
        // Paris area (between 48.8686,2.3314 and 48.870637,2.318747)
        $paris = $this->get('rpps', [
            'latitude' => 48.8695,
            'longitude' => 2.3255,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertArrayHasKey('hydra:member', $paris);
        $this->assertIsArray($paris['hydra:member']);

        // Expect exactly one RPPS around Paris in 30km with current fixtures
        $this->assertCount(1, $paris['hydra:member']);
        $this->assertSame('10101485653', $paris['hydra:member'][0]['idRpps']);
    }

    /**
     *
     * Purpose: validate latitude/longitude filtering uses RPPSAddress coordinates (cabinet),
     * not legacy RPPS coords. We keep the existing preparation logic (fixed 30km radius),
     * and only assert that the right RPPS are returned around known points.
     *
     * Cases:
     *  - Near Paris (within 30km): only RPPS_USER_1 (Mercure Ochrome) should match
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testLatitudeLongitudeFilterUsesRppsAddressCoordinates2(): void
    {
        // Bourg-en-Bresse area (46.2052, 5.2460)
        $bourg = $this->get('rpps', [
            'latitude' => 46.2052,
            'longitude' => 5.2460,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertArrayHasKey('hydra:member', $bourg);
        $this->assertIsArray($bourg['hydra:member']);

        $this->assertCount(1, $bourg['hydra:member']);
        $this->assertSame('19900000002', $bourg['hydra:member'][0]['idRpps']);
    }
}
