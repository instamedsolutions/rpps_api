<?php

namespace App\Entity;

enum ModifierType: string
{
    case associatedWith = 'associatedWith';
    case infectiousAgent = 'infectiousAgent';
    case temporalPatternAndOnset = 'temporalPatternAndOnset';
    case hasManifestation = 'hasManifestation';
    case specificAnatomy = 'specificAnatomy';
    case course = 'course';
    case diagnosisConfirmedBy = 'diagnosisConfirmedBy';
    case laterality = 'laterality';
    case hasCausingCondition = 'hasCausingCondition';
    case histopathology = 'histopathology';
    case hasSeverity = 'hasSeverity';
    case hasAlternativeSeverity1 = 'hasAlternativeSeverity1';
    case hasAlternativeSeverity2 = 'hasAlternativeSeverity2';
    case timeInLife = 'timeInLife';
    case medication = 'medication';
    case hasPupilReactionScore = 'hasPupilReactionScore';
    case hasGCSEyeScore = 'hasGCSEyeScore';
    case hasGCSMotorScore = 'hasGCSMotorScore';
    case hasGCSVerbalScore = 'hasGCSVerbalScore';
    case chemicalAgent = 'chemicalAgent';
    case distribution = 'distribution';
    case causality = 'causality';
    case relational = 'relational';
    case typeOfInjury = 'typeOfInjury';
    case fractureSubtype = 'fractureSubtype';
    case fractureOpenOrClosed = 'fractureOpenOrClosed';
    case jointInvolvementInFracture = 'jointInvolvementInFracture';
    case extentOfBurnByBodySurface = 'extentOfBurnByBodySurface';
    case extentOfFullThicknessBurnByBodySurface = 'extentOfFullThicknessBurnByBodySurface';
    case outcomeOfFullThicknessBurn = 'outcomeOfFullThicknessBurn';
    case activityWhenInjured = 'activityWhenInjured';
    case placeOfOccurrence = 'placeOfOccurrence';
    case transportEventDescriptor = 'transportEventDescriptor';
    case alcoholUseInInjury = 'alcoholUseInInjury';
    case psychoactiveDrugUseInInjury = 'psychoactiveDrugUseInInjury';
    case objectOrSubstanceProducingInjury = 'objectOrSubstanceProducingInjury';
    case sportsActivityDescriptor = 'sportsActivityDescriptor';
    case aspectsOfIntentionalSelfHarm = 'aspectsOfIntentionalSelfHarm';
    case aspectsOfAssaultAndMaltreatment = 'aspectsOfAssaultAndMaltreatment';
    case mechanismOfInjury = 'mechanismOfInjury';
    case aspectsOfArmedConflict = 'aspectsOfArmedConflict';
}
