<?php

namespace Heyday\GoogleContentExperiments\Tests;

use Heyday\GoogleContentExperiments\GoogleContentExperiments;

class GoogleContentExperimentsTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $projectID = '1234';
        $webPropertyID = 'UA-123456-78';
        $profileID = '12345678';

        $this->experiment = $this->getMockBuilder(
            'GoogleApi\Contrib\Experiment'
        )->setMethods(array('getId'))->getMock();;

        $this->experiment->expects($this->any())
            ->method('getId')
            ->will($this->onConsecutiveCalls(1,2,3));


        $experiments =  $this->getMockBuilder(
            'GoogleApi\Contrib\Experiments'
        )->setMethods(array('getItems'))->getMock();

        $experiments->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(
                array(
                    $this->experiment,
                    $this->experiment,
                    $this->experiment
                )
            ));

        $managementExperiments = $this->getMockBuilder(
            'GoogleApi\Contrib\ManagementExperimentsServiceResource'
        )->setMethods(array('listManagementExperiments'))->getMock();

        $managementExperiments->expects($this->once())
            ->method('listManagementExperiments')
            ->will($this->returnValue($experiments));

        $analytics = $this->getMockBuilder('GoogleApi\Contrib\AnalyticsService')
            ->disableOriginalConstructor()

            ->getMock();

        $analytics->management_experiments = $managementExperiments;

        $this->gce = new GoogleContentExperiments(
            $analytics,
            $projectID,
            $webPropertyID,
            $profileID
        );
    }

    protected function tearDown()
    {
        $this->gce = null;
    }

    public function testGetExperiments()
    {
        $this->assertEquals(
            $this->gce->getExperiments(),
            array(
                $this->experiment,
                $this->experiment,
                $this->experiment
            )
        );
    }

}
