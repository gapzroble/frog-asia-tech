<?php

namespace FrogAsia;

/**
 * @author Randolph Roble <roblerm@gmail.com>
 * @Entity
 */
class Pond
{
    
    const CONDITION_NORMAL = 'Normal',
            CONDITION_HOT = 'Hot',
            CONDITION_COLD = 'Cold',
            CONDITION_PERFECT = 'Perfect';
    
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @Column(type="string", length=20)
     */
    private $condition;
    
    public function __construct()
    {
        $this->condition = static::CONDITION_NORMAL;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition($condition)
    {
        switch($condition) {
            case static::CONDITION_COLD:
            case static::CONDITION_HOT:
            case static::CONDITION_NORMAL:
            case static::CONDITION_PERFECT:
                break;
            default: throw new \Exception('Invalid pond condition setting.');
        }
        $this->condition = $condition;
        return $this;
    }
    
    public function __toString()
    {
        return (string) $this->condition;
    }

    public function mate(Frog $frog, Frog $partner)
    {
        if ($frog->getId() == $partner->getId()) {
            throw new \Exception('Masturbation is healthy but what a waste of time and energy.');
        }
        
        if ($partner->isDead()) {
            throw new \Exception('There is no chance having kids from mating with the dead. Try it, how disgusting.');
        }
        
        if ($frog->getGender() == $partner->getGender()) {
            throw new \Exception('Same sex, really? Perhaps adoption works.');
        }
        
        return $this->createFrogs($frog, $partner);
    }
    
    public function createFrogs(Frog $parent1 = null, Frog  $parent2 = null)
    {
        $min = 0;
        $max = 0;
        switch ($this->condition) {
            case static::CONDITION_COLD:
                $min = 0;
                $max = 3;
                break;
            case static::CONDITION_HOT:
                $min = 0;
                $max = 1;
                break;
            case static::CONDITION_NORMAL:
                $min = 1;
                $max = 5;
                break;
            case static::CONDITION_PERFECT:
                $min = 3;
                $max = 10;
                break;
        }
        
        $result = [];
        $possibleKids = rand($min, $max);
        for ($i = 0; $i < $possibleKids; $i++) {
            $result[] = $this->createFrog($parent1, $parent2);
        }
        return $result;
    }
    
    private function createFrog(Frog $parent1 = null, Frog $parent2 = null)
    {
        $gender = rand(0, 1) ? Frog::GENDER_FEMALE : Frog::GENDER_MALE;
        if ($parent1 && $parent2)
        {
            if ($parent1->isFemale()) {
                $surname = $parent2->getName();
                $mother = $parent1;
                $father = $parent2;
            } else {
                $surname = $parent1->getName();
                $mother = $parent2;
                $father = $parent1;
            }
            list($firstname,) = explode(' ', $surname);
            $name = sprintf('%s %s', $this->getRandomName($gender), $firstname);
        } else {
            $name = $this->getRandomName($gender);
        }
        $child = new Frog();
        $child->setName($name)
                ->setGender($gender)
                ->setFather(isset($father) ? $father : null)
                ->setMother(isset($mother) ? $mother : null);
        return $child;
    }
    
    private function getRandomName($gender)
    {
        switch($gender) {
            case Frog::GENDER_MALE:
                $choices = ['Stefan','Freddy','Gordon','Bruno','Lorenzo','Abdul',
                            'Werner','Jesse','Larry','Harrison','Basil','Cody',
                            'Jarrod','Santos','Rey','Graham','Jamie','Luis',
                            'Marty','Geoffrey'];
                break;
            case Frog::GENDER_FEMALE:
                $choices = ['Madlyn','Etsuko','Kristin','Shari','Shirly','Dawn',
                            'Stefania','Kristyn','Charlsie','Carin','Lola',
                            'Takako','Louetta','Debrah','Melissia','Elvia',
                            'Gillian','Ghislaine','Oneida','Chan'];
                break;
            default : $choices = ['Frog'];
        }
        shuffle($choices);
        return $choices[0];
    }

    public static function conditions()
    {
        $list = array(static::CONDITION_COLD, static::CONDITION_HOT, 
            static::CONDITION_NORMAL, static::CONDITION_PERFECT);
        return array_combine($list, $list);
    }
    
}
