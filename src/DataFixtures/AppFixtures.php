<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\RoleSkill;
use App\Entity\Skill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $skillsData = [
            ['SQL',           'Databases'],
            ['PostgreSQL',    'Databases'],
            ['MongoDB',       'Databases'],
            ['Symfony',       'Programming'],
            ['React',         'Programming'],
            ['TypeScript',    'Programming'],
            ['Node.js',       'Programming'],
            ['Python',        'AI'],
            ['Docker',        'DevOps'],
            ['Kubernetes',    'DevOps'],
            ['Git',           'DevOps'],
            ['Linux',         'DevOps'],
            ['CI/CD',         'DevOps'],
            ['AWS',           'DevOps'],
            ['JWT Auth',      'Programming'],
            ['REST API',      'Programming'],
            ['Salesforce',    'Salesforce'],
            ['Apex',          'Salesforce'],
            ['System Design', 'Programming'],
            ['Machine Learning', 'AI'],
        ];

        $skills = [];
        foreach ($skillsData as [$name, $category]) {
            $skill = new Skill();
            $skill->setName($name)->setCategory($category);
            $manager->persist($skill);
            $skills[$name] = $skill;
        }

        // ── Roles ────────────────────────────────────────────────────────────
        $rolesData = [
            'Backend Developer' => [
                'description' => 'Builds server-side APIs and services',
                'skills' => [
                    'Symfony'       => 4,
                    'SQL'           => 3,
                    'PostgreSQL'    => 3,
                    'REST API'      => 4,
                    'Docker'        => 3,
                    'Git'           => 3,
                    'JWT Auth'      => 3,
                    'System Design' => 3,
                ],
            ],
            'Frontend Developer' => [
                'description' => 'Builds web UIs with React and TypeScript',
                'skills' => [
                    'React'         => 4,
                    'TypeScript'    => 4,
                    'REST API'      => 3,
                    'Git'           => 3,
                ],
            ],
            'DevOps Engineer' => [
                'description' => 'Manages infrastructure, CI/CD and deployments',
                'skills' => [
                    'Docker'     => 4,
                    'Kubernetes' => 4,
                    'Linux'      => 4,
                    'CI/CD'      => 4,
                    'AWS'        => 3,
                    'Git'        => 4,
                ],
            ],
            'AI / Data Engineer' => [
                'description' => 'Builds ML pipelines and data systems',
                'skills' => [
                    'Python'           => 4,
                    'Machine Learning' => 4,
                    'SQL'              => 3,
                    'PostgreSQL'       => 3,
                    'MongoDB'          => 3,
                    'Docker'           => 3,
                ],
            ],
            'CRM Developer' => [
                'description' => 'Develops and customises Salesforce CRM solutions',
                'skills' => [
                    'Salesforce' => 4,
                    'Apex'       => 4,
                    'REST API'   => 3,
                    'SQL'        => 3,
                    'Git'        => 3,
                ],
            ],
            'Senior Full-Stack Developer' => [
                'description' => 'Leads full-stack development across frontend and backend',
                'skills' => [
                    'React'         => 4,
                    'TypeScript'    => 4,
                    'Node.js'       => 4,
                    'PostgreSQL'    => 3,
                    'Docker'        => 3,
                    'System Design' => 4,
                    'AWS'           => 3,
                    'CI/CD'         => 3,
                    'REST API'      => 4,
                    'Git'           => 4,
                ],
            ],
        ];

        foreach ($rolesData as $roleName => $roleInfo) {
            $role = new Role();
            $role->setName($roleName)->setDescription($roleInfo['description']);
            $manager->persist($role);

            foreach ($roleInfo['skills'] as $skillName => $requiredLevel) {
                if (!isset($skills[$skillName])) continue;

                $rs = new RoleSkill();
                $rs->setRole($role)
                    ->setSkill($skills[$skillName])
                    ->setRequiredLevel($requiredLevel);
                $manager->persist($rs);
            }
        }

        $manager->flush();
    }
}
