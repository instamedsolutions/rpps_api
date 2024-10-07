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

/**
 * Class ApiTestCase
 *
 * @package App\Tests\Functional
 */
abstract class ApiTestCase extends \ApiPlatform\Symfony\Bundle\Test\ApiTestCase
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @var string
     */
    protected $token;


    /**
     * @var Client
     */
    protected $client;


    /**
     * @var string[]
     */
    public $fixtures = [
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
        LoadCity::class
    ];


    /**
     *
     */
    protected function setUp(): void
    {
        /** @var Client $client */
        $client = self::createClient(array(
            'max_redirects' => 10,
        ));

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

        VarDumper::setHandler(function ($var) {
            $cloner = new VarCloner();
            $dumper = new CliDumper();
            $dumper->dump($cloner->cloneVar($var));
        });
    }


    /**
     *
     * Do a POST Request
     *
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function post($url, $data = array(), $raw = false)
    {
        return $this->doRequest("POST", $url, $data, array(), $raw);
    }


    /**
     *
     * Do a POST Request
     *
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function delete($url)
    {
        return $this->doRequest("DELETE", $url);
    }


    /**
     *
     * Do a POST Request
     *
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function get($url, $data = array(), $raw = false, $headers = array())
    {
        return $this->doRequest("GET", $url, null, $data, $raw, $headers);
    }


    /**
     *
     * Do a POST Request
     *
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function put($url, $data)
    {
        return $this->doRequest("PUT", $url, $data);
    }


    /**
     *
     * Do a POST Request
     *
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function patch($url, $data)
    {
        return $this->doRequest("PATCH", $url, $data);
    }


    /**
     * @param $type
     * @param $url
     * @param array $data
     * @param array $query
     * @param FormDataPart|null $formData
     * @param false $raw
     *
     * @return array|Response
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function doRequest($type, $url, $data = array(), $query = array(), $raw = false, $headers = array())
    {
        if (strpos($url, '/') !== 0) {
            $url = "api/$url";
        };

        $headers = array_merge(array(
            'Content-Type' => 'application/json',
        ), $headers);

        if ($type === "PATCH") {
            $headers['Content-Type'] = 'application/merge-patch+json';
        }

        if ($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }


        $args = array(
            'headers' => $headers
        );

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


    /**
     * @param array|Collection $collection
     * @param string $key
     * @param array $values
     */
    protected function assertCollectionKeyContains($collection, string $key, array $values)
    {
        $data = $this->getCollectionValues($collection, $key);

        foreach ($values as $test) {
            $this->assertContains($test, $data);
        }
    }


    /**
     * @param array|Collection $collection
     * @param string $key
     * @param array $values
     */
    protected function assertCollectionKeyNotContains($collection, string $key, array $values)
    {
        $data = $this->getCollectionValues($collection, $key);

        foreach ($values as $test) {
            $this->assertNotContains($test, $data);
        }
    }


    /**
     * @param array|Collection $collection
     * @param string $key
     * @return array
     */
    protected function getCollectionValues($collection, string $key): array
    {
        if ($collection instanceof Collection) {
            $collection = $collection->toArray();
        }

        $data = [];

        $key = explode(".", $key);

        foreach ($collection as $item) {
            $value = null;
            foreach ($key as $index => $k) {
                if ($index === 0) {
                    $value = $item[$k];
                } else {
                    $value = $value[$k];
                }
            }

            $data[] = $value;
        }

        return $data;
    }


    /**
     * @param $data
     */
    public static function dump($data, $die = true)
    {
        if (is_array($data) && isset($data['error'])) {
            dump($data['error']);
        } else {
            dump($data);
        }

        if (!$die) {
            return;
        }
        die();
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
