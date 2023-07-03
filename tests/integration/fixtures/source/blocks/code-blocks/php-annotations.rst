
.. code-block:: php-annotations
    // src/AppBundle/Entity/Transaction.php
    namespace AppBundle\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Transaction
    {
        /**
         * @Assert\Iban(
         *     message="This is not a valid International Bank Account Number (IBAN)."
         * )
         */
        protected $bankAccountNumber;
    }
