<?php
// api/src/DataProvider/RPPSItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\RPPS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RPPSItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @var Request|null
     */
    protected $request;

    /**
     * ModuleItemDataProvider constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(RequestStack $requestStack,EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->request = $requestStack->getMasterRequest();
    }


    /**
     * @param string $resourceClass
     * @param string|null $operationName
     * @param array $context
     * @return bool
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return RPPS::class === $resourceClass && "get" === $operationName;
    }


    /**
     * @param string $resourceClass
     * @param array|int|string $id
     * @param string|null $operationName
     * @param array $context
     * @return RPPS|null
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?RPPS
    {

        if(0 === $id) {
            $id = $this->request->get("id",null);
        }

        if(null === $id) {
            return null;
        }

        // Some uuId start with 00
        if(strpos($id,'-') === false) {
            $id = preg_replace('#^0+#', '', $id);
        }

        return $this->em->getRepository(RPPS::class)->find($id);

    }
}
