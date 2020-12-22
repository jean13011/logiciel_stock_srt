<?php

namespace App\Entity;

use App\Repository\ProductActionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductActionRepository::class)
 * @ORM\Table(name="`product_action`")
 */
class ProductAction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $idProduct;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $newName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $newReference;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $newQuantity;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $newEmplacement;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modificationDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdProduct(): ?int
    {
        return $this->idProduct;
    }

    public function setIdProduct(int $idProduct): self
    {
        $this->idProduct = $idProduct;

        return $this;
    }

    public function getNewName(): ?string
    {
        return $this->newName;
    }

    public function setNewName(?string $newName): self
    {
        $this->newName = $newName;

        return $this;
    }

    public function getNewReference(): ?string
    {
        return $this->newReference;
    }

    public function setNewReference(?string $newReference): self
    {
        $this->newReference = $newReference;

        return $this;
    }

    public function getNewQuantity(): ?int
    {
        return $this->newQuantity;
    }

    public function setNewQuantity(?int $newQuantity): self
    {
        $this->newQuantity = $newQuantity;

        return $this;
    }

    public function getNewEmplacement(): ?string
    {
        return $this->newEmplacement;
    }

    public function setNewEmplacement(?string $newEmplacement): self
    {
        $this->newEmplacement = $newEmplacement;

        return $this;
    }

    public function getModificationDate(): ?\DateTimeInterface
    {
        return $this->modificationDate;
    }

    public function setModificationDate(?\DateTimeInterface $modificationDate): self
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }
}
