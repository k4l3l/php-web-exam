<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Repair
 *
 * @ORM\Table(name="repairs")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RepairRepository")
 */
class Repair
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_full_service", type="boolean")
     */
    private $isFullService;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_diagnostics_check", type="boolean")
     */
    private $isDiagnosticsCheck;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_run_gear_check", type="boolean")
     */
    private $isRunGearCheck;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_archived", type="boolean")
     */
    private $isArchived;

    /**
     * @var string
     *
     * @ORM\Column(name="other_service", type="text")
     */
    private $otherService;

    /**
     * @var Car
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Car", inversedBy="repairs")
     */
    private $car;

//    /**
//     * @var User
//     *
//     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="repairs")
//     */
//    private $client;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime")
     */
    private $dateCreated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modified", type="datetime")
     */
    private $dateModified;

    public function __construct()
    {
        $this->dateCreated = new \DateTime("now");
        $this->dateModified = new \DateTime("now");
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Repair
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isFullService
     *
     * @param boolean $isFullService
     *
     * @return Repair
     */
    public function setIsFullService($isFullService)
    {
        $this->isFullService = $isFullService;

        return $this;
    }

    /**
     * Get isFullService
     *
     * @return bool
     */
    public function getIsFullService()
    {
        return $this->isFullService;
    }

    /**
     * Set isDiagnosticsCheck
     *
     * @param boolean $isDiagnosticsCheck
     *
     * @return Repair
     */
    public function setIsDiagnosticsCheck($isDiagnosticsCheck)
    {
        $this->isDiagnosticsCheck = $isDiagnosticsCheck;

        return $this;
    }

    /**
     * Get isDiagnosticsCheck
     *
     * @return bool
     */
    public function getIsDiagnosticsCheck()
    {
        return $this->isDiagnosticsCheck;
    }

    /**
     * Set isRunGearCheck
     *
     * @param boolean $isRunGearCheck
     *
     * @return Repair
     */
    public function setIsRunGearCheck($isRunGearCheck)
    {
        $this->isRunGearCheck = $isRunGearCheck;

        return $this;
    }

    /**
     * Get isRunGearCheck
     *
     * @return bool
     */
    public function getIsRunGearCheck()
    {
        return $this->isRunGearCheck;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param bool $isArchived
     * @return Repair
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;
        return $this;
    }

    /**
     * Set otherService
     *
     * @param string $otherService
     *
     * @return Repair
     */
    public function setOtherService($otherService)
    {
        $this->otherService = $otherService;

        return $this;
    }

    /**
     * Get otherService
     *
     * @return string
     */
    public function getOtherService()
    {
        return $this->otherService;
    }


    /**
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @param Car $car
     * @return Repair
     */
    public function setCar($car)
    {
        $this->car = $car;

        return $this;
    }

//    /**
//     * @return User
//     */
//    public function getClient()
//    {
//        return $this->client;
//    }
//
//    /**
//     * @param User $client
//     * @return Repair
//     */
//    public function setClient($client)
//    {
//        $this->client = $client;
//
//        return $this;
//    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     * @return Repair
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @param \DateTime $dateModified
     * @return Repair
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }
}

