<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\Region;
use App\Enum\DepartmentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class LoadDepartment extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $departmentsData = [
            ['Ain', '01', '84', DepartmentType::DEPARTMENT],
            ['Paris', '75', '11', DepartmentType::DEPARTMENT],
            ['Hauts-de-Seine', '92', '11', DepartmentType::DEPARTMENT],
            ['Seine-Saint-Denis', '93', '11', DepartmentType::DEPARTMENT],
            ["Val-D'Oise", '95', '11', DepartmentType::DEPARTMENT],
            ['Guadeloupe', '971', '01', DepartmentType::DOM],
            ['Martinique', '972', '02', DepartmentType::DOM],
            ['Saint-Pierre-et-Miquelon', '975', '95', DepartmentType::COM],
            ['Saint-Barthélémy', '977', '96', DepartmentType::COM],
            ['Nouvelle-Calédonie', '988', '101', DepartmentType::PTOM],
            ['Terres australes et antarctiques françaises', '984', '102', DepartmentType::TOM],
        ];

        foreach ($departmentsData as [$name, $codeDepartment, $codeRegion, $departmentType]) {
            $region = $manager->getRepository(Region::class)->findOneBy(['codeRegion' => $codeRegion]);

            if (!$region) {
                throw new Exception("Region with code '$codeRegion' not found for department '$name'");
            }

            $department = new Department();
            $department->setName($name);
            $department->setCodeDepartment($codeDepartment);
            $department->setRegion($region);
            $department->setDepartmentType($departmentType);
            $department->importId = 'import_1';
            $manager->persist($department);
        }

        $manager->flush();
    }

    /**
     * This fixture depends on the LoadRegion fixture.
     *
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [LoadRegion::class];
    }
}
