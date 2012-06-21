<?php
return array(
    'slmcmf_kernel' => array(
        /**
         * Class for page service
         */
        'page_service_class' => '',
        
        /**
         * Whether page parsing (routes + navigation) is enabled
         */
        'pages_parse'      => true,
        
        /**
         * Whether page instances should be loaded during dispatch
         */
        'page_load'        => true,
        
        /**
         * Flags to enable/disable cache and keys pointing to cache adapters
         */
        'cache_routes'     => false,
        'cache_navigation' => false,
        'cache_routes_key'    => '',
        'cache_navigation_key' => ''
    ),
);
