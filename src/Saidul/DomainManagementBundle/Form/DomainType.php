<?php

namespace Saidul\DomainManagementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class DomainType extends AbstractType{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('ip');
        $builder->add('host');
    }
    
    public function getName()
    {
        return 'domain';
    }
}

?>
