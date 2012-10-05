
Kega_Touch documentation
#############################

This document contains the basic know-abouts for the Kega_Touch module.

--------------------------------------------------------------------------
!! By default this module is disabled in app/etc/modules/Kega_Touch.xml !!
--------------------------------------------------------------------------

##------------------------------------------
## uRapidFlow profiles
##------------------------------------------
Make sure you have uRapidFlow running with valid licences.
Create all export profiles that are needed for the Touch exports.
You can find the Profile Configuration as JSON data in the .profile files in this documentation dir.

##--------------------------------------------
## Kega_Touch_Model_Observer::createCatalogDb
##--------------------------------------------
Every morning at 5.30 we export all catalog/product data that is needed for the touch app to catalog.db

##------------------------------------------
## Kega_Touch_Model_Observer::createStockDb
##------------------------------------------
Every 30 minutes (between 7 and 23hour) we export product stock info to stock.db

##------------------------------------------
## uRapidFlow output redirection
##------------------------------------------
The export makes use of observers, that redirect the uRapidFlow output.
Simply said:
1) Export starts, SQLite (temp)DB is created.
2) uRapidFlow profiles are executed.
3) uRapidFlow output is redirected to the corresponding SQLite DB / table.
   Just before we export the data to the SQLite table, we manipulate it in several ways.
   In example: Split category names, add simple product data (sizes), etc. 
4) uRapidFlow output file is deleted.
5) Rename SQLite (temp)DB -> SQLite DB

##------------------------------------------
## Webservices
##------------------------------------------
The touch app makes use of the default Magento SOAP API.
In addition it also uses SOAP API methods from:
Kega_Touch (touch), Kega_Store (retail), Kega_Webservices (webservices_nl)