<?php

namespace Heyday\GoogleContentExperiments;

class Container extends \Pimple
{

    protected static $instance;

    public function __construct()
    {

        $this['google_content_experiments'] = $this->share(
            function ($c) {
                $googleContentExperiments = new $c['google_content_experiments.class'](
                    $c['google_analytics_service'],
                    $c['google_content_experiments.config.project_id'],
                    $c['google_content_experiments.config.web_property_id'],
                    $c['google_content_experiments.config.profile_id']
                );

                return $googleContentExperiments;
            }
        );

    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getService($name)
    {
        $container = self::getInstance();
        return $container[$name];
    }

    /**
     * Returns and instance of the container
     * @return \Heyday\GoogleContentExperiments\Container Google Content Experiments Container
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

}
