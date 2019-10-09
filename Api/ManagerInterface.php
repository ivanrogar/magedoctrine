<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Api;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @api
 * Interface ManagerInterface
 * @package JohnRogar\MageDoctrine\Api
 */
interface ManagerInterface
{

    /**
     * @return EntityManagerInterface
     */
    public function getManager(): EntityManagerInterface;
}