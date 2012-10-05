
Kega_TntDirect documentation
#############################

This document contains the basic know-abouts for the Kega_Export module.

##-------------------------------------------
## Kega_TntDirect_Model_Observer::export2Tnt
##-------------------------------------------
Every 30 minutes this cron runs and creates a PostNL export file for every (not exported) PostNL (TNT) shipment in the DB.
The export file is uploaded to PostNL by FTP.

The structure of this file and info about what all A and V records are you can find in the documentation PDF's.

##-----------------
## FTP: Watch out!
##-----------------
Do not use ftp.ebn.enovation.nl, you need to use ftp2.ebn.enovation.nl because of a FTP server bug.
Response codes are not valid for PHP FTP client.


##--------------------------------------------
## Observer: sales_order_shipment_save_before
##--------------------------------------------
If the shipment is new and is a kega_tnt_direct shipment we automaticly create a tracking number.
This tracking number is needed for the barcode sticker and export file.
(In older versions we did this by extending Mage_Adminhtml_Sales_Order_ShipmentController)

##-------------------------------------------
## Observer: kega_pdf_refund_insert_shipment_track_sticker_kega_tnt_direct
##-------------------------------------------
At the end of the refund PDF creation, the Kega_Pdf module dispatches this event.
Kega_TntDirect hooks into this event and inserts the PostNL tracking sticker into the refund PDF (page).