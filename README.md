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
        "intelive/claro": "1.0.0"
    }
```

You can also add this using the Composer command line with the command:

    composer require intelive/claro:1.0.0

#### Run Update
From the command line, run the composer update with the command:

    composer update

#### Run setup:upgrade
From the command line, run setup:upgrade with the command:

    magento setup:upgrade

#### Run di:compile
From the command line, run di:compile with the command:

    magento setup:di:compile