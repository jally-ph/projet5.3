<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Books;
use App\Entity\Chapter;
use App\Entity\Comment;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $faker = \Faker\Factory::create('fr_FR');

        // $product = new Product();
        // $manager->persist($product);
        for($i=1; $i <= 4; $i++){
            $book = new Books();

            $content = '<p>'. join($faker->paragraphs(5), '</p><p>') .'</p>';
            
            // $status_1 = true;
            // $status_2 = false;
             
            $status = rand(0,1);
            $status2 = rand(0,1);

            $book->setTitle($faker->sentence())
                ->setContent($content)
                ->setAuthor($faker->name)
                ->setDate($faker->dateTimeBetween('-6 months'))
                ->setPublic($status)
                ->setCompleted($status2);

            $manager->persist($book);

            // Créer 4 chapters
            for($j = 1; $j <= mt_rand(3, 8); $j++)
            {
                $status = rand(0,1);
                $status2 = rand(0,1);

                $chapter = new Chapter();
                $chapter->setTitle($faker->sentence())
                        ->setContent($faker->paragraph(4))
                        ->setPublishedDate($faker->dateTimeBetween('-6 months'))
                        ->setBooks($book)
                        ->setPublic($status)
                        ->setCompleted($status2);

                $manager->persist($chapter);

                //Créer 4 comments
                for($k = 1; $k <= mt_rand(3, 6); $k++)
                {
                    $content = '<p>'. join($faker->paragraphs(2), '</p><p>') .'</p>';

                    $comment = new Comment();

                    $now = new \DateTime();
                    $interval = $now->diff($chapter->getPublishedDate());
                    $days = $interval->days;
                    $min = '-'.$days. ' days'; //-100 days

                    $comment->setAuthor($faker->name)
                            ->setContent($content)
                            ->setCreatedAt($faker->dateTimeBetween($min))
                            ->setChapter($chapter);

                    $manager->persist($comment);

                }
            }

        }

        $manager->flush();
    }
}
