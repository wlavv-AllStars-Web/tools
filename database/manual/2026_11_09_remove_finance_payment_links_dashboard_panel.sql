-- Payment links are now created and sent directly by Sales.
-- Remove the former Finance approval panel.
DELETE FROM `dashboard`
WHERE `tab` = 'finance'
  AND `panel` = 'payment_links_pending';
