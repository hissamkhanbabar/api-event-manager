<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class SponsorFields extends Fields
{
    private $postType = 'sponsor';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {
        // Website
        register_rest_field($this->postType,
            'website',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with sponsor website.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Phone link
        register_rest_field($this->postType,
            'phone',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with phone number.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Email
        register_rest_field($this->postType,
            'email',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with sponsor email.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Instagram link
        register_rest_field($this->postType,
            'instagram_link',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with instagram link.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Youtube link
        register_rest_field($this->postType,
            'youtube_link',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with youtube link.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Twitter link
        register_rest_field($this->postType,
            'twitter_link',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with twitter link.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Facebook link
        register_rest_field($this->postType,
            'facebook_link',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with facebook link.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );
    }
}
