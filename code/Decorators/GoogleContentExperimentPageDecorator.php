<?php
/**
 * Class GoogleContentExperimentPageDecorator
 *
 * Decorate pages or objects
 */
class GoogleContentExperimentPageDecorator extends DataExtension
{

    private static $has_many = array(
        'ContentExperiment' => 'GoogleContentExperiment'
    );

    /**
     * Update the CMS fields on the extended object
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $gridFieldConfig = GridFieldConfig_RelationEditor::create();
        $gridField = new GridField('Content Experiment', 'ContentExperiment', $this->owner->ContentExperiment(), $gridFieldConfig);
        $fields->addFieldToTab('Root.ContentExperiment', $gridField);
    }

    /**
     * Check if the page this is attached to is a google experiment page. Here we check global experiments
     * first as they take precedence.
     *
     * @return bool
     */
    public function GoogleContentExperiment()
    {
        $experiment = DataObject::get_one('GoogleContentExperiment', "GlobalExperiment = 1");

        if ($experiment && $experiment->exists() && $experiment->Status == 'RUNNING') {
            return true;
        } else {
            $experiment = $this->owner->ContentExperiment();

            if ($experiment && $experiment->exists() && $experiment->Status == 'RUNNING') {
                return true;
            }

        }
        return false;
    }

    /**
     * Check whether the current user is part of a variation test.
     *
     * This function takes arguments in the following form:
     *      variationID_experimentID
     *
     * @param string $args The variation and experiment to check
     * @return bool Whether this user is part of the variation test or not
     */
    public function GoogleContentExperimentVariation($args)
    {
        $experimentID = false;

        if (strpos($args, '_')) {

            list($variationID, $experimentID) = explode('_', $args);

            $this->setUserVariation($experimentID);

        } else {
            $variationID = $args;
            $this->setUserVariation();
        }

        $experiment = $experimentID ? DataObject::get_by_id('GoogleContentExperiment', $experimentID) : DataObject::get_one('GoogleContentExperiment', "GlobalExperiment = 1");

        if (!$experiment || !$experiment->exists()) {
            $experiment = $this->owner->ContentExperiment();

            if (!$experiment || !$experiment->exists()) {
                return false;
            }
        }

        $storageString = 'utmgce_' . $experiment->ID;

        // rely on the cookie in the first instance
        if ($currentVariation = Cookie::get($storageString)) {

            if ($currentVariation == 'v' . $variationID) {

                return true;

            }

            return false;

        }

        // otherwise check session
        if ($currentVariation = Session::get($storageString)) {

            if ($currentVariation == 'v' . $variationID) {

                return true;

            }

            return false;

        }

        return false;

    }

    /**
     * Set the variation which the user will see for their journey.
     *
     * Here we prefer global experiments to localised ones. If you have an experiment running on a page and one
     * which is running globally, the one running globally takes precedent. This makes it very important to manage your
     * experiments as pushing code which relies on experiments could be in effect due to the global one. Basically, turn
     * off global experiments before trying to set up a localised one.
     */
    private function setUserVariation($experimentID = false)
    {
        if ($experimentID) {
            $experiment = DataObject::get_by_id('GoogleContentExperiment', $experimentID);
        } else {
            $experiment = DataObject::get_one('GoogleContentExperiment', "GlobalExperiment = 1 AND Status = 'RUNNING'");

            if (!$experiment || !$experiment->exists()) {
                $experiment = $this->owner->ContentExperiment("Status = 'RUNNING'");
            }
        }

        if ($experiment && $experiment->exists()) {

            $storageString = 'utmgce_' . $experiment->ID;

            // the variation has already been set, so don't reset it
            if (Cookie::get($storageString) || Session::get($storageString)) {
                return;
            }

            // decide whether to include the user in the test or not
            if (rand(0.0, 1.0) <= $experiment->TrafficCoverage) {

                $variations = $experiment->ContentExperimentVariations();

                $cumulativeWeights = 0;

                if ($variations) {

                    foreach ($variations as $variation) {

                        if ($variation->Status == 'ACTIVE') {
                            $cumulativeWeights += $variation->Weight;
                        }

                        if (rand(0, 1.0) < $cumulativeWeights) {

                            $variationID = 'v' . $variation->VariationID;
                            Cookie::set($storageString, $variationID);
                            Session::set($storageString, $variationID);

                            return;

                        }

                    }

                }

            }
        }

        return;
    }

    /**
     * Get experiment data including VariationID and ExperimentID for the front end.
     *
     * @return ArrayList
     */
    public function getGoogleContentExperimentsData()
    {

        $experimentData = new ArrayList();

        foreach ($this->getActiveExperimentsIDs() as $experimentID) {

            $gce = DataObject::get_by_id('GoogleContentExperiment', $experimentID);

            $experiment = new DataObject();
            $experiment->ExperimentID = $gce->ExperimentID;
            $experiment->VariationID = $this->getChosenVariation($experimentID);
            $experimentData->push($experiment);
        }

        return $experimentData;

    }

    /**
     * Get the currently active experiment ID's for this page. These are local database ID's and not the GCE ID.
     *
     * @return array Experiment ID's
     */
    private function getActiveExperimentsIDs()
    {
        $experiments = DataObject::get('GoogleContentExperiment');
        $activeExperiments = array();

        foreach ($experiments as $experiment) {
            $storageName = 'utmgce_' . $experiment->ID;
            
            if (Session::get($storageName) || Cookie::get($storageName)) {
                $contentExperiment = $this->owner->ContentExperiment();

                if ($contentExperiment->exists() && $contentExperiment->ID == $experiment->ID) {
                    $activeExperiments[] = $experiment->ID;
                } else if (DataObject::get('GoogleContentExperiment', "GlobalExperiment = 1 AND ID = $experiment->ID")) {
                    $activeExperiments[] = $experiment->ID;
                }

            }
        }

        return $activeExperiments;
    }

    /**
     * Get the current index of the VariationID that the user is seeing. This is a zero based index.
     *
     * @param int $experimentID ExperimentID
     * @return int Variation ID
     */
    private function getChosenVariation($experimentID)
    {

        $variationID = 0;

        // set the variation which the user will see
        $this->setUserVariation($experimentID);

        $storageString = 'utmgce_' . $experimentID;

        // rely on the cookie in the first instance
        if ($currentVariation = Cookie::get($storageString)) {

            $variationID = substr($currentVariation, 1, strlen($currentVariation) - 1);

        } else if ($currentVariation = Session::get($storageString)) {

            $variationID = substr($currentVariation, 1, strlen($currentVariation) - 1);

        }

        return $variationID;
    }


}