# SilverStripe Google Content Experiments

Integration with Google Content Experiments for SilverStripe. Allows for Experiment Data to be integrated with your
application so that experiments can be served without the usage of the GCE javascript implementation. For more
information please see [running a server-side experiment](https://developers.google.com/analytics/solutions/experiments-server-side).

## License

SilverStripe Google Content Experiments is released under the [MIT license](http://heyday.mit-license.org/)

## Installation

    If using SivlerStripe version 2.4, please use the 1.0.0 tag

	$ composer require silverstripe-googlecontentexperiments

## How to use

### Setup

First you must set up the correct authentication details so that Google Content Experiments can retrieve data from your
Google Analytics account.

To do this:
 * Visit http://code.google.com/apis/console
 * Sign in with the account you use to manage your analytics
 * Create a new project for the website you wish to target
 * Browse to 'Services' and enable the Analytics API
 * Browse to 'API access' and click 'Create another client ID'
 * Choose 'Service account'
 * Download the private key and store it somewhere securely within your application directory

Once this is done, you will see the details for the service account. From here, you will need to copy the email address
which was generated and add it to the analytics account you are targeting with 'Read & Analyze' access only. This will
allow the server connection to read your experiment data.

### Configuration

Once you have set up the account and downloaded the private key for your application we have to configure the service.
This can be completed in your `_config.php` with the following. This code sets up the dependancy injection container
which is used.

```php
use Heyday\GoogleContentExperiments;

$container = GoogleContentExperiments\Container::getInstance();
$container['google_content_experiments.class'] = 'Heyday\GoogleContentExperiments\GoogleContentExperiments';
$container['google_content_experiments.config.project_id'] = 'PROJECT_ID';
$container['google_content_experiments.config.web_property_id'] = 'WEB_PROPERTY_ID';
$container['google_content_experiments.config.profile_id'] = 'PROFILE_ID';
$container['google_analytics_service'] = function ($c) {

    $client = new \GoogleApi\Client(
        array(
            'application_name' => 'APPLICATION_NAME',
            'oauth2_client_id' => 'CLIENT_ID',
            'use_objects' => true
        )
    );
    $client->setAssertionCredentials(
        new \GoogleApi\Auth\AssertionCredentials(
                'EMAIL_ADDRESS',
                array('https://www.googleapis.com/auth/analytics.readonly'), // SCOPE
                file_get_contents(
                    BASE_PATH . PATH_TO_SERVER_KEY
                )
        )
    );

    return new \GoogleApi\Contrib\AnalyticsService(
        $client
    );

};

```

Generally the following applies:
```
PROJECT_ID: available from your Analytics account
WEB_PROPERTY_ID: available from your Analytics account - usually takes the form 'UA-11111111-11'
PROFILE_ID: available from your Analytics account
APPLICATION_NAME: the name of your Application
CLIENT_ID: available from the Google APIs console
SCOPE: Scope needed for the application - usually only 'https://www.googleapis.com/auth/analytics.readonly'
EMAIL_ADDRESS: the email address which we previously added to our Analytics account
```
You also need to include the GoogleContentExperimentScripts somewhere on the page(s) you want to test.
```
<% include GoogleContentExperimentsScript %>
```

Its also a wise move to set up a cron job which will run the processor periodically for you and pick up the new
experiments so that you can then run them. This also will update variations status as the experiments progress.

```bash
0 */12 * * * /path/to/webroot/sapphire/sake GoogleContentExperimentsProcessor
```

### Usage in templates

Templates are set up so that you can push the code without having to worry about the experiment being set up or running.
This means that there is a necessary `<% else %>` to make sure that when experiments finish the page goes back to
'normal' until it can be updated to the winning solution.

```
<% if GoogleContentExperiment %>

    <% if GoogleContentExperimentVariation(0_1) %>
        THIS IS THE ORIGINAL VARIATION
    <% end_if %>

    <% if GoogleContentExperimentVariation(1_1) %>
        THIS IS ALTERNATIVE VARIATION
    <% end_if %>

    <% if GoogleContentExperimentVariation(2_1) %>
       THIS IS ALTERNATIVE VARIATION
    <% end_if %>

<% else %>
    NO EXPERIMENT IS RUNNING RETURN TO REGULAR SERVICES
<% end_if %>
```
`GoogleContentExperimentVariation()` takes two parameters, which are separated by an underscore. The first is the
VariationID we are checking for, the second is the ExperimentID (internal ID). The above example shows three variation
possibilities for ExperimentID 1.

If you are only running one experiment per page, the ExperimentID is not needed, e.g. `<% if GoogleContentExperimentVariation(0) %>`


## Contributing

### Unit Testing

	$ composer install --prefer-dist --dev
	$ phpunit

### Code guidelines

This project follows the standards defined in:

* [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
* [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)