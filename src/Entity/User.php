<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email", "username"},
 *     errorPath="email",
 *     message="Adresse mail/Nom d'utilisateur déjà utilisé"
 * )
 *
 */
class User implements UserInterface {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z0-9]+$/i",
     *     htmlPattern = "^[a-zA-Z0-9]+$",
     *     message="Ne peut être que des lettres et des chiffres"
     * )
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\Email(
     *     message = "Cette adresse email est invalide",
     *     checkMX= true
     * )
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalScore = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $biography;

    /**
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z]+$/i",
     *     htmlPattern = "^[a-zA-Z]+$",
     *     message="Ne peux contenir que des lettres"
     * )
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $city;

    /**
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z0-9.]+$/i",
     *     htmlPattern = "^[a-zA-Z0-9.]+$",
     *     message="Domaine du site sans http(s):// "
     * )
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $website;

    /**
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z]+$/i",
     *     htmlPattern = "^[a-zA-Z]+$",
     *     message="Ne peux contenir que des lettres"
     * )
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $facebook;

    /**
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z0-9_-]+$/i",
     *     htmlPattern = "^[a-zA-Z0-9_-]+$",
     *     message="Votre Pseudo Twitter sans twitter.com/"
     * )
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $twitter;

    /**
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z0-9_-]+$/i",
     *     htmlPattern = "^[a-zA-Z0-9_-]+$",
     *     message="Votre Pseudo Instagram sans instagram.com"
     * )
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $instagram;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $picture = 'default.png';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Like", mappedBy="user", orphanRemoval=true)
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="user", orphanRemoval=true)
     */
    private $posts;

    /**
     * @ORM\Column(type="integer")
     */
    private $monthScore = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validated = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Badge", inversedBy="users")
     * @OrderBy({"cost" = "DESC"})
     */
    private $badges;


    public function __construct() {
        $this->likes = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->badges = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string {
        return (string)$this->username;
    }

    public function setUsername(string $username): self {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string {
        return (string)$this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt() {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;

        return $this;
    }

    public function getTotalScore(): ?float {
        return $this->totalScore;
    }

    public function setTotalScore(float $totalScore): self {
        $this->totalScore = $totalScore;

        return $this;
    }

    public function addScore(float $score): self {
        $this->totalScore += $score;
        $this->monthScore += $score;
        return $this;
    }

    public function getBiography(): ?string {
        return $this->biography;
    }

    public function setBiography(?string $biography): self {
        $this->biography = $biography;

        return $this;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function setCity(?string $city): self {
        $this->city = $city;

        return $this;
    }

    public function getWebsite(): ?string {
        return $this->website;
    }

    public function setWebsite(?string $website): self {
        $this->website = $website;

        return $this;
    }

    public function getFacebook(): ?string {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self {
        $this->facebook = $facebook;

        return $this;
    }

    public function getTwitter(): ?string {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self {
        $this->twitter = $twitter;

        return $this;
    }

    public function getInstagram(): ?string {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self {
        $this->instagram = $instagram;

        return $this;
    }

    public function getPicture(): ?string {
        return $this->picture;
    }

    public function setPicture(string $picture): self {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Collection|Like[]
     */
    public function getLikes(): Collection {
        return $this->likes;
    }

    public function addLike(Like $like): self {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection {
        return $this->posts;
    }

    public function addPost(Post $post): self {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    public function getMonthScore(): ?int {
        return $this->monthScore;
    }

    public function setMonthScore(int $monthScore): self {
        $this->monthScore = $monthScore;

        return $this;
    }

    public function isValidated(): ?bool {
        return $this->validated;
    }

    public function setValidated(bool $validated): self {
        $this->validated = $validated;

        return $this;
    }

    public function getToken(): ?string {
        return $this->token;
    }

    public function setToken(?string $token): self {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection|Badge[]
     */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    public function addBadge(Badge $badge): self
    {
        if (!$this->badges->contains($badge)) {
            $this->badges[] = $badge;
        }

        return $this;
    }

    public function removeBadge(Badge $badge): self
    {
        if ($this->badges->contains($badge)) {
            $this->badges->removeElement($badge);
        }

        return $this;
    }

}
