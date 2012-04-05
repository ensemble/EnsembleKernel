<?php
return array(
    'slmcmfbase' => array(
        'load_routes' => true,
        'load_page'   => false,
    ),
    'di' => array(
        'instance' => array(
            'SlmCmfBase\Router\Parser' => array(
                'parameters' => array(
                    'routes' => array()
                ),
            ),
            'SlmCmfBase\Listener\AddRoutesFromDb' => array(
                'parameters' => array(
                    'em'     => 'doctrine_em',
                    'parser' => 'SlmCmfBase\Router\Parser',
                ),
            ),
            
            // Set Doctrine annotations in driver chain
            'orm_driver_chain' => array(
                'parameters' => array(
                    'drivers' => array(
                        'slm_cmf_base' => array(
                            'class'     => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                            'namespace' => 'SlmCmfBase\Entity',
                            'paths'     => array(__DIR__ . '/../src/SlmCmfBase/Entity')
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
