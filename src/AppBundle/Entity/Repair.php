<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var string
     *
     * @ORM\Column(name="report", type="text", nullable=true)
     */
    private $report;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_archived", type="boolean", nullable=false)
     */
    private $isArchived;

    /**
     * @var Car
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Car", inversedBy="repairs")
     */
    private $car;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="repairs")
     */
    private $client;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var ArrayCollection
     *
     *@ORM\OneToMany(targetEntity="AppBundle\Entity\RepairImage", mappedBy="repair", cascade={"persist"}, orphanRemoval=true)
     */
    private $images;

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

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="repair", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $notifications;

    public function __construct()
    {
        $this->dateCreated = new \DateTime("now");
        $this->dateModified = new \DateTime("now");
        $this->images = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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
     * @return bool
     */
    public function getIsArchived()
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

    /**
     * @return string
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param string $report
     * @return
     */
    public function setReport($report)
    {
        $this->report = $report;
        return $this;
    }


    /**
     * @return User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param User $client
     * @return Repair
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Add image
     *
     * @param RepairImage $image
     *
     * @return Repair
     */
    public function addImage(RepairImage $image)
    {
        // Bidirectional Ownership
        $image->setRepair($this);

        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param RepairImage $image
     */
    public function removeImage(RepairImage $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param Notification $notification
     * @return Repair
     */
    public function addNotification($notification)
    {
        $this->notifications[] = $notification;
        return $this;
    }

}

