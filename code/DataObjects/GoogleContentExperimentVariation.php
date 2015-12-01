<?php


class GoogleContentExperimentVariation extends DataObject
{

    private static $db = array(
        'VariationID' => 'Int',
        'Weight' => 'Decimal(10,2)',
        'Status' => 'Varchar(255)'
    );

    private static $has_one = array(
        'ContentExperiment' => 'GoogleContentExperiment'
    );

    /**
     * Helper function to update variations
     *
     * @param int $variationID The ID of the variation
     * @param object $variationData The variation data returned from Google.
     * @param int $contentExperimentID The internal SilverStripe ContentExperiment Object ID
     * @return mixed
     */
    public function updateVariation($variationID, $variationData, $contentExperimentID)
    {
        $this->VariationID = $variationID;
        $this->Weight = $variationData->getWeight();
        $this->Status = $variationData->getStatus();
        $this->ContentExperimentID = $contentExperimentID;

    }

}