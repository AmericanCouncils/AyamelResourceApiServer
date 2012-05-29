# ACGetID3Bundle #

This bundle provides container services for loading the `getid3` library in your own code.

## Installation ##

The `getid3` library is not currently available via composer, github or packagist.org.  In order to have composer automatically download the required `getid3`
dependency library, you will have to add a custom repository to your application's root `composer.json` file.  Below is the custom repository definition
that points to a `zip` archive of `getid3` on sourceforge.com, add this JSON structure into your app's `composer.json` and run `php composer.phar update`
to have it install the library properly.

    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "getid3/getid3",
                "version": "1.9.3",
                "autoload": {
                    "classmap": "getid3/"
                },
                "dist": {
                    "url": "http://downloads.sourceforge.net/project/getid3/getID3%28%29%201.x/1.9.3/getid3-1.9.3-20111213.zip?r=http%3A%2F%2Fwww.google.com%2Furl%3Fsa%3Dt%26rct%3Dj%26q%3Dgetid3%2520download%26source%3Dweb%26cd%3D3%26ved%3D0CGIQFjAC%26url%3Dhttp%253A%252F%252Fsourceforge.net%252Fprojects%252Fgetid3%252Ffiles%252Flatest%252Fdownload%26ei%3DNPu8T-r4GsG46QG_uoxG%26usg%3DAFQjCNFz3LqAXh-pQFwXXRLMnXy41BPkKQ&ts=1337785158&use_mirror=iweb",
                    "type": "zip"
                }
            }
        }
    ]
    

## Usage ##

`getid3` is not available as a service, you can instantiate it directly, as shown below:

	<?php
	//instantiate directly
	$getid3 = new \getID3
	
	//analyze a file
	$stats = $getid3->analyze('/path/to/file.mp3');
	
	//see the results
	var_dump($stats);
    
## Commands ##

The command `getid3:analyze` is provided to allow you to analyze a file from the command line with `getid3`.  Example below:
 
	app/console getid3:analyze /path/to/file.mp3

