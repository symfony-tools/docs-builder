.. code-block:: php-attributes

    // src/SomePath/SomeClass.php
    namespace App\SomePath;

    use Symfony\Component\Validator\Constraints as Assert;

    class SomeClass
    {
        #[AttributeName]
        private $property1;

        #[AttributeName()]
        private $property2;

        #[AttributeName('value')]
        private $property3;

        #[AttributeName('value', option: 'value')]
        private $property4;

        #[AttributeName(['value' => 'value'])]
        private $property5;

        #[AttributeName(
            'value',
            option: 'value'
        )]
        private $property6;

        #[Assert\AttributeName('value')]
        private $property7;

         #[Assert\AttributeName(
            'value',
            option: 'value'
         )]
         private $property8;

         #[Route('/blog/{page<\d+>}', name: 'blog_list')]
         private $property9;

         #[Assert\GreaterThanOrEqual(
             value: 18,
         )]
         private $property10;

         #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
         private $property11;
    }

