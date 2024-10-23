<?php

namespace App\Enum;

enum DepartmentType: string
{
    case DEPARTMENT = 'department';
    case DOM = 'dom'; // départements d'outre-mer
    case COM = 'com'; // collectivités d'outre-mer
    case TOM = 'tom'; // territoires d'outre-mer
    case PTOM = 'ptom'; // Pays et territoires d'outre-mer
}
