<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Visa Workflow Repository
 * @author dev@maarch.org
 */

declare(strict_types=1);

namespace MaarchCourrier\SignatureBook\Infrastructure\Repository;

use Entity\models\ListInstanceModel;
use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceInterface;
use MaarchCourrier\Core\Domain\Problem\ParameterMustBeGreaterThanZeroException;
use MaarchCourrier\Core\Domain\User\Port\UserFactoryInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\Core\Domain\User\Problem\UserDoesNotExistProblem;
use MaarchCourrier\SignatureBook\Domain\Port\VisaWorkflowRepositoryInterface;

class VisaWorkflowRepository implements VisaWorkflowRepositoryInterface
{
    public function __construct(
        private readonly UserFactoryInterface $userFactory
    ) {
    }

    /**
     * @param MainResourceInterface $mainResource
     *
     * @return bool
     */
    public function isWorkflowActiveByMainResource(MainResourceInterface $mainResource): bool
    {
        $listInstances = ListInstanceModel::get([
            'select'    => ['COUNT(*)'],
            'where'     => ['res_id = ?', 'item_mode in (?)', 'process_date IS NULL'],
            'data'      => [$mainResource->getResId(), ['visa', 'sign']]
        ]);

        return ((int)$listInstances[0]['count'] > 0);
    }

    /**
     * @param MainResourceInterface $mainResource
     *
     * @return ?UserInterface
     * @throws ParameterMustBeGreaterThanZeroException
     * @throws UserDoesNotExistProblem
     */
    public function getCurrentStepUserByMainResource(MainResourceInterface $mainResource): ?UserInterface
    {
        $currentStep = ListInstanceModel::getCurrentStepByResId(['resId' => $mainResource->getResId()]);

        if (empty($currentStep['item_id'])) {
            return null;
        }

        return $this->userFactory->createRetrieveUser()->getUserById($currentStep['item_id']);
    }
}
