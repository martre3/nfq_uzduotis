<?php

namespace AppBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="orderData")
 */
class Order
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *
     * @Assert\NotBlank(
     *     message = "Prašome įvesti savo vardą",
     * )
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "Vardas per trumpas",
     *      maxMessage = "Vardas per ilgas"
     * )
     * @Assert\Regex(
     *     pattern     = "/^([A-Ža-ž] *)+$/",
     *     message = "Netinkamas vardas (galimos tik raidės ir tarpai)",
     * )
     * @ORM\Column(type="string", length=30)
     */

    protected $name;

    /**
     * @Assert\NotBlank(
     *     message = "Prašome įvesti savo pavardę",
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "Pavardė per trumpa",
     *      maxMessage = "Pavardė per ilga"
     * )
     * @Assert\Regex(
     *     pattern     = "/^([A-Ža-ž])+$/",
     *     message = "Netinkama pavardė(galimos tik raidės ir tarpai)",
     * )
     * @ORM\Column(type="string", length=30)
     */
    protected $lastName;
    /**
     * @Assert\NotBlank(
     *     message = "Prašome įvesti savo adresą",
     * )
     * @Assert\Length(
     *      min = 5,
     *      max = 100,
     *      minMessage = "Adresas per trumpas",
     *      maxMessage = "Adresas per ilgas"
     * )
     * @ORM\Column(type="string", length=100)
     */
    protected $address;
    /**
     * @Assert\NotBlank(
     *     message = "Prašome įvesti savo telefono numerį",
     * )
      * @Assert\Regex(
     *     pattern     = "/^((\+[0-9]{11})|(^[0-9]{9}))$/",
     *     message = "Netinkamas telefono numerio formatas",
     * )
     * @ORM\Column(type="string", length=12)
     */
    protected $phone;

   public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $this->toUpper($name);
    }
    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $this->toUpper($lastName);
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $this->toUpper($address);
    }
    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    function toUpper($str)//bet kokios duotos eilutės raides sumažinam, o pirmą - padarom didžiąja
    {
        $encoding = 'UTF-8';
        $end = mb_strtolower($str, 'UTF-8');
        $first = mb_substr($end, 0, 1, $encoding);
        $first = mb_strtoupper($first);
        $end = mb_substr($end, 1, mb_strlen($end, $encoding), $encoding);
        return $first.$end;
    }
}