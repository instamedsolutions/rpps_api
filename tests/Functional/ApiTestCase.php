<?php


namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase as BaseTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use App\DataFixtures\LoadDiseaseGroups;
use App\DataFixtures\LoadDiseases;
use App\DataFixtures\LoadDrugs;
use App\DataFixtures\LoadRPPS;
use App\Kernel;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ApiTestCase
 * @package App\Tests\Functional
 */
abstract class ApiTestCase extends BaseTestCase
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
    public $fixtures = array(
        LoadRPPS::class,
        LoadDrugs::class,
        LoadDiseaseGroups::class,
        LoadDiseases::class
    );


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
    protected function post($url,$data = array(),$raw = false)
    {
        return $this->doRequest("POST",$url,$data,array(),$raw);
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
        return $this->doRequest("DELETE",$url);
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
    protected function get($url,$data = array(),$raw = false,$headers = array())
    {
        return $this->doRequest("GET",$url,null,$data,$raw,$headers);
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
    protected function put($url,$data)
    {
        return $this->doRequest("PUT",$url,$data);
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
    protected function patch($url,$data)
    {
        return $this->doRequest("PATCH",$url,$data);
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
    protected function doRequest($type,$url,$data = array(),$query = array(),$raw = false,$headers = array())
    {
        if(strpos($url,'/') !== 0) {
            $url = "api/$url";
        };

        $headers = array_merge(array(
            'Content-Type' => 'application/json',
        ),$headers);

        if($type === "PATCH") {
            $headers['Content-Type'] = 'application/merge-patch+json';
        }

        if($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }


        $args = array(
            'headers' => $headers
        );

        if($query) {
            $args['query'] = $query;
        }

        $args['json'] = $data;

        $response = $this->client->request($type, $url,$args);

        $content = $response->getContent(false);

        if($raw) {
            return $response;
        }

        return json_decode($content,true);
    }



    /**
     * @param $data
     */
    public static function dump($data,$die = true)
    {
        if(is_array($data) && isset($data['error'])) {
            dump($data['error']);
        } else {
            dump($data);
        }

        if(!$die) {
            return;
        }
        die();

    }



}
