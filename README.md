## Claro BI Magento 2 Service

Bi is essential for any business that wants to stay ahead of he competition  
Bi can help grow revenue for online shops through better management of inventory, better understanding of customers and optimization of business processes  
No more looking at multiple data sources! No more exports,imports and complicated pivot tables!  
All you need to do is install the connector module for your platform and you can start capturing exactly the knowledge that your organization requires.  

_________________  

<p align="center">
  <img src="https://www.clarobi.com/themes/mehedi-megakit//assets/images/clarobi-shots/trends.png" />
</p>

**Trends and anomalies**   
Our solution continuosly monitors your sales data in order to detect patterns like trends and anomalies like peaks and dips so that no relevant information passes unnoticed

_________________  

<p align="center">
  <img src="https://www.clarobi.com/themes/mehedi-megakit//assets/images/clarobi-shots/stock.png" />
</p>

**Inventory forecasting**   
Out-of-stock products can severely damage your sales performance since you can quickly lose a customer to a competitor offering something similar. Avoid it with accurate inventory forecasts based on previous sales and seasonal trends.
_________________
  
<p align="center">
  <img src="https://www.clarobi.com/themes/mehedi-megakit//assets/images/clarobi-shots/customers.PNG" />
</p>

**Customer segmentation**   
Quickly identify your VIP customers, or the ones that were inactive for a long period with automatic customer segmentation in groups based on their activity.
_________________  

<p align="center">
  <img src="https://www.clarobi.com/themes/mehedi-megakit//assets/images/clarobi-shots/goals.PNG" />
</p>

**Goals**   
Define your goal for any of KPI and we will automatically monitor it and inform you on its achievement.
_________________  

<p align="center">
  <img src="https://www.clarobi.com/themes/mehedi-megakit//assets/images/clarobi-shots/claro_daily_email.png" />
</p>

**Daily email**   
An overview of your store's activity delivered to your inbox every morning.
_________________  

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
        "intelive/claro": "1.2.7"
    }
```

You can also add this using the Composer command line with the command:

    composer require intelive/claro:1.2.7

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
