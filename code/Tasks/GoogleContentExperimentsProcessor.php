<?php

use GoogleApi\Client as GoogleClient;

use Heyday\GoogleContentExperiments\Container;
use Heyday\GoogleContentExperiments\GoogleContentExperiments;

/**
 * Class GoogleContentExperimentsProcessor
 *
 * Processes GoogleContentExperiments and adds them as options on the list of available content experiments.
 */
class GoogleContentExperimentsProcessor extends CliController
{

    public function __construct(GoogleContentExperiments $googleContentExperiments = null)
    {
        parent::__construct();
        if (!$googleContentExperiments) {
            $container = Container::getInstance();
            $this->googleContentExperiments = $container['google_content_experiments'];
        } else {
            $this->googleContentExperiments = $googleContentExperiments;
        }
    }

    public function process()
    {
        $experiments = $this->googleContentExperiments->getExperiments();

        if ($experiments) {
            foreach ($experiments as $experiment) {

                // if the experiment already exists then update its details, otherwise create a new one

                if (!$googleExperiment = \DataObject::get_one('GoogleContentExperiment', "ExperimentID = '" . $experiment->getId() . "'")) {
                    $googleExperiment = new GoogleContentExperiment();
                }
                $googleExperiment->updateExperiment($experiment);
                $googleExperiment->write();

                foreach ($experiment->getVariations() as $variationID => $variation) {

                    // if we have a variation, update it - otherwise create a new one
                    if (!$googleVariation = \DataObject::get_one('GoogleContentExperimentVariation', "ContentExperimentID = '" . $googleExperiment->ID . "' AND VariationID = '" . $variationID . "'")) {
                        $googleVariation = new GoogleContentExperimentVariation();
                    }

                    $googleVariation->updateVariation($variationID, $variation, $googleExperiment->ID);
                    $googleVariation->write();

                }

            }
        }

    }

}