<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Offer
 *
 * @ORM\Table(name="cuistot_category", options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Recipe", mappedBy="user", cascade={"persist", "remove"})
     */
    private $receipts;

    public function __construct()
    {
        $this->receipts = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return Category
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add recipe
     *
     * @param Recipe $recipe
     *
     * @return Category
     */
    public function addRecipe(Recipe $recipe)
    {
        $this->receipts[] = $recipe;

        return $this;
    }

    /**
     * Remove recipe
     *
     * @param Recipe $recipe
     *
     * @return User
     */
    public function removeOfferItem(Recipe $recipe)
    {
        $this->receipts->removeElement($recipe);

        return $this;
    }

    /**
     * Get receipts
     *
     * @return ArrayCollection
     */
    public function getReceipts()
    {
        return $this->receipts;
    }

}
