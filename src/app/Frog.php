<?php

namespace FrogAsia;

/**
 * @author Randolph Roble <roblerm@gmail.com>
 * @Entity(repositoryClass="FrogAsia\FrogRepository") 
 */
class Frog
{

    const GENDER_MALE = 'Male',
            GENDER_FEMALE = 'Female';

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @Column(type="string", length=1, nullable=false)
     */
    private $gender;

    /**
     * @Column(type="datetime", nullable=false)
     */
    private $birthdate;

    /**
     * @Column(type="datetime", nullable=true)
     */
    private $death;

    /**
     * @ManyToOne(targetEntity="FrogAsia\Frog", inversedBy="offspring1")
     */
    private $father;

    /**
     * @ManyToOne(targetEntity="FrogAsia\Frog", inversedBy="offspring2")
     */
    private $mother;
    
    /**
     * @OneToMany(targetEntity="FrogAsia\Frog", mappedBy="father")
     */
    private $offspring1;
    
    /**
     * @OneToMany(targetEntity="FrogAsia\Frog", mappedBy="mother")
     */
    private $offspring2;

    public function __construct()
    {
        $this->birthdate = new \DateTime();
        $this->dead = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function isDead()
    {
        return $this->death !== null;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setGender($gender)
    {
        $genders = static::genders();
        if (!isset($genders[$gender])) {
            throw new \Exception('Invalid pond condition setting.');
        }
        
        if ($this->gender && $this->gender != $gender) {
            throw new \Exception('Changing gender is not supported, maybe in Thailand?');
        }
        
        $this->gender = $gender;
        return $this;
    }

    public function setBirthdate(\DateTime $death)
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function getDeath()
    {
        return $this->death;
    }

    public function setDeath(\DateTime $death = null)
    {
        $this->death = $death;
        return $this;
    }

    public function __toString()
    {
        $name = (string) $this->name;
        if ($this->isDead()) {
            $name .= ' (dead)';
        }
        return $name;
    }

    public function getAge()
    {
        $date = $this->death ? $this->death : new \DateTime();

        $d = $this->birthdate->diff($date);
        if ($d->y > 0) {
            $format = '%y years';
        } elseif ($d->m > 0) {
            $format = '%m months';
        } elseif ($d->d > 0) {
            $format = '%d days';
        } elseif ($d->i > 0) {
            $format = '%i mins';
        } elseif ($d->s > 0) {
            $format = '%s secs';
        } else {
            return '1 sec';
        }

        return $d->format($format);
    }

    public function isFemale()
    {
        return $this->gender == static::GENDER_FEMALE;
    }

    public function __clone()
    {
        $this->id = null;
        $this->name .= ' (clone)';
        $this->birthdate = new \DateTime();
        $this->death = null;
    }

    public function getFather()
    {
        return $this->father;
    }

    public function getMother()
    {
        return $this->mother;
    }

    public function setFather(Frog $father = null)
    {
        $this->father = $father;
        return $this;
    }

    public function setMother(Frog $mother = null)
    {
        $this->mother = $mother;
        return $this;
    }
    
    public function kill()
    {
        $this->death = new \DateTime();
        return $this;
    }
    
    public function getParents()
    {
        $p = [];
        if ($this->father) {
            $p[] = (string) $this->father;
        }
        if ($this->mother) {
            $p[] = (string) $this->mother;
        }
        return implode(' + ', $p);
    }

    public function getOffspring()
    {
        $result = [];
        foreach ($this->offspring1 as $o1) {
            $result[] = $o1;
        }
        foreach ($this->offspring2 as $o2) {
            $result[] = $o2;
        }
        return $result;
    }

    public static function genders()
    {
        $list = array(static::GENDER_MALE, static::GENDER_FEMALE);
        return array_combine($list, $list);
    }
    
}
