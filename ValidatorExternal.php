<?php
namespace Sentia\Utils;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ValidatorExternal {
    protected $item;

    /**
     * Zvaliduje sameho seba (this).
     * Errory prida do context-u.
     * @param ExecutionContextInterface $context
     * @param $item - object na validaciu
     */
    public function validate(ExecutionContextInterface $context, $item){
        $this->item = $item;
        // validate this
        $errs = $context->getValidator()->validate($this);
        // add errors to context
        /* @var $err ConstraintViolation */
        foreach($errs as $err){
            $context->buildViolation($err->getMessage())->atPath($err->getPropertyPath())->addViolation();
        }
    }

}
