<?php

namespace Heyday\GoogleContentExperiments;

use GoogleApi\Contrib\AnalyticsService;

class GoogleContentExperiments
{

    private $analytics;
    private $projectID;
    private $webPropertyID;
    private $profileID;

    public function __construct(AnalyticsService $analytics, $projectID, $webPropertyID, $profileID)
    {
        $this->analytics = $analytics;
        $this->projectID = $projectID;
        $this->webPropertyID = $webPropertyID;
        $this->profileID = $profileID;
    }

    /**
     * Creates local experiments which exist within analytics but not here. Updates experiments which already exist
     * locally but have been changed within analytics.
     */
    public function getExperiments()
    {

        $experiments = $this->analytics->management_experiments
            ->listManagementExperiments(
                $this->projectID,
                $this->webPropertyID,
                $this->profileID
            );

        return $experiments->getItems();

    }


}