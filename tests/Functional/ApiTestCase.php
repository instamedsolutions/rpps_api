<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\LoadAllergens;
use App\DataFixtures\LoadCCAM;
use App\DataFixtures\LoadCCAMGroup;
use App\DataFixtures\LoadCity;
use App\DataFixtures\LoadDCim11;
use App\DataFixtures\LoadDepartment;
use App\DataFixtures\LoadDiseaseGroups;
use App\DataFixtures\LoadDiseases;
use App\DataFixtures\LoadDrugs;
use App\DataFixtures\LoadInseeCommune;
use App\DataFixtures\LoadInseeCommune1943;
use App\DataFixtures\LoadInseePays;
use App\DataFixtures\LoadInseePays1943;
use App\DataFixtures\LoadNGAP;
use App\DataFixtures\LoadRegion;
use App\DataFixtures\LoadRPPS;
use App\DataFixtures\LoadSpecialty;
use App\Entity\City;
use App\Entity\Specialty;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class ApiTestCase extends \ApiPlatform\Symfony\Bundle\Test\ApiTestCase
{
    protected EntityManagerInterface $em;
    protected ?string $token = null;
    protected Client $client;

    public array $fixtures = [
        LoadRPPS::class,
        LoadDrugs::class,
        LoadDiseaseGroups::class,
        LoadDiseases::class,
        LoadCCAMGroup::class,
        LoadDCim11::class,
        LoadCCAM::class,
        LoadAllergens::class,
        LoadNGAP::class,
        LoadSpecialty::class,
        LoadRegion::class,
        LoadDepartment::class,
        LoadCity::class,
        LoadInseeCommune::class,
        LoadInseeCommune1943::class,
        LoadInseePays::class,
        LoadInseePays1943::class,
    ];

    protected function setUp(): void
    {
        $client = self::createClient([
            'max_redirects' => 10,
        ]);

        $kernel = $client->getKernel();

        $container = $client->getContainer();
        $doctrine = $container->get('doctrine');

        $this->em = $doctrine->getManager();

        $loader = new ContainerAwareLoader($kernel->getContainer());

        $purger = new ORMPurger($this->em);
        $purger->purge();

        foreach ($this->fixtures as $class) {
            $fixture = new $class();
            $loader->addFixture($fixture);
        }

        $executor = new ORMExecutor($this->em);
        $executor->execute($loader->getFixtures(), true);

        $this->client = $client;

        VarDumper::setHandler(static function ($var) {
            $cloner = new VarCloner();
            $dumper = new CliDumper();
            $dumper->dump($cloner->cloneVar($var));
        });
    }

    /**
     * Do a POST Request.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function post($url, $data = [], $raw = false)
    {
        return $this->doRequest('POST', $url, $data, [], $raw);
    }

    /**
     * Do a POST Request.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function delete($url)
    {
        return $this->doRequest('DELETE', $url);
    }

    /**
     * Do a POST Request.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function get($url, $data = [], $raw = false, $headers = [])
    {
        // Check if the 'Accept-Language' header is not already set
        if (!isset($headers['Accept-Language'])) {
            $headers['Accept-Language'] = 'fr'; // Add the default language
        }

        return $this->doRequest('GET', $url, null, $data, $raw, $headers);
    }

    /**
     * Do a POST Request.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function put($url, $data)
    {
        return $this->doRequest('PUT', $url, $data);
    }

    /**
     * Do a POST Request.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function patch($url, $data)
    {
        return $this->doRequest('PATCH', $url, $data);
    }

    /**
     * @param array $data
     * @param array $query
     * @param false $raw
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function doRequest($type, $url, $data = [], $query = [], $raw = false, $headers = [])
    {
        if (0 !== strpos($url, '/')) {
            $url = "api/$url";
        }

        $headers = array_merge([
            'Content-Type' => 'application/json',
        ], $headers);

        if ('PATCH' === $type) {
            $headers['Content-Type'] = 'application/merge-patch+json';
        }

        if ($this->token) {
            $headers['Authorization'] = "Bearer $this->token";
        }

        $args = [
            'headers' => $headers,
        ];

        if ($query) {
            $args['query'] = $query;
        }

        $args['json'] = $data;

        $response = $this->client->request($type, $url, $args);

        $content = $response->getContent(false);

        if ($raw) {
            return $response;
        }

        return json_decode($content, true);
    }

    protected function assertCollectionKeyContains($collection, string $key, array $values): void
    {
        $data = $this->getCollectionValues($collection, $key);

        foreach ($values as $test) {
            $this->assertContains($test, $data);
        }
    }

    protected function assertCollectionKeyNotContains($collection, string $key, array $values): void
    {
        $data = $this->getCollectionValues($collection, $key);

        foreach ($values as $test) {
            $this->assertNotContains($test, $data);
        }
    }

    protected function getCollectionValues($collection, string $key): array
    {
        if ($collection instanceof Collection) {
            $collection = $collection->toArray();
        }

        $data = [];

        $key = explode('.', $key);

        foreach ($collection as $item) {
            $value = null;
            foreach ($key as $index => $k) {
                if (0 === $index) {
                    $value = $item[$k];
                } else {
                    $value = $value[$k];
                }
            }

            $data[] = $value;
        }

        return $data;
    }

    public static function dump($data, $die = true): void
    {
        if (is_array($data) && isset($data['error'])) {
            dump($data['error']);
        } else {
            dump($data);
        }

        if (!$die) {
            return;
        }
        exit;
    }

    protected function getSpecialty(string $canonical = 'medecine-generale'): ?Specialty
    {
        return $this->em->getRepository(Specialty::class)->findOneBy(['canonical' => $canonical]);
    }

    protected function getCity(string $canonical = 'paris'): ?City
    {
        return $this->em->getRepository(City::class)->findOneBy(['canonical' => $canonical]);
    }
}
