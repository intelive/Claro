## Claro Magento 2 Service


## Installation

Before installing, it is recommended that you disable your cache in System -> Cache Mangement.

#### Update composer.json
To install, you'll need to be sure that your root `composer.json` file contains a reference to the Claro repository.  To do so, add the following to `composer.json`:

```json
    "repositories": [
        {
            "type": "vcs",                                                                                                              
            "url": "https://github.com/intelive/claro.git"
        }
    ]
```

The above can also be added using the Composer command line with the command:

    composer config repositories.claro vcs https://github.com/intelive/claro.git

Next, add the required package your root `composer.json` file:

```json
    "require": {
        "intelive/claro": "1.2.0"
    }
```

You can also add this using the Composer command line with the command:

    composer require intelive/claro:1.2.0

#### Run Update
From the command line, run the composer update with the command:

    composer update

#### Run setup:upgrade
From the command line, run setup:upgrade with the command:

    magento setup:upgrade

#### Run di:compile
From the command line, run di:compile with the command:

    magento setup:di:compile

#### Run setup:static-content:deploy
From the command line, run setup:static-content:deploy with the command:

    magento setup:static-content:deploy
    
## Uninstall

There are two ways to uninstall the module from Magento:

#### Automatic Uninstall (Note: Puts the store in maintenance mode until the process is finished):
From the command line, remove the code and database data related to the module:
    
    magento module:uninstall Intelive_Claro

 
#### Manual Uninstall
Disable the Intelive module from magento:

    magento module:disable Intelive_Claro
    magento setup:upgrade

Remove the Intelive package from your `composer.json` and vendor folder:
    
    composer remove intelive/claro
    
Remove module data from the database:
* Go to the table `url_rewrite` and remove any entries that match `target_path LIKE '%intelive%'`
* Go to the table `core_config_data` and remove any entries that match `path LIKE '%intelive%'`
* Go to the table `setup_module` and remove any entries that match `module LIKE '%intelive%'`
