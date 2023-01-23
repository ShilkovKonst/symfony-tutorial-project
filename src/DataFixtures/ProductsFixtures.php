<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductsFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($prod = 1; $prod <= 10; $prod++) {
            $product = new Products();
            $product->setName($faker->text(15));
            $product->setDescription($faker->text());
            $product->setSlug($this->slugger->slug($product->getName())->lower());
            $product->setPrice($faker->numberBetween(900, 1500));
            $product->setStock($faker->numberBetween(0, 15));

            $category = $this->getReference('cat-'.rand(1, 8));
            $product->setCategories($category);
            $this->setReference('prod-' . $prod, $product);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
