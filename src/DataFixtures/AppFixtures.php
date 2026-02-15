<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Skill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ROLES
        $roles = [
            'CRM Developer',
            'Backend Developer',
            'DevOps Engineer',
            'AI / Data Engineer'
        ];

        foreach ($roles as $roleName) {
            $role = new Role();
            $role->setName($roleName);
            $manager->persist($role);
        }

        // SKILLS
        $skills = [
            ['SQL', 'Databases'],
            ['PostgreSQL', 'Databases'],
            ['Symfony', 'Programming'],
            ['React', 'Programming'],
            ['Docker', 'DevOps'],
            ['Git', 'DevOps'],
            ['Linux', 'DevOps'],
            ['JWT Auth', 'Programming'],
            ['REST API', 'Programming'],
            ['Salesforce', 'Salesforce'],
        ];

        foreach ($skills as [$name, $category]) {
            $skill = new Skill();
            $skill->setName($name);
            $skill->setCategory($category);
            $manager->persist($skill);
        }

        $manager->flush();
    }
}
