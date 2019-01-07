<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Car
 *
 * @ORM\Table(name="cars")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarRepository")
 */
class Car
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
     * @ORM\Column(name="make", type="string", length=255)
     */
    private $make;

    /**
     * @var string
     *
     * @ORM\Column(name="year", type="string", length=255)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", length=255)
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var Engine
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Engine", inversedBy="car")
     * @ORM\JoinColumn(name="engine_id", referencedColumnName="id")
     */
    private $engine;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="cars")
     */
    private $owner;

    /**
     * @var Repair
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Repair")
     * @ORM\JoinColumn(name="repair_id", referencedColumnName="id")
     */
    private $activeRepair;

    /**
     * @var Repair[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Repair", mappedBy="car")
     */
    private $repairs;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_updated", type="boolean", nullable=false)
     */
    private $isUpdated;

    public function __construct()
    {
        $this->repairs = new ArrayCollection();
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
     * Set make
     *
     * @param string $make
     *
     * @return Car
     */
    public function setMake($make)
    {
        $this->make = $make;

        return $this;
    }

    /**
     * Get make
     *
     * @return string
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * Set model
     *
     * @param string $model
     *
     * @return Car
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Car
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Engine $engine
     * @return Car
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param User $owner
     * @return Car
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param $repair
     * @return Car
     */
    public function addRepair($repair)
    {
        $this->repairs[] = $repair;
        return $this;
    }

    /**
     * @return Repair[]|ArrayCollection
     */
    public function getRepairs()
    {
        return $this->repairs;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param string $year
     * @return Car
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return Repair
     */
    public function getActiveRepair()
    {
        return $this->activeRepair;
    }

    /**
     * @param $activeRepair
     * @return Car
     */
    public function setActiveRepair($activeRepair)
    {
        $this->activeRepair = $activeRepair;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsUpdated()
    {
        return $this->isUpdated;
    }

    /**
     * @param bool $isUpdated
     * @return Car
     */
    public function setIsUpdated($isUpdated)
    {
        $this->isUpdated = $isUpdated;
        return $this;
    }

}

