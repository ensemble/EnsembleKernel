<?php
return array(
    'slmcmfkernel' => array(
        'load_routes' => true,
        'load_page'   => false,
    ),
    
    'di' => array(
        'instance' => array(
            // Set Doctrine annotations in driver chain
            'orm_driver_chain' => array(
                'parameters' => array(
                    'drivers' => array(
                        'slm_cmf_base' => array(
                            'class'     => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                            'namespace' => 'SlmCmfKernel\Entity',
                            'paths'     => array(__DIR__ . '/../src/SlmCmfKernel/Entity')
                        ),
                    ),
                ),
            ),
            
            // Set Gedmo tree subscriber
            'orm_evm' => array(
                'parameters' => array(
                    'opts' => array(
                        'subscribers' => array('Gedmo\Tree\TreeListener')
                    )
                )
            ),
        ),
    ),
);
