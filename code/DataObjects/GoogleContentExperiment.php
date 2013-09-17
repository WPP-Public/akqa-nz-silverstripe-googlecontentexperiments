<?php

class GoogleContentExperiment extends DataObject
{

    public static $db = array(
        'StartTime' => 'SS_DateTime',
        'EndTime' => 'SS_DateTime',
        'Name' => 'Text',
        'Description' => 'Text',
        'ExperimentID' => 'Varchar(255)',
        'Status' => 'Varchar(255)',
        'WinnerFound' => 'Varchar(255)',
        'TrafficCoverage' => 'Varchar(255)',
        'GlobalExperiment' => 'Boolean'
    );

    public static $has_many = array(
        'ContentExperimentVariations' => 'GoogleContentExperimentVariation',
        'Pages' => 'Page'
    );

    public static $summary_fields = array(
        'Name' => 'Name',
        'IsGlobal' => 'Global'
    );

    public function getCMSFields()
    {
        $fields = new FieldSet();

        $fields->push(new CheckboxField('GlobalExperiment'));

        return $fields;
    }

    public function getIsGlobal()
    {
        return $this->GlobalExperiment ? 'Yes' : 'No';
    }

    /**
     * Helper function to update experiments
     *
     * @param $experimentData
     * @return mixed
     */
    public function updateExperiment($experimentData)
    {

        $this->StartTime = strtotime($experimentData->getStartTime());
        $this->EndTime = strtotime($experimentData->getEndTime());
        $this->Name = $experimentData->getName();
        $this->Description = $experimentData->getDescription();
        $this->ExperimentID = $experimentData->getId();
        $this->Status = $experimentData->getStatus();
        $this->WinnerFound = $experimentData->getWinnerFound();
        $this->TrafficCoverage = $experimentData->getTrafficCoverage();

    }


}